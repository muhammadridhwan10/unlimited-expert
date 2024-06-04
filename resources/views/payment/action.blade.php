{{Form::open(array('url'=>'payment/changeaction','method'=>'post'))}}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
                <table class="table">
                    <tr role="row">
                        <th>{{__('Issue Date')}}</th>
                        <td>{{date("d-m-Y",strtotime($payment->date))}}</td>
                    </tr>
                    <tr>
                        <th>{{__('Partner Name ')}}</th>
                        <td>{{ !empty($user->name)?$user->name:'-' }}</td>
                    </tr>
                    {{-- <tr>
                        <th>{{__('CoA Code')}}</th>
                        <td>{{ !empty($account->code)?$account->code:'-' }}</td>
                    </tr> --}}
                    {{-- <tr>
                        <th>{{__('CoA Name')}}</th>
                        <td>{{ !empty($account->name)?$account->name:'-' }}</td>
                    </tr> --}}
                    <tr>
                        <th>{{__('Amount Before Tax')}}</th>
                        <td>{{  $payment->currency . '' . number_format($payment->amount_before_tax)}}</td>
                    </tr>
                    <tr>
                        <th>{{__('Tax (%)')}}</th>
                        <td>{{ !empty($payment->tax)?$payment->tax:'-'}}</td>
                    </tr>
                    <tr>
                        <th>{{__('Amount After Tax')}}</th>
                        <td>{{  $payment->currency . '' . number_format($payment->amount)}}</td>
                    </tr>
                    <input type="hidden" value="{{ $payment->id }}" name="payment_id">
                </table>
        </div>
        
    </div>
</div>
<div class="modal-footer">
    <input type="submit" value="{{__('Approved')}}" class="btn btn-success" data-bs-dismiss="modal" name="status">
    <input type="submit" value="{{__('Reject')}}" class="btn btn-danger" name="status">
    <input type="submit" value="{{__('Paid')}}" class="btn btn-success" data-bs-dismiss="modal" name="status">
</div>
{{Form::close()}}
