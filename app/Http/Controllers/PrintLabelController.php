<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class PrintLabelController extends Controller
{
    public function index(Request $request)
    {

        if(\Auth::user()->can('manage document'))
        {
            return view('print-label.index');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function printLabels(Request $request)
    {
        $labels = $request->all();

        // Generate PDF with labels using DOMPDF or any PDF library
        $pdf = Pdf::loadView('print-label.print', ['labels' => $labels]);

        // Set page size and margins for A4 label format
        $pdf->setPaper([0, 0, 595.276, 841.890], 'portrait'); // A4 size in points

        return $pdf->stream('labels.pdf');
    }

}
