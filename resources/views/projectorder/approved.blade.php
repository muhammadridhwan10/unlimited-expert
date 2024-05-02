{{ Form::open(['route' => ['projectorder.approved', $projectOrder->id], 'method' => 'get']) }}
<div class="modal-body">
    <h6>{{'Does the order qualify as a potential client?'}}</h6>
    <div class="form-check">
        {{ Form::radio('is_fulfilling_prospective_clients', '1', false, ['class' => 'form-check-input', 'id' => 'is_fulfilling_prospective_clients_yes']) }}
        {{ Form::label('is_fulfilling_prospective_clients_yes', __('Yes'), ['class' => 'form-check-label']) }}
    </div>
    <div class="form-check">
        {{ Form::radio('is_fulfilling_prospective_clients', '0', false, ['class' => 'form-check-input', 'id' => 'is_fulfilling_prospective_clients_no']) }}
        {{ Form::label('is_fulfilling_prospective_clients_no', __('No'), ['class' => 'form-check-label']) }}
    </div>
    <br>
    <h6>{{'Is the order qualified?'}}</h6>
    <div class="form-check">
        {{ Form::radio('is_fulfill', '1', false, ['class' => 'form-check-input', 'id' => 'is_fulfill_yes']) }}
        {{ Form::label('is_fulfill_yes', __('Yes'), ['class' => 'form-check-label']) }}
    </div>
    <div class="form-check">
        {{ Form::radio('is_fulfill', '0', false, ['class' => 'form-check-input', 'id' => 'is_fulfill_no']) }}
        {{ Form::label('is_fulfill_no', __('No'), ['class' => 'form-check-label']) }}
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Submit')}}" class="btn btn-primary">
</div>
{{Form::close()}}
