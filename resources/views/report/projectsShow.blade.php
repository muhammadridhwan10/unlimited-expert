<div class="modal-body">
    <div class="row mt-2">
        <table class="table datatable">
            <thead>
            <tr>
                <th>{{__('Project Name')}}</th>
                <th>{{__('Total Working Hours')}}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($project as $projects)
            @php
                $hours_format_number = 0;
                $total_hours = 0;
                $hourdiff_late = 0;
                $esti_late_hour =0;
                $esti_late_hour_chart=0;

                $logged_hours = 0;
                $timesheets = App\Models\Timesheet::where('project_id',$projects->project_id)->where('created_by' ,$projects->user_id)->get();
            @endphp

            @foreach($timesheets as $timesheet)
                @php

                    $hours =  date('H', strtotime($timesheet->time));
                    $minutes =  date('i', strtotime($timesheet->time));
                    $total_hours = $hours + ($minutes/60) ;
                    $logged_hours += $total_hours ;
                    $hours_format_number = number_format($logged_hours, 2, '.', '');
                @endphp
            @endforeach
                <tr>
                    <td>{{!empty($projects->project->project_name)?$projects->project->project_name:''}}</td>
                    <td>{{$hours_format_number . ' H'}}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">{{__('No Data Found.!')}}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
