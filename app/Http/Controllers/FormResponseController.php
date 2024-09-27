<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Training;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use App\Models\Criteria;

class FormResponseController extends Controller
{

    public function index()
    {
        $user           = \Auth::user();

        if($user->type == 'admin' || $user->type == 'company' || $user->type == 'partners')
        {
            $assessment     = Assessment::orderByDesc('id')->get()->unique('user_id');
            $approvalAsSupervisor = Assessment::where('supervisor_id', auth()->user()->employee->id)
                ->orderByDesc('id')
                ->get();

            $approvalAsAppraisal = Assessment::where('appraisal_id', auth()->user()->employee->id)
                ->orderByDesc('id')
                ->get();
            
        }
        else
        {
            $assessment     = Assessment::where('user_id', '=', $user->id)->orderByDesc('id')->get()->unique('user_id');
            $approvalAsSupervisor = Assessment::where('supervisor_id', auth()->user()->employee->id)
                ->orderByDesc('id')
                ->get();

            $approvalAsAppraisal = Assessment::where('appraisal_id', auth()->user()->employee->id)
                ->orderByDesc('id')
                ->get()
                ->unique('user_id');
        }

        return view('formResponses.index', compact('assessment', 'approvalAsAppraisal', 'approvalAsSupervisor'));
    }
    
