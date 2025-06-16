<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\EvaluationDetail;
use App\Models\User;
use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Branch;
use App\Exports\EvaluationExport;
use Maatwebsite\Excel\Facades\Excel;

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if(\Auth::user()->type == 'company')
        {
            // Query untuk admin - bisa melihat semua evaluasi
            $query = Evaluation::with(['details', 'evaluator', 'evaluatee', 'evaluatee.employee']);

            // Filter berdasarkan user yang dipilih
            if ($request->has('user_id') && $request->user_id != '') {
                $query->where('evaluatee_id', $request->user_id);
            }

            // Filter berdasarkan branch
            if ($request->has('branch_id') && $request->branch_id != '') {
                $query->whereHas('evaluatee.employee', function($q) use ($request) {
                    $q->where('branch_id', $request->branch_id);
                });
            }

            // Filter berdasarkan caturwulan jika ada request
            if ($request->has('cw') && in_array($request->cw, ['CW 1', 'CW 2', 'CW 3'])) {
                $query->where('quarter', $request->cw);
            }

            // Filter berdasarkan evaluator jika ada request
            if ($request->has('evaluator_id') && $request->evaluator_id != '') {
                $query->where('evaluator_id', $request->evaluator_id);
            }

            $evaluations = $query->orderBy('created_at', 'desc')->get();

            // Ambil daftar user untuk filter dropdown
            $users = User::where('type', '!=', 'client')
                        ->where('is_active', 1)
                        ->orderBy('name', 'asc')
                        ->get();

            // Ambil daftar evaluator untuk filter dropdown
            $evaluators = User::whereHas('evaluationsAsEvaluator')
                            ->orderBy('name', 'asc')
                            ->get();

            // Ambil daftar branch untuk filter dropdown
            $branches = Branch::orderBy('name', 'asc')->get();

            return view('evaluation.admin.index', compact('evaluations', 'users', 'evaluators', 'branches'));
        }
        else
        {
            $query = Evaluation::with(['details', 'evaluator', 'evaluatee'])
                ->where('evaluatee_id', $user->id)
                ->orderBy('quarter', 'desc');

            // Filter berdasarkan caturwulan jika ada request
            if ($request->has('cw') && in_array($request->cw, ['CW 1', 'CW 2', 'CW 3'])) {
                $query->where('quarter', $request->cw);
            }

            $evaluations = $query->get();
            
            return view('evaluation.index', compact('evaluations'));
        }
    }

    public function create(Request $request)
    {
        // Validasi input dari modal
        $request->validate([
            'quarter' => 'required|in:CW 1,CW 2,CW 3',
            'evaluatees' => 'required|array|min:1',
            'evaluatees.*' => 'exists:users,id'
        ]);

        $quarter = $request->quarter;
        $selectedEmployeeIds = $request->evaluatees;
        
        // Ambil data karyawan yang dipilih
        $selectedEmployees = User::whereIn('id', $selectedEmployeeIds)->get();
        
        // Cek apakah ada evaluasi yang sudah ada untuk periode dan karyawan yang sama
        $existingEvaluations = Evaluation::where('quarter', $quarter)
            ->where('evaluator_id', auth()->user()->id)
            ->whereIn('evaluatee_id', $selectedEmployeeIds)
            ->with('evaluatee')
            ->get();
            
        if ($existingEvaluations->count() > 0) {
            $existingNames = $existingEvaluations->pluck('evaluatee.name')->join(', ');
            return redirect()->route('evaluation.index')
                ->with('error', "Penilaian untuk periode {$quarter} sudah ada untuk karyawan: {$existingNames}");
        }

        return view('evaluation.create', compact('selectedEmployees', 'quarter'));
    }

    public function store(Request $request)
    {

        // dd($request->all());
        // Validasi input
        $request->validate([
            'quarter' => 'required|string',
            'evaluations' => 'required|array|min:1',
            'evaluations.*.evaluatee_id' => 'required|exists:users,id',
            'evaluations.*.details' => 'required|array|min:1',
            'evaluations.*.details.*.indicator_id' => 'required|exists:attributes,id',
            'evaluations.*.details.*.indicator_category' => 'required|string',
            'evaluations.*.details.*.indicator_name' => 'required|string',
            'evaluations.*.details.*.score' => 'required|integer|min:1|max:5',
            'evaluations.*.details.*.comments' => 'nullable|string|max:1000',
        ]);

        try {
            \DB::beginTransaction();

            foreach ($request->evaluations as $evaluationData) {
                // Cek apakah evaluasi sudah ada
                $existingEvaluation = Evaluation::where('quarter', $request->quarter)
                    ->where('evaluator_id', auth()->user()->id)
                    ->where('evaluatee_id', $evaluationData['evaluatee_id'])
                    ->first();
                    
                if ($existingEvaluation) {
                    continue; // Skip jika sudah ada
                }

                // Buat evaluasi baru
                $evaluation = Evaluation::create([
                    'evaluator_id' => auth()->user()->id,
                    'evaluatee_id' => $evaluationData['evaluatee_id'],
                    'quarter' => $request->quarter,
                ]);

                // Simpan detail evaluasi
                foreach ($evaluationData['details'] as $detail) {
                    EvaluationDetail::create([
                        'evaluation_id' => $evaluation->id,
                        'indicator_id' => $detail['indicator_id'],
                        'indicator_category' => $detail['indicator_category'],
                        'indicator_name' => $detail['indicator_name'],
                        'score' => (int) $detail['score'],
                        'comments' => $detail['comments'] ?? null,
                    ]);
                }
            }

            \DB::commit();

            return redirect()->route('evaluation.index')
                ->with('success', 'Penilaian berhasil disimpan untuk semua karyawan yang dipilih.');

        } catch (\Exception $e) {
            \DB::rollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan penilaian: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $evaluation = Evaluation::with(['details', 'evaluator', 'evaluatee'])
            ->findOrFail($id);
            
        // Pastikan hanya evaluator atau evaluatee yang bisa melihat
        if ($evaluation->evaluator_id !== auth()->user()->id && 
            $evaluation->evaluatee_id !== auth()->user()->id) {
            abort(403, 'Unauthorized access');
        }

        return view('evaluation.show', compact('evaluation'));
    }

    public function edit($id)
    {
        $evaluation = Evaluation::with(['details', 'evaluatee'])
            ->where('evaluator_id', auth()->user()->id)
            ->findOrFail($id);

        return view('evaluation.edit', compact('evaluation'));
    }

    public function update(Request $request, $id)
    {
        $evaluation = Evaluation::where('evaluator_id', auth()->user()->id)
            ->findOrFail($id);

        $request->validate([
            'details' => 'required|array|min:1',
            'details.*.indicator_id' => 'required|exists:attributes,id',
            'details.*.score' => 'required|integer|min:1|max:5',
            'details.*.comments' => 'nullable|string|max:1000',
        ]);

        try {
            \DB::beginTransaction();

            // Update detail evaluasi
            foreach ($request->details as $detailId => $detailData) {
                EvaluationDetail::where('id', $detailId)
                    ->where('evaluation_id', $evaluation->id)
                    ->update([
                        'score' => (int) $detailData['score'],
                        'comments' => $detailData['comments'] ?? null,
                    ]);
            }

            \DB::commit();

            return redirect()->route('evaluation.index')
                ->with('success', 'Penilaian berhasil diperbarui.');

        } catch (\Exception $e) {
            \DB::rollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui penilaian: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $evaluation = Evaluation::where('evaluator_id', auth()->user()->id)
            ->findOrFail($id);

        try {
            \DB::beginTransaction();

            // Hapus detail evaluasi terlebih dahulu
            $evaluation->details()->delete();
            
            // Hapus evaluasi
            $evaluation->delete();

            \DB::commit();

            return redirect()->route('evaluation.index')
                ->with('success', 'Penilaian berhasil dihapus.');

        } catch (\Exception $e) {
            \DB::rollback();
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus penilaian: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        // Validasi bahwa hanya admin yang bisa export
        if(auth()->user()->type != 'company') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Query yang sama dengan index tapi untuk export
        $query = Evaluation::with(['details', 'evaluator', 'evaluatee', 'evaluatee.employee', 'evaluatee.employee.branch']);

        // Filter berdasarkan user yang dipilih
        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('evaluatee_id', $request->user_id);
        }

        // Filter berdasarkan branch
        if ($request->has('branch_id') && $request->branch_id != '') {
            $query->whereHas('evaluatee.employee', function($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        // Filter berdasarkan caturwulan
        if ($request->has('cw') && in_array($request->cw, ['CW 1', 'CW 2', 'CW 3'])) {
            $query->where('quarter', $request->cw);
        }

        // Filter berdasarkan evaluator
        if ($request->has('evaluator_id') && $request->evaluator_id != '') {
            $query->where('evaluator_id', $request->evaluator_id);
        }

        $evaluations = $query->orderBy('created_at', 'desc')->get();

        // Generate filename dengan timestamp dan filter info
        $filename = 'evaluation_report_' . date('Y-m-d_H-i-s');
        
        if ($request->cw) {
            $filename .= '_' . str_replace(' ', '', $request->cw);
        }
        
        if ($request->branch_id) {
            $branch = Branch::find($request->branch_id);
            if ($branch) {
                $filename .= '_' . str_replace(' ', '_', $branch->name);
            }
        }
        
        $filename .= '.xlsx';

        return Excel::download(new EvaluationExport($evaluations, $request->all()), $filename);
    }

}