{{Form::open(array('url'=>'reimbursment-personal/changeaction','method'=>'post'))}}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
                <table class="table">
                    <tr role="row">
                        <th>{{__('Employee')}}</th>
                        <td>{{ !empty($user->name)?$user->name:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Client Name ')}}</th>
                        <td>{{ !empty($reimbursment->client->name)?$reimbursment->client->name:'' }}</td>
                    </tr>
                     <tr>
                        <th>{{__('Reimbursement Type')}}</th>
                        <td>{{ !empty($reimbursment->reimbursment_type)?$reimbursment->reimbursment_type:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Date')}}</th>
                        <td>{{date("d-m-Y",strtotime($reimbursment->date))}}</td>
                    </tr>
                    <tr>
                        <th>{{__('Amount')}}</th>
                        <td>{{!empty(number_format($reimbursment->amount))?number_format($reimbursment->amount):'-'}}</td>
                    </tr>
                     <tr>
                        <th>{{__('Description')}}</th>
                        <td>{{ !empty($reimbursment->description)?$reimbursment->description:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Status')}}</th>
                        <td>{{ !empty($reimbursment->status)?$reimbursment->status:'' }}</td>
                    </tr>
                    <input type="hidden" value="{{ $reimbursment->id }}" name="reimbursment_id">
                </table>
        </div>
        
    </div>
</div>
<div class="modal-footer">
    <input type="submit" value="{{__('Paid')}}" class="btn btn-success" data-bs-dismiss="modal" name="status">
    <input type="submit" value="{{__('Reject')}}" class="btn btn-danger" name="status">
</div>
{{Form::close()}}
