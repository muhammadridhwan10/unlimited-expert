<div class="modal-body">
    <div class="row mt-2">
        <table class="table datatable">
            <thead>
            <tr>
                <th>{{__('Meeting title')}}</th>
                <th>{{__('Meeting Date')}}</th>
                <th>{{__('Meeting Time')}}</th>
                <th>{{__('Meeting With')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($employee_meeting as $meeting)
                <tr>
                    <td>{{ $meeting->title }}</td>
                    <td>{{  \Auth::user()->dateFormat($meeting->date) }}</td>
                    <td>{{  $meeting->time . ' H' }}</td>
                    <td>
                        @php
                            $employeeIds = json_decode($meeting->employee_id);
                            $employeeNames = \App\Models\Employee::whereIn('id', $employeeIds)->pluck('name')->toArray();
                        @endphp
                        {{ implode(', ', $employeeNames) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
