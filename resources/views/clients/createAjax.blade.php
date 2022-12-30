<div class="card bg-none card-box">
    {{ Form::open(array('url' => 'clients')) }}
    <div class="row">
        <div class="col-6 form-group">
            {{ Form::label('name', __('Name'),['class'=>'form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('email', __('E-Mail Address'),['class'=>'form-label']) }}
            {{ Form::email('email', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('password', __('Password'),['class'=>'form-label']) }}
            {{ Form::password('password', null, array('class' => 'form-control','required'=>'required')) }}
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
                {{ Form::label('client_business_sector', __('Client Business Sector'),['class'=>'form-label']) }}
                {{ Form::text('client_business_sector', null, array('class' => 'form-control','placeholder'=>__('Enter Client Business Sector'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('client_ownership_status', __('Client Ownership Status'),['class'=>'form-label']) }}
                {{ Form::text('client_ownership_status', null, array('class' => 'form-control','placeholder'=>__('Enter Client Ownership Status'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('book_year', __('Book Year'),['class'=>'form-label']) }}
                {{ Form::text('book_year', null, array('class' => 'form-control','placeholder'=>__('Enter Book Year'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('engagement_type', __('Engagement Type'),['class'=>'form-label']) }}
                {{ Form::text('engagement_type', null, array('class' => 'form-control','placeholder'=>__('Enter Engagement Type'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('engagement_types', __('Engagement Types'),['class'=>'form-label']) }}
                {{ Form::text('engagement_types', null, array('class' => 'form-control','placeholder'=>__('Engagement Types'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('auditing_standard', __('Auditing Standard'),['class'=>'form-label']) }}
                {{ Form::text('auditing_standard', null, array('class' => 'form-control','placeholder'=>__('Enter Auditing Standard'))) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('client_accounting_standard', __('Client Accounting Standard'),['class'=>'form-label']) }}
                {{ Form::text('client_accounting_standard', null, array('class' => 'form-control','placeholder'=>__('Enter Client Accounting Standard'))) }}
            </div>
        </div>

        <div class="form-group mt-4 mb-0">
            {{ Form::hidden('ajax',true) }}
            <input type="submit" value="{{__('Create')}}" class="btn-create badge-blue">
        </div>
    </div>
    {{ Form::close() }}
</div>
