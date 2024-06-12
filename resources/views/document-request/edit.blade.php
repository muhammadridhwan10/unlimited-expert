{{ Form::model($document_request, ['route' => ['document-request.update', $document_request->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'client' || \Auth::user()->type == 'staff_client')
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('employee_id',__('Employee') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                {{Form::select('employee_id',$employees,null,array('class'=>'form-control select2','id'=>'employee_id','placeholder'=>__('Select Employee')))}}
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('approval',__('Approval By') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                {{Form::select('approval',$approval,null,array('class'=>'form-control select2','id'=>'approval','placeholder'=>__('Select Approval')))}}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('document_type',__('Document Type') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                    <select name="document_type" id="document_type" class="form-control main-element">
                        <option value="0">{{__('Select Document Type')}}</option>
                        @foreach(\App\Models\DocumentRequest::$document_type as $k => $v)
                            <option value="{{$k}}" {{ $document_request->document_type == $k ? 'selected' : '' }}>{{__($v)}}</option>
                        @endforeach
                    </select>
            </div>
        </div>
    </div>

    <div id="client_fields" style="display: none;">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('client_name_invoice', __('Client Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('client_name_invoice', $document_request->client_name, array('class'=>'form-control'))}}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('email_attention_invoice', __('Email Attention'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('email_attention_invoice', $document_request->email_attention, array('class'=>'form-control'))}}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('name_attention_invoice', __('Name Attention'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('name_attention_invoice', $document_request->name_attention, array('class'=>'form-control'))}}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('position_attention_invoice', __('Position Attention'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('position_attention_invoice', $document_request->position_attention, array('class'=>'form-control'))}}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('address_invoice', __('Address'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('address_invoice', $document_request->address, array('class'=>'form-control'))}}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('no_pic_invoice', __('Phone Number PIC'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('no_pic_invoice', $document_request->no_pic, array('class'=>'form-control'))}}
                </div>
            </div>
        </div>
    </div>

    <div id="proposal_fields" style="display: none;">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('client_name_proposal', __('Client Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('client_name_proposal', $document_request->client_name, array('class'=>'form-control'))}}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('email_attention_proposal', __('Email Attention'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('email_attention_proposal', $document_request->email_attention, array('class'=>'form-control'))}}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('name_attention_proposal', __('Name Attention'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('name_attention_proposal', $document_request->name_attention, array('class'=>'form-control'))}}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('position_attention_proposal', __('Position Attention'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('position_attention_proposal', $document_request->position_attention, array('class'=>'form-control'))}}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('address_proposal', __('Address'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('address_proposal', $document_request->address, array('class'=>'form-control'))}}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('service_type_proposal', __('Service Type'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <select class="form-control select2" id="service_type_proposal" name="service_type_proposal">
                        @foreach($category as $categoryId => $categoryName)
                            <option value="{{ $categoryId }}">{{ $categoryName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('period_proposal', __('Period'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('period_proposal', $document_request->period, array('class'=>'form-control'))}}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('termin1_proposal', __('Termin 1'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('termin1_proposal', $document_request->termin1, array('class'=>'form-control'))}}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('termin2_proposal', __('Termin 2'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('termin2_proposal', $document_request->termin2, array('class'=>'form-control'))}}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('termin3_proposal', __('Termin 3'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('termin3_proposal', $document_request->termin3, array('class'=>'form-control'))}}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('fee_proposal', __('Fee'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('fee_proposal', $document_request->fee, array('class'=>'form-control'))}}
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('pph23_proposal', __('PPh 23'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{Form::text('pph23_proposal', $document_request->pph23, array('class'=>'form-control'))}}
                </div>
            </div>
        </div>
    </div>

    <div id="barcode_fields" style="display: none;">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('client_name_barcode', __('Client Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('client_name_barcode', $document_request->client_name, array('class'=>'form-control'))}}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('note', __('Note'),['class'=>'form-label']) }}
                {{Form::textarea('note', $document_request->note, array('class'=>'form-control','placeholder'=>__('Note')))}}
            </div>
        </div>
    </div>

    <div class="col-12 text-end">
        <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
    </div>
</div>
{{ Form::close() }}

<script>
    $(document).ready(function(){
        $('#document_type').change(function(){
            var documentTypeSelect = $(this).val();
            if(documentTypeSelect === 'Invoice'){
                $('#client_fields').show();
                $('#proposal_fields').hide();
                $('#barcode_fields').hide();
                $('#client_fields input').attr('name', function() {
                    return $(this).attr('data-name-invoice');
                });
            } else if(documentTypeSelect === 'Proposal' || documentTypeSelect === 'EL'){
                $('#proposal_fields').show();
                $('#client_fields').hide();
                $('#barcode_fields').hide();
                $('#proposal_fields input').attr('name', function() {
                    return $(this).attr('data-name-proposal');
                });
            } else if(documentTypeSelect === 'Barcode LAI'){
                $('#barcode_fields').show();
                $('#client_fields').hide();
                $('#proposal_fields').hide();
                $('#barcode_fields input').attr('name', function() {
                    return $(this).attr('data-name-barcode');
                });
            } else {
                $('#client_fields').hide();
                $('#proposal_fields').hide();
                $('#barcode_fields').hide();
            }
        });
    });
</script>