    public function create()
    {
        $user           = \Auth::user();
        $formC_criteria = Criteria::where('form_type', 'C')->orderBy('order')->get();
        $formD_criteria = Criteria::where('form_type', 'D')->orderBy('order')->get();
        $supervisor     = Employee::get()->pluck('name', 'id');

        $totalProject = Project::whereHas('projectUsers', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        $totalTraining = Training::where('employee', auth()->user()->employee->id)->where('training_type', 2)->count();
        $totalSertifikasi = Training::where('employee', auth()->user()->employee->id)->where('training_type', 1)->count();

        return view('formResponses.create', compact('formC_criteria', 'formD_criteria','supervisor','totalProject','totalTraining','totalSertifikasi'));
    }
    
    public function store(Request $request)
    {
        // Handle Form A1 (Only if customCriteriaA1 exists and not empty)
        if ($request->has('customCriteriaA1') && !empty($request->input('customCriteriaA1'))) {
            foreach ($request->input('customCriteriaA1') as $index => $criteria) {
                if (!empty($criteria)) {
                    Assessment::create([
                        'user_id' => auth()->user()->id,
                        'appraisal_id' => $request->input('appraisal_id'),
                        'form_type' => 'A1',
                        'criteria' => $criteria,
                        'work_targets' => $request->input('work_targetsA1')[$index] ?? null,  
                        'performance_achievements' => $request->input('performance_achievementsA1')[$index] ?? null,  
                        'self_assessment' => $request->input('form_selfA1')[$index] ?? null,  
                        'year' => \Carbon\Carbon::now()->year,
                    ]);
                }
            }
        }

        // Handle Form A2 (Only if customCriteriaA2 exists and not empty)
        if ($request->has('customCriteriaA2') && !empty($request->input('customCriteriaA2'))) {
            foreach ($request->input('customCriteriaA2') as $index => $criteria) {
                if (!empty($criteria)) {
                    Assessment::create([
                        'user_id' => auth()->user()->id,
                        'appraisal_id' => $request->input('appraisal_id'),
                        'form_type' => 'A2',
                        'project_name' => $request->input('project_nameA')[$index] ?? null,  
                        'supervisor_id' => $request->input('supervisor_idA')[$index] ?? null,
                        'criteria' => $criteria,
                        'work_targets' => $request->input('work_targetsA2')[$index] ?? null,  
                        'performance_achievements' => $request->input('performance_achievementsA2')[$index] ?? null,  
                        'self_assessment' => $request->input('form_selfA2')[$index] ?? null,  
                        'year' => \Carbon\Carbon::now()->year,
                    ]);
                }
            }
        }

        // Handle Form C (Check if not empty)
        if ($request->has('formC') && !empty($request->input('formC'))) {
            foreach ($request->input('formC') as $criteriaId => $ratings) {
                if (!empty($ratings)) {
                    Assessment::create([
                        'user_id' => auth()->user()->id,
                        'appraisal_id' => $request->input('appraisal_id'),
                        'form_type' => 'C',
                        'criteria_id' => $criteriaId,
                        'self_assessment' => $ratings['_self'] ?? null,
                        'supervisor_assessment' => $ratings['_supervisor'] ?? null,
                        'final_assessment' => $ratings['_final'] ?? null,
                        'comment' => $request->input("comment_formC.$criteriaId"),
                        'year' => \Carbon\Carbon::now()->year,
                    ]);
                }
            }
        }

        // Handle Form D (Check if not empty)
        if ($request->has('formD') && !empty($request->input('formD'))) {
            foreach ($request->input('formD') as $criteriaId => $ratings) {
                if (!empty($ratings)) {
                    Assessment::create([
                        'user_id' => auth()->user()->id,
                        'appraisal_id' => $request->input('appraisal_id'),
                        'form_type' => 'D',
                        'criteria_id' => $criteriaId,
                        'self_assessment' => $ratings['_self'] ?? null,
                        'supervisor_assessment' => $ratings['_supervisor'] ?? null,
                        'final_assessment' => $ratings['_final'] ?? null,
                        'comment' => $request->input("comment_formD.$criteriaId"),
                        'year' => \Carbon\Carbon::now()->year,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Form data saved successfully.');
    }


    public function show($id)
    {
        $employee = Employee::where('user_id', $id)->first();
        
        // Ambil semua data respon form berdasarkan user_id
        $formResponses = Assessment::where('user_id', $id)->get();

        // Ambil rata-rata nilai dari Form A1, A2, C, dan D
        $averageA1 = $formResponses->where('form_type', 'A1')->avg('final_assessment');
        $averageA2 = $formResponses->where('form_type', 'A2')->avg('final_assessment');
        $averageC = $formResponses->where('form_type', 'C')->avg('final_assessment');
        $averageD = $formResponses->where('form_type', 'D')->avg('final_assessment');

        // Hitung rata-rata gabungan dari Form A1 dan A2
        $combinedAverageA = ($averageA1 + $averageA2) / 2;

        // Hitung nilai akhir berdasarkan bobot yang diberikan (80% untuk gabungan Form A, 10% untuk Form C, dan 10% untuk Form D)
        $finalA = $combinedAverageA * 0.8;
        $finalC = $averageC * 0.1;
        $finalD = $averageD * 0.1;

        // Total nilai akhir
        $totalFinal = $finalA + $finalC + $finalD;

        // get comment form E
        $commentE = Assessment::where('user_id', $id)->where('form_type', 'E')->first();

        $totalProject = Project::whereHas('projectUsers', function ($query) use ($id) {
            $query->where('user_id', $id);
        })->count();

        $totalTraining = Training::where('employee',$employee->id)->where('training_type', 2)->count();
        $totalSertifikasi = Training::where('employee', $employee->id)->where('training_type', 1)->count();

        // Kirim data ke view
        return view('formResponses.show', compact('formResponses', 'id', 'averageA1', 'averageA2', 'averageC', 'averageD', 'combinedAverageA', 'finalA', 'finalC', 'finalD', 'totalFinal','commentE','totalProject','totalTraining','totalSertifikasi'));
    }



    public function update(Request $request, $id)
    {
        // Update Form A1 - Supervisor Assessment
        if($request->has('formA1_supervisor')){
            foreach($request->formA1_supervisor as $responseId => $value) {
                $response = Assessment::find($responseId);
                $response->supervisor_assessment = $value;
                $response->save();
            }
        }

        // Update Form A1 - Final Assessment
        if($request->has('formA1_final')){
            foreach($request->formA1_final as $responseId => $value) {
                $response = Assessment::find($responseId);
                $response->final_assessment = $value;
                $response->save();
            }
        }

        // Update Form A2 - Supervisor Assessment
        if($request->has('formA2_supervisor')){
            foreach($request->formA2_supervisor as $responseId => $value) {
                $response = Assessment::find($responseId);
                $response->supervisor_assessment = $value;
                $response->save();
            }
        }

        // Update Form A2 - Final Assessment
        if($request->has('formA2_final')){
            foreach($request->formA2_final as $responseId => $value) {
                $response = Assessment::find($responseId);
                $response->final_assessment = $value;
                $response->save();
            }
        }

        // Update Form C - Supervisor Assessment
        if($request->has('formC_supervisor')){
            foreach($request->formC_supervisor as $responseId => $value) {
                $response = Assessment::find($responseId);
                $response->supervisor_assessment = $value;
                $response->save();
            }
        }

        // Update Form C - Final Assessment and Comment
        if($request->has('formC_final')){
            foreach($request->formC_final as $responseId => $value) {
                $response = Assessment::find($responseId);
                $response->final_assessment = $value;
                $response->save();
            }
        }
        if($request->has('formC_comment')){
            foreach($request->formC_comment as $responseId => $value) {
                $response = Assessment::find($responseId);
                $response->comment = $value;
                $response->save();
            }
        }

        // Update Form D - Supervisor Assessment
        if($request->has('formD_supervisor')){
            foreach($request->formD_supervisor as $responseId => $value) {
                $response = Assessment::find($responseId);
                $response->supervisor_assessment = $value;
                $response->save();
            }
        }

        // Update Form D - Final Assessment and Comment
        if($request->has('formD_final')){
            foreach($request->formD_final as $responseId => $value) {
                $response = Assessment::find($responseId);
                $response->final_assessment = $value;
                $response->save();
            }
        }
        if($request->has('formD_comment')){
            foreach($request->formD_comment as $responseId => $value) {
                $response = Assessment::find($responseId);
                $response->comment = $value;
                $response->save();
            }
        }


        // Cek apakah ada entri dengan form_type 'E'
        $formResponse = Assessment::where('user_id', $id)->where('form_type', 'E')->where('year', \Carbon\Carbon::now()->year)->first();

        if ($formResponse) {
            // Jika ada, perbarui data yang ada
            $formResponse->update([
                'performance_progress' => $request->input('formE_kemajuan_kinerja', $formResponse->performance_progress),
                'barriers' => $request->input('formE_hambatan', $formResponse->barriers),
                'follow_up' => $request->input('formE_tindak_lanjut', $formResponse->follow_up),
                'comment' => $request->input('formE_comment', $formResponse->comment),
                'advantages' => $request->input('formE_kelebihan', $formResponse->advantages),
                'tiers' => $request->input('formE_tingkatan', $formResponse->tiers),
                'training_plan' => $request->input('formE_pelatihan', $formResponse->training_plan),
                'appraisal_id' => auth()->user()->employee->id,
            ]);
        } else {
            // Jika tidak ada, buat data baru
            Assessment::create([
                'form_type' => 'E',
                'user_id' => $id,
                'performance_progress' => $request->input('formE_kemajuan_kinerja'),
                'barriers' => $request->input('formE_hambatan'),
                'follow_up' => $request->input('formE_tindak_lanjut'),
                'comment' => $request->input('formE_comment'),
                'advantages' => $request->input('formE_kelebihan'),
                'tiers' => $request->input('formE_tingkatan'),
                'training_plan' => $request->input('formE_pelatihan'),
                'year' => \Carbon\Carbon::now()->year,
            ]);
        }


        return redirect()->back()->with('success', 'Form data updated successfully.');
    }


    public function downloadPdf($id)
    {
        // Ambil data form dari database
        $formResponses = Assessment::where('user_id', $id)->get();
        // Ambil rata-rata nilai dari Form A1, A2, C, dan D
        $averageA1 = $formResponses->where('form_type', 'A1')->avg('supervisor_assessment');
        $averageA2 = $formResponses->where('form_type', 'A2')->avg('supervisor_assessment');
        $averageC = $formResponses->where('form_type', 'C')->avg('supervisor_assessment');
        $averageD = $formResponses->where('form_type', 'D')->avg('supervisor_assessment');

        // Hitung rata-rata gabungan dari Form A1 dan A2
        $combinedAverageA = ($averageA1 + $averageA2) / 2;

        // Hitung nilai akhir berdasarkan bobot yang diberikan (80% untuk gabungan Form A, 10% untuk Form C, dan 10% untuk Form D)
        $finalA = $combinedAverageA * 0.8;
        $finalC = $averageC * 0.1;
        $finalD = $averageD * 0.1;

        // Total nilai akhir
        $totalFinal = $finalA + $finalC + $finalD;

        $commentE = Assessment::where('user_id', $id)->where('form_type', 'E')->first();

        // Siapkan data untuk view
        $data = [
            'formResponses' => $formResponses,
            'combinedAverageA' => $combinedAverageA, // Misal nilai rata-rata form A
            'averageC' => $averageC, // Misal nilai rata-rata form C
            'averageD' => $averageD, // Misal nilai rata-rata form D
            'finalA' => $finalA, // Nilai akhir form A
            'finalC' => $finalC, // Nilai akhir form C
            'finalD' => $finalD, // Nilai akhir form D
            'totalFinal' => $totalFinal, // Total nilai akhir
            'commentE' => $commentE
        ];

        // Render view HTML menjadi string
        $html = view('formResponses.form-pdf', $data)->render();

        // Buat instance Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');

        // Render ke PDF
        $dompdf->render();

        // Unduh PDF
        return $dompdf->stream("form-e-laporan.pdf");
    }

    public function assessment($id)
    {
        $employee = Employee::where('user_id', $id)->first();
        
        // Ambil semua data respon form berdasarkan user_id
        $formResponses = Assessment::where('user_id', $id)->get();

        // Ambil rata-rata nilai dari Form A1, A2, C, dan D
        $averageA1 = $formResponses->where('form_type', 'A1')->avg('final_assessment');
        $averageA2 = $formResponses->where('form_type', 'A2')->avg('final_assessment');
        $averageC = $formResponses->where('form_type', 'C')->avg('final_assessment');
        $averageD = $formResponses->where('form_type', 'D')->avg('final_assessment');

        // Hitung rata-rata gabungan dari Form A1 dan A2
        $combinedAverageA = ($averageA1 + $averageA2) / 2;

        // Hitung nilai akhir berdasarkan bobot yang diberikan (80% untuk gabungan Form A, 10% untuk Form C, dan 10% untuk Form D)
        $finalA = $combinedAverageA * 0.8;
        $finalC = $averageC * 0.1;
        $finalD = $averageD * 0.1;

        // Total nilai akhir
        $totalFinal = $finalA + $finalC + $finalD;

        // get comment form E
        $commentE = Assessment::where('user_id', $id)->where('form_type', 'E')->first();

        $totalProject = Project::whereHas('projectUsers', function ($query) use ($id) {
            $query->where('user_id', $id);
        })->count();

        $totalTraining = Training::where('employee',$employee->id)->where('training_type', 2)->count();
        $totalSertifikasi = Training::where('employee', $employee->id)->where('training_type', 1)->count();

        // Kirim data ke view
        return view('formResponses.assessment', compact('formResponses', 'id', 'averageA1', 'averageA2', 'averageC', 'averageD', 'combinedAverageA', 'finalA', 'finalC', 'finalD', 'totalFinal','commentE','totalProject','totalTraining','totalSertifikasi'));
    }



}

