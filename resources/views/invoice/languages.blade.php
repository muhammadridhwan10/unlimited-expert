{{ Form::open(['route' => ['invoice.sent', $invoice->id], 'method' => 'get']) }}
<div class="modal-body">
<p>{{__('Choose language for the invoice:')}}</p>
<div class="form-check">
    <input class="form-check-input" type="radio" name="language" id="english" value="english" checked>
    <label class="form-check-label" for="english">
        {{__('English')}}
    </label>
</div>
<div class="form-check">
    <input class="form-check-input" type="radio" name="language" id="indonesian" value="indonesian">
    <label class="form-check-label" for="indonesian">
        {{__('Indonesian')}}
    </label>
</div>
<br>
<br>
<div class="form-group">
    <label for="cc_email">{{__('CC Email')}}</label>
    <textarea class="form-control" id="cc_email" name="cc_email" placeholder="Enter CC Email(s)"></textarea>
</div>
{{ Form::hidden('selected_language', '', ['id' => 'selected_language']) }}
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Send')}}" class="btn btn-primary">
</div>
{{Form::close()}}
