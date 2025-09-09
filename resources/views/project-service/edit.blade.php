{{ Form::model($serviceType, ['route' => ['project-service.update', $serviceType->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('name', __('Service Type Name'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter service type name')]) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('code', __('Code'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('code', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter unique code')]) }}
                <small class="form-text text-muted">{{ __('Unique identifier for this service type. Use lowercase letters and underscores only.') }}</small>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Enter description (optional)')]) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('sort_order', __('Sort Order'), ['class' => 'form-label']) }}
                {{ Form::number('sort_order', null, ['class' => 'form-control', 'min' => '0', 'placeholder' => __('Enter sort order')]) }}
                <small class="form-text text-muted">{{ __('Lower numbers appear first in dropdowns.') }}</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-check form-switch mt-4">
                    {{ Form::checkbox('is_active', 1, $serviceType->is_active, ['class' => 'form-check-input', 'id' => 'is_active']) }}
                    {{ Form::label('is_active', __('Active'), ['class' => 'form-check-label']) }}
                </div>
                <small class="form-text text-muted">{{ __('Only active service types will appear in project forms.') }}</small>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}