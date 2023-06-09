<div class="modal-body">
    <div class="row mt-2">
        <table class="table datatable">
            <thead>
            <tr>
                <th>{{__('Start Date')}}</th>
                <th>{{__('Start Time')}}</th>
                <th>{{__('End Time')}}</th>
                <th>{{__('Label')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($employee_overtime as $employee_overtimes)
            <?php
            $date = date("l, d-m-Y", strtotime($employee_overtimes->start_date));

            if (date("N", strtotime($employee_overtimes->start_date)) >= 6) {
                $label = "Weekend";
            } else {
                $label = "Weekdays";
                
            }
            ?>
                <tr>
                    <td>{{date("l, d-m-Y",strtotime($employee_overtimes->start_date))}}</td>
                    <td>{{!empty($employee_overtimes->start_time)?$employee_overtimes->start_time:''}}</td>
                    <td>{{!empty($employee_overtimes->end_time)?$employee_overtimes->end_time:''}}</td>
                    <td>
                        @if($label=='Weekend')
                            <i class="badge bg-success p-2 rounded">{{__('Weekend')}}</i>
                        @elseif($label=='Weekdays')
                            <i class="badge bg-danger p-2 rounded">{{__('Weekdays')}}</i>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
