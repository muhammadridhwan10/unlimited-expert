<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form E Report</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        h3 {
            text-align: center; /* Menengah-kan judul laporan */
        }
        .signature {
            margin-top: 50px;
            width: 100%;
        }
        .signature div {
            display: inline-block;
            width: 45%;
            text-align: center;
        }
        .signature div p {
            margin-top: 60px; /* Jarak kosong untuk tanda tangan */
        }
    </style>
</head>
<body>
    <h3>Annual Assessment Final Report</h3>
    <table class="mt-4">
        <thead>
            <tr>
                <th>{{__('FORM')}}</th>
                <th>{{__('AVERAGE SCORE')}}</th>
                <th>{{__('WEIGHT')}}</th>
                <th>{{__('FINAL SCORE')}}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Work Target Achievement (Form A1 & A2)</td>
                <td>{{ number_format($combinedAverageA, 2) }}</td>
                <td>80%</td>
                <td>{{ number_format($finalA, 2) }}</td>
            </tr>
            <tr>
                <td>Core Competencies (Form C)</td>
                <td>{{ number_format($averageC, 2) }}</td>
                <td>10%</td>
                <td>{{ number_format($finalC, 2) }}</td>
            </tr>
            <tr>
                <td>Managerial Competence (Form D)</td>
                <td>{{ number_format($averageD, 2) }}</td>
                <td>10%</td>
                <td>{{ number_format($finalD, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">{{__('TOTAL FINAL SCORE')}}</th>
                <th>{{ number_format($totalFinal, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <h4>{{__('COMMENTS')}}</h4>
    <p>{{ $commentE->comment ?? 'Comment Not Found' }}</p>

    <h4 class="mt-4">Evaluasi Mid Season</h4>
    <table class="mt-4">
        <thead>
            <tr>
                <th>{{__('PERFORMANCE PROGRESS')}}</th>
                <th>{{__('BARRIERS TO ACHIEVING TARGETS')}}</th>
                <th>{{__('FOLLOW-UP ACTIONS')}}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($formResponses->where('form_type', 'E') as $response)
            <tr>
                <td>{!! nl2br(e($response->performance_progress)) !!}</td>
                <td>{!! nl2br(e($response->barriers)) !!}</td>
                <td>{!! nl2br(e($response->follow_up)) !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h4 class="mt-4">Evaluasi Final Season</h4>
    <h4>{{__('Development Plan')}}</h4>
    <table class="mt-4">
        <thead>
            <tr>
                <th>{{__('STRENGTHS / ADVANTAGES')}}</th>
                <th>{{__('AREAS FOR IMPROVEMENT')}}</th>
                <th>{{__('DEVELOPMENT / TRAINING PLAN')}}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($formResponses->where('form_type', 'E') as $response)
            <tr>
                <td>{!! nl2br(e($response->advantages)) !!}</td>
                <td>{!! nl2br(e($response->tiers)) !!}</td>
                <td>{!! nl2br(e($response->training_plan)) !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        <div>
            <p>_________________________</p>
            <p>{{__('SELF SIGN')}}</p>
        </div>
        <div>
            <p>_________________________</p>
             <p>{{__('SUPERVISOR SIGN')}}</p>
        </div>
    </div>
    <div class="penjelasan-nilai">
        <p><strong>3.5 - 4.0:</strong> Exceed Expectations<br>
        Pegawai mencapai lebih dari 100% dari target kerja yang ditetapkan. Perilaku diterapkan secara konsisten di lingkungan kerja sehari-hari.</p>

        <p><strong>2.5 - 3.4:</strong> Meet Expectations<br>
        Pegawai mencapai 90% - 100% dari target kerja yang ditetapkan. Perilaku diterapkan, meskipun tidak konsisten.</p>

        <p><strong>1.5 - 2.4:</strong> Below Expectations<br>
        Pegawai mencapai 50% - 89% dari target kerja yang ditetapkan. Perilaku sudah sering terlihat diterapkan di lingkungan kerja.</p>

        <p><strong>1.0 - 1.4:</strong> Significantly Below Expectations<br>
        Pegawai mencapai kurang dari 50% dari target kerja yang ditetapkan. Perilaku tidak diterapkan di lingkungan kerja.</p>
    </div>
</body>
</html>
