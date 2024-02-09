{{ Form::open(['route' => ['invoice.payment.reminder', $invoice->id], 'method' => 'get']) }}
<div class="modal-body">
<div class="form-group">
    <textarea class="form-control" id="cc_email" name="cc_email" placeholder="Enter CC Email(s)"></textarea>
</div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Send')}}" class="btn btn-primary">
</div>
</div>
{{Form::close()}}


