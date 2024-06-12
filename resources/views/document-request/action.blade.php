{{Form::open(array('url'=>'document-request/changeaction','method'=>'post','enctype' => 'multipart/form-data'))}}
<style>
.text-wrap {
    word-wrap: break-word; 
    word-break: break-all;
    white-space: normal;
}
.print-content {
    width: 10cm; /* Lebar border */
    height: 3.8cm; /* Tinggi border */
    padding: 10px;
    font-size: 11px; /* Ukuran font diubah menjadi 11 */
    word-wrap: break-word;
    border: 1px solid black; /* Border ditambahkan */
    box-sizing: border-box; /* Memastikan padding termasuk dalam ukuran border */
}
</style>
<div class="modal-body">
    <div class="row">
        <div class="col-12">
                <table class="table">
                    <tr role="row">
                        <th>{{__('Employee Request')}}</th>
                        <td>{{ !empty($employee->name)?$employee->name:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Document Type')}}</th>
                        <td>{{ !empty($document->document_type)?$document->document_type:'' }}</td>
                    </tr>
                    @if($document->document_type == 'Invoice')
                    <tr>
                        <th>{{__('Client Name')}}</th>
                        <td>{{ !empty($document->client_name)?$document->client_name:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Email Attention')}}</th>
                        <td>{{ !empty($document->email_attention)?$document->email_attention:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Name Attention (PIC)')}}</th>
                        <td>{{ !empty($document->name_attention)?$document->name_attention:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Position Attention (PIC)')}}</th>
                        <td>{{ !empty($document->position_attention)?$document->position_attention:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Client Address')}}</th>
                        <td class="text-wrap">{{ !empty($document->address)?$document->address:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Phone Number (PIC)')}}</th>
                        <td>{{ !empty($document->no_pic)?$document->no_pic:'' }}</td>
                    </tr>
                    @elseif($document->document_type == 'Proposal' || $document->document_type == 'EL')
                    <tr>
                        <th>{{__('Client Name')}}</th>
                        <td>{{ !empty($document->client_name)?$document->client_name:'-' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Client Address')}}</th>
                        <td class="text-wrap">{{ !empty($document->address)?$document->address:'-' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Name Attention (PIC)')}}</th>
                        <td>{{ !empty($document->name_attention)?$document->name_attention:'-' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Position Attention (PIC)')}}</th>
                        <td>{{ !empty($document->position_attention)?$document->position_attention:'-' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Email Attention')}}</th>
                        <td>{{ !empty($document->email_attention)?$document->email_attention:'-' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Service Type')}}</th>
                        <td>{{ !empty($document->service->name)?$document->service->name:'-' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Termin (%)')}}</th>
                        <td>
                            {{ !empty($document->termin1) ? $document->termin1 . '%' : '-' }},
                            {{ !empty($document->termin2) ? $document->termin2 . '%' : '-' }},
                            {{ !empty($document->termin3) ? $document->termin3 . '%' : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <th>{{__('Fee')}}</th>
                        <td>{{ !empty(\Auth::user()->priceFormat($document->fee))?\Auth::user()->priceFormat($document->fee):'-' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('PPH 23')}}</th>
                        <td>{{ !empty($document->pph23)?$document->pph23:'-' }}</td>
                    </tr>
                    @elseif($document->document_type == 'Contract Employee')
                    <tr>
                        <th>{{ __('File From User') }}</th>
                        <td>
                            @if(!empty($document->file_feedback))
                                @php
                                    $fileUrl = Storage::disk('s3')->url($document->file_feedback);
                                @endphp
                                <a href="{{ $fileUrl }}" target="_blank">Click Here</a>
                            @else
                                {{ '-' }}
                            @endif
                        </td>
                    </tr>
                    @else
                    <tr>
                        <th>{{__('Note')}}</th>
                        <td>{{ !empty($document->note)?$document->note:'' }}</td>
                    </tr>
                    @endif
                    <input type="hidden" value="{{ $document->id }}" name="document_id">
                </table>
        </div>
    </div>
    @if($document->status == 'Pending')
        @if($document->document_type == 'Contract Employee' || $document->document_type == 'Other Letters')
        <div class="row">
            <div class="form-group col-sm-12 col-md-12">
                    {{Form::label('file',__('File'),['class'=>'form-label'])}}<span class="text-danger">*</span>
                    <div class="choose-file form-group">
                        <label for="file" class="form-label">
                            <input type="file" accept=".png, .jpg, .jpeg, .docx, .doc, .xlsx, .xls, .pdf" class="form-control" name="file" id="file" data-filename="filecreate">
                        </label>
                    </div>
            </div>
        </div>
        @endif
    @else
        @if($document->document_type == 'Contract Employee' && $document->file_feedback == NULL)
            <div class="row">
                <div class="form-group col-sm-12 col-md-12">
                        {{Form::label('file_feedback',__('File'),['class'=>'form-label'])}}<span class="text-danger">*</span>
                        <div class="choose-file form-group">
                            <label for="file_feedback" class="form-label">
                                <input type="file" accept=".png, .jpg, .jpeg, .docx, .doc, .xlsx, .xls, .pdf" class="form-control" name="file_feedback" id="file_feedback" data-filename="file_feedbackcreate">
                            </label>
                        </div>
                </div>
            </div>
        @endif
    @endif
    @if($document->document_type == 'Invoice')
        @if($document->status == 'Completed')
        <div class="row">
            <div class="col-sm-12">
                <button type="button" class="btn btn-primary" onclick="openPrintWindow()">Print</button>
            </div>
        </div>
        <div id="printArea" style="display:none;">
            <div class="print-content">
                <p>{{ !empty($document->client_name)?$document->client_name:'' }}</p>
                <p>{{ !empty($document->address)?$document->address:'' }}</p>
                <p>{{ !empty($document->name_attention) && !empty($document->no_pic) ? $document->name_attention . ' (' . $document->no_pic . ')' : (!empty($document->name_attention) ? $document->name_attention : (!empty($document->no_pic) ? $document->no_pic : '')) }}</p>
                <p>{{ !empty($document->note)?$document->note:'' }}</p>
            </div>
        </div>
        @endif
    @endif
</div>
@if($document->status == 'Pending')
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Completed') }}" class="btn btn-primary">
        </div>
@else
    @if($document->document_type == 'Contract Employee'  && $document->file_feedback == NULL)
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Send') }}" class="btn btn-primary">
        </div>
    @endif
@endif
<script>
function openPrintWindow() {
    var printContents = document.getElementById('printArea').innerHTML;
    var newWindow = window.open('', '_blank');
    newWindow.document.write('<html><head><title>Print Label</title>');
    newWindow.document.write('<style>');
    newWindow.document.write('.print-content { width: 10cm; height: 3.8cm; font-size: 11px; padding: 10px; border: 1px solid black; box-sizing: border-box; }');
    newWindow.document.write('</style>');
    newWindow.document.write('</head><body>');
    newWindow.document.write(printContents);
    newWindow.document.write('</body></html>');
    newWindow.document.close();
    newWindow.print();
}
</script>
{{Form::close()}}
