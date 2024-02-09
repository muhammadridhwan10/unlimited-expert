
{{ Form::model($client, array('route' => array('clients.update', $client->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <h6>{{__('--Basic Info--')}}</h6>
        <br>
        <br>
        <div class="form-group">
            {{ Form::label('name', __('Name Company'),['class'=>'form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','placeholder'=>__('Enter client Name'),'required'=>'required')) }}
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('npwp', __('NPWP'), ['class' => 'form-label']) }}
                {{ Form::text('npwp', $clients ? $clients->npwp : '', ['class' => 'form-control', 'placeholder' => __('Enter Client NPWP')]) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('telp', __('Telephone'),['class'=>'form-label']) }}
                {{ Form::number('telp', $clients ? $clients->telp : '', array('class' => 'form-control','placeholder'=>__('Enter Client Telephone'))) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('address', __('Address'),['class' => 'form-label']) }}
                <small class="form-text text-muted mb-2 mt-0">{{__('This textarea will autosize while you type')}}</small>
                {{ Form::textarea('address', $clients ? $clients->address : '', ['class' => 'form-control','rows'=>'1','data-toggle' => 'autosize']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('country', __('Country'),['class'=>'form-label']) }}
                {{ Form::text('country', $clients ? $clients->country : '', array('class' => 'form-control','placeholder'=>__('Enter Client Country'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('state', __('State'),['class'=>'form-label']) }}
                {{ Form::text('state', $clients ? $clients->state : '', array('class' => 'form-control','placeholder'=>__('Enter Client State'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('city', __('City'),['class'=>'form-label']) }}
                {{ Form::text('city', $clients ? $clients->city : '', array('class' => 'form-control','placeholder'=>__('Enter Client City'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('client_business_sector_id', __('Client Business Sector'),['class'=>'form-label']) }}
                {{ Form::select('client_business_sector_id', $businesssector,null, array('class' => 'form-control select')) }}
            </div>
        </div>
        <h6>{{__('--Invoice--')}}</h6>
        <br>
        <br>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('email', __('E-Mail'),['class'=>'form-label']) }}
                {{ Form::email('email', null, array('class' => 'form-control','placeholder'=>__('Enter Client Email For Invoice'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('name_invoice', __('Attention'),['class'=>'form-label']) }}
                {{ Form::text('name_invoice', $clients ? $clients->name_invoice : '', array('class' => 'form-control','placeholder'=>__('Enter Client Name For Invoice'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('position', __('Position'),['class'=>'form-label']) }}
                {{ Form::text('position', $clients ? $clients->position : '', array('class' => 'form-control','placeholder'=>__('Enter Client Position For Invoice'))) }}
            </div>
        </div>

        @if(!$customFields->isEmpty())
            @include('custom_fields.formBuilder')
        @endif

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>

{{Form::close()}}


