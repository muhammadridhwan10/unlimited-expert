{{ Form::open(['route' => ['projectorder.sent', $projectOrder->id], 'method' => 'get']) }}
<div class="modal-body">
<div class="col-md-12">
    <div class="form-group">
        {{ Form::label('approval', __('Partners'),['class'=>'form-label']) }}<span class="text-danger">*</span>
        {{ Form::select('approval', $partners,null, array('class' => 'form-control select','required'=>'required')) }}
    </div>
</div>
<div class="form-group">
    <label for="cc_email">{{__('CC Email')}}</label>
    <textarea class="form-control" id="cc_email" name="cc_email" placeholder="Enter CC Email(s)"></textarea>
</div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Send')}}" class="btn btn-primary">
</div>
{{Form::close()}}
