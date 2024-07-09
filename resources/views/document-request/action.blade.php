{{Form::open(array('url'=>'document-request/changeaction','method'=>'post','enctype' => 'multipart/form-data'))}}
<style>
.text-wrap {
    word-wrap: break-word; 
    word-break: break-all;
    white-space: normal;
}
.print-content {
    width: 10cm; 
    height: 3.8cm; 
    padding: 10px 0.5cm;
    font-size: 11px; 
    word-wrap: break-word;
    border: 1px solid black; 
    box-sizing: border-box; 
    position: absolute; 
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
                @endif
                <tr>
                    <th>{{__('Note')}}</th>
                    <td>{{ !empty($document->note)?$document->note:'' }}</td>
                </tr>
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
                <button type="button" class="btn btn-primary" onclick="openPrintDialog()">Print</button>
            </div>
        </div>
        <div id="printArea" style="display:none;">
            <div class="print-content">
                <p>{{ !empty($document->sender_or_receiver) ? $document->sender_or_receiver . ' :' : '' }}</p>
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
<div class="modal fade" id="printDialog" tabindex="-1" aria-labelledby="printDialogLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printDialogLabel">Select Print Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="printForm">
                    <div class="mb-3">
                        <label for="column" class="form-label">Column:</label>
                        <select id="column" name="column" class="form-select">
                            <option value="1">1</option>
                            <option value="2">2</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="row" class="form-label">Row:</label>
                        <select id="row" name="row" class="form-select">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printLabel()">Print</button>
            </div>
        </div>
    </div>
</div>
<script>
function openPrintDialog() {
    var printDialog = new bootstrap.Modal(document.getElementById('printDialog'));
    printDialog.show();
}

function printLabel() {
    var column = document.getElementById('column').value;
    var row = document.getElementById('row').value;
    var printContents = document.getElementById('printArea').innerHTML;
    var newWindow = window.open('', '_blank');
    newWindow.document.write('<html><head><title>Print Label</title>');
    newWindow.document.write('<style>');
    newWindow.document.write('@page { size: A4; margin: 0; }');
    newWindow.document.write('body { margin: 0; padding: 0; }');
    newWindow.document.write('.print-content { width: 10cm; height: 3.8cm; font-size: 11px; padding: 10px 0.5cm; box-sizing: border-box; position: absolute; }');
    newWindow.document.write('</style>');
    newWindow.document.write('</head><body>');
    
    var top = (row - 1) * (3.8 + 0.2) + 'cm';
    var left = (column - 1) * (10 + 0.3) + 'cm';
    newWindow.document.write('<div class="print-content" style="top:' + top + '; left:' + left + ';">');
    newWindow.document.write(printContents);
    newWindow.document.write('</div>');
    
    newWindow.document.write('</body></html>');
    newWindow.document.close();
    newWindow.print();
    newWindow.close();
    
    var printDialog = bootstrap.Modal.getInstance(document.getElementById('printDialog'));
    printDialog.hide();
}
</script>

{{Form::close()}}
