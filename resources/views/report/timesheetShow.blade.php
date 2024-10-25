<div class="modal-body">
    <div class="row mt-2">
        <table class="table datatable">
            <thead>
            <tr>
                <th> {{__('Project')}}</th>
                <th> {{__('Date')}}</th>
                <th> {{__('Time')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($employee_timesheet as $employee_timesheets)
                <tr>
                    <td>{{!empty($employee_timesheets->project->project_name)?$employee_timesheets->project->project_name:'-'}}</td>
                    <td>{{date("l, d-m-Y",strtotime($employee_timesheets->date))}}</td>
                    <td>{{date("H:i:s",strtotime($employee_timesheets->time))}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
