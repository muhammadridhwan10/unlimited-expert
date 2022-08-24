{{ Form::open(array('url' => 'contract')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('client_name', __('Client'),['class'=>'form-label']) }}
            {{ Form::select('client_name', $clients,null, array('class' => 'form-control','data-toggle="select"','required'=>'required')) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('subject', __('Subject'),['class'=>'form-label']) }}
            {{ Form::text('subject', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Contract Type'),['class'=>'form-label']) }}
            {{ Form::select('type', $contractTypes,null, array('class' => 'form-control','data-toggle="select"','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('value', __('Contract Value'),['class'=>'form-label']) }}
            {{ Form::number('value', '', array('class' => 'form-control','required'=>'required','stage'=>'0.01')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('start_date', __('Start Date'),['class'=>'form-label']) }}
            {{ Form::date('start_date', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('end_date', __('End Date'),['class'=>'form-label']) }}
            {{ Form::date('end_date', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'3']) !!}
        </div>
    </div>
</div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}

