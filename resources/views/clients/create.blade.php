{{ Form::open(array('url' => 'clients')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'),['class'=>'form-label']) }}
                {{ Form::text('name', null, array('class' => 'form-control','placeholder'=>__('Enter client Name'),'required'=>'required')) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('email', __('E-Mail Address'),['class'=>'form-label']) }}
                {{ Form::email('email', null, array('class' => 'form-control','placeholder'=>__('Enter Client Email'),'required'=>'required')) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('password', __('Password'),['class'=>'form-label']) }}
                {{Form::password('password',array('class'=>'form-control','placeholder'=>__('Enter User Password'),'required'=>'required','minlength'=>"6"))}}
                @error('password')
                <small class="invalid-password" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('alamat', __('Address'),['class' => 'form-label']) }}
                <small class="form-text text-muted mb-2 mt-0">{{__('This textarea will autosize while you type')}}</small>
                {{ Form::textarea('alamat', null, ['class' => 'form-control','rows'=>'1','data-toggle' => 'autosize']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('npwp', __('NPWP'),['class'=>'form-label']) }}
                {{ Form::text('npwp', null, array('class' => 'form-control','placeholder'=>__('Enter Client Npwp'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('telp', __('Telephone'),['class'=>'form-label']) }}
                {{ Form::number('telp', null, array('class' => 'form-control','placeholder'=>__('Enter Client Telephone'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('client_business_sector_id', __('Client Business Sector'),['class'=>'form-label']) }}
                {{ Form::select('client_business_sector_id', $businesssector,null, array('class' => 'form-control select','required'=>'required')) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('client_ownership_status_id', __('Client Ownership Status'),['class'=>'form-label']) }}
                {{ Form::select('client_ownership_status_id', $ownershipstatus,null, array('class' => 'form-control select','required'=>'required')) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('engagement_type', __('Engagement Type'), ['class' => 'form-label']) }}
                <select name="engagement_type" id="engagement_type" class="form-control main-element">
                    @foreach(\App\Models\User::$engagement_type as $et => $v)
                        <option value="{{$et}}">{{__($v)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('engagement_types', __('Engagement Types'), ['class' => 'form-label']) }}
                <select name="engagement_types" id="engagement_types" class="form-control main-element">
                    @foreach(\App\Models\User::$engagement_types as $ets => $v)
                        <option value="{{$ets}}">{{__($v)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('auditing_standard', __('Auditing Standard'), ['class' => 'form-label']) }}
                <select name="auditing_standard" id="auditing_standard" class="form-control main-element">
                    @foreach(\App\Models\User::$auditing_standard as $as => $v)
                        <option value="{{$as}}">{{__($v)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('client_accounting_standard_id', __('Client Accounting Standard'),['class'=>'form-label']) }}
                {{ Form::select('client_accounting_standard_id', $accountingstandard,null, array('class' => 'form-control select','required'=>'required')) }}
            </div>
        </div>
        @if(!$customFields->isEmpty())
            @include('custom_fields.formBuilder')
        @endif

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>

{{Form::close()}}


