<div class="modal-body">
    <div class="row mt-2">
        <table class="table datatable">
            <thead>
            <tr>
                <th>{{__('Start Date')}}</th>
                <th>{{__('Start Time')}}</th>
                <th>{{__('End Time')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($employee_overtime as $employee_overtimes)
                <tr>
                    <td>{{date("l, d-m-Y",strtotime($employee_overtimes->start_date))}}</td>
                    <td>{{!empty($employee_overtimes->start_time)?$employee_overtimes->start_time:''}}</td>
                    <td>{{!empty($employee_overtimes->end_time)?$employee_overtimes->end_time:''}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
