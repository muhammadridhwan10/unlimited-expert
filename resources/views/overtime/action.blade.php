{{ Form::open(['url' => 'overtime/changeaction', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table">
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>{{ !empty($user->name) ? $user->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Project Name') }}</th>
                    <td>{{ !empty($project->project_name) ? $project->project_name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Start Date') }}</th>
                    <td>{{ date('d-m-Y', strtotime($overtime->start_date)) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Start Time') }}</th>
                    <td>
                        <input type="time" name="start_time" id="start_time" class="form-control" value="{{ $overtime->start_time != '00:00:00' ? $overtime->start_time : '' }}" required>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('End Time') }}</th>
                    <td>
                        <input type="time" name="end_time" id="end_time" class="form-control" value="{{ $overtime->end_time != '00:00:00' ? $overtime->end_time : '' }}" required>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Initial Total Time') }}</th>
                    <td>
                        <span id="initial_total_time">
                            {{ \App\Models\UserOvertime::calculateTimeDifference($overtime->start_time, $overtime->end_time) . ' H' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Updated Total Time') }}</th>
                    <td>
                        <span id="updated_total_time">
                            {{ \App\Models\UserOvertime::calculateTimeDifference($overtime->start_time, $overtime->end_time) . ' H' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Note') }}</th>
                    <td>{{ !empty($overtime->note) ? $overtime->note : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>{{ !empty($overtime->status) ? $overtime->status : '' }}</td>
                </tr>
                <input type="hidden" value="{{ $overtime->id }}" name="overtime_id">
            </table>
            <p><strong><em>Note: If the overtime data does not match, please edit the start_time and end_time data to change the overtime data.</em></strong></p>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="submit" value="{{ __('Approval') }}" class="btn btn-success" name="status">
    <input type="submit" value="{{ __('Reject') }}" class="btn btn-danger" name="status">
</div>
{{ Form::close() }}

<script>
    $(document).on('change', '#start_time', function () {
        var start_time = $(this).val();
        var end_time = $('#end_time').val();
        var updatedTotalTimeSpan = $('#updated_total_time');

        console.log(start_time);
        console.log(end_time);

        function calculateTimeDifference() {

            if (start_time && end_time) {
                const start = new Date(`1970-01-01T${start_time}Z`);
                const end = new Date(`1970-01-01T${end_time}Z`);

                console.log(start);

                let difference = (end - start) / 1000; // in seconds

                // Handle case where end time is after midnight
                if (difference < 0) {
                    difference += 24 * 60 * 60; // Add 24 hours in seconds
                }

                const hours = Math.floor(difference / 3600);
                const minutes = Math.floor((difference % 3600) / 60);
                const seconds = difference % 60;

                updatedTotalTimeSpan.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')} H`;
            }
        }

    });

    document.addEventListener('DOMContentLoaded', function() {
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        const updatedTotalTimeSpan = document.getElementById('updated_total_time');

        function calculateTimeDifference() {
            const startTime = startTimeInput.value;
            const endTime = endTimeInput.value;

            if (startTime && endTime) {
                const start = new Date(`1970-01-01T${startTime}Z`);
                const end = new Date(`1970-01-01T${endTime}Z`);

                let difference = (end - start) / 1000; // in seconds

                // Handle case where end time is after midnight
                if (difference < 0) {
                    difference += 24 * 60 * 60; // Add 24 hours in seconds
                }

                const hours = Math.floor(difference / 3600);
                const minutes = Math.floor((difference % 3600) / 60);
                const seconds = difference % 60;

                updatedTotalTimeSpan.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')} H`;
            }
        }

        startTimeInput.addEventListener('change', calculateTimeDifference);
        endTimeInput.addEventListener('change', calculateTimeDifference);
    });
</script>
