{{Form::open(array('url'=>'overtime/changeaction','method'=>'post'))}}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
                <table class="table">
                    <tr role="row">
                        <th>{{__('Employee')}}</th>
                        <td>{{ !empty($user->name)?$user->name:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Project Name ')}}</th>
                        <td>{{ !empty($project->project_name)?$project->project_name:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Start Date')}}</th>
                        <td>{{date("d-m-Y",strtotime($overtime->start_date))}}</td>
                    </tr>
                    <tr>
                        <th>{{__('Start Time')}}</th>
                        <td>{{ ($overtime->start_time !='00:00:00') ?\Auth::user()->timeFormat( $overtime->start_time):'00:00' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('End Time')}}</th>
                        <td>{{ ($overtime->end_time !='00:00:00') ?\Auth::user()->timeFormat( $overtime->end_time):'00:00' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Note')}}</th>
                        <td>{{ !empty($overtime->note)?$overtime->note:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Status')}}</th>
                        <td>{{ !empty($overtime->status)?$overtime->status:'' }}</td>
                    </tr>
                    <input type="hidden" value="{{ $overtime->id }}" name="overtime_id">
                </table>
        </div>
        
    </div>
</div>
<div class="modal-footer">
    <input type="submit" value="{{__('Approval')}}" class="btn btn-success" data-bs-dismiss="modal" name="status">
    <input type="submit" value="{{__('Reject')}}" class="btn btn-danger" name="status">
</div>
{{Form::close()}}
