@extends('layouts.admin')
@section('page-title')
    {{__('Invoice Edit')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('invoice.index')}}">{{__('Invoice')}}</a></li>
    <li class="breadcrumb-item">{{__('Invoice Edit')}}</li>
@endsection
@push('script-page')
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateAmounts() {
                let subTotal = 0;
                let totalTax = 0;
                let rows = document.querySelectorAll('#sortable-table tbody tr');
                let operator = document.querySelector('#operator').value;

                rows.forEach(row => {
                    let priceInput = row.querySelector('.price');
                    let taxInput = row.querySelector('.tax');
                    let amountCell = row.querySelector('.amount');

                    if (priceInput && taxInput && amountCell) {
                        let price = parseFloat(priceInput.value) || 0;
                        let tax = parseFloat(taxInput.value) || 0;

                        let amount = price;
                        let taxAmount = price * (tax / 100);

                        subTotal += amount;
                        totalTax += taxAmount;

                        amountCell.textContent = amount.toFixed(2);
                    }
                });

                let totalAmount = operator === '+' 
                ? subTotal + totalTax 
                : subTotal - totalTax;

                document.querySelector('.subTotal').textContent = subTotal.toFixed(2);
                document.querySelector('.totalTax').textContent = totalTax.toFixed(2);
                document.querySelector('.totalAmount').textContent = totalAmount.toFixed(2);
            }

            document.querySelectorAll('.price, .tax').forEach(input => {
                input.addEventListener('input', updateAmounts);
            });

            document.querySelector('#operator').addEventListener('change', updateAmounts);

            document.querySelectorAll('.delete_item').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    let confirmMessage = this.getAttribute('data-confirm');
                    let confirmed = confirm(confirmMessage);
                    if (confirmed) {
                        this.closest('tr').remove();
                        updateAmounts();
                    }
                });
            });

            updateAmounts();
        });
    </script>


@endpush
@section('content')
    <div class="row">
         {{ Form::model($invoice, array('route' => array('invoice.update', $invoice->id), 'method' => 'PUT','class'=>'w-100')) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="form-group" id="customer-box">
                                {{ Form::label('client_id', __('Client'),['class'=>'form-label']) }}<span class="text-danger">*</span>  <a style="font-size:10px" href="#" data-size="lg" data-url="{{ route('clients.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="" data-bs-original-title="{{__('Create Client')}}">{{__('Create Client')}}</a>
                                {{ Form::select('client_id', $customers,null, array('class' => 'form-control select2','id'=>'customer','data-url'=>route('invoice.customer'),'required'=>'required')) }}

                            </div>

                            <div id="customer_detail" class="d-none">
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('issue_date', __('Issue Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                        <div class="form-icon-user">
                                            {{Form::date('issue_date',null,array('class'=>'form-control','required'=>'required'))}}

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('due_date', __('Due Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                        <div class="form-icon-user">
                                            {{Form::date('due_date',null,array('class'=>'form-control','required'=>'required'))}}

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('invoice_number', __('Invoice Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                        <div class="form-icon-user">
                                            {{Form::text('invoice_id',null,array('class'=>'form-control','required'=>'required'))}}

                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('category_invoice', __('Category Invoice'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                        {{ Form::select('category_invoice', \App\Models\Invoice::$categoryInvoice, null, ['class' => 'form-control main-element', 'required' => 'required']) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('category_id', __('Category'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                        <select class="form-control select2" id="category_id" name="category_id[]" multiple="multiple" required>
                                            @foreach($category as $categoryId => $categoryName)
                                                <option value="{{ $categoryId }}" @if(in_array($categoryId, explode(',', $invoice->category_id))) selected @endif>{{ $categoryName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('account_id', __('CoA'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                        {{ Form::select('account_id', $account,null, array('class' => 'form-control select','required'=>'required')) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('user_id', __('Partners'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                                        {{ Form::select('user_id', $partners,null, array('class' => 'form-control select','required'=>'required')) }}
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('Company', __('company'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                        {{ Form::select('company', \App\Models\Invoice::$company, null, ['class' => 'form-control main-element', 'required' => 'required']) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('ref_number', __('Ref Number'),['class'=>'form-label']) }}
                                        <div class="form-icon-user">
                                            <span><i class="ti ti-joint"></i></span>
                                            {{ Form::text('ref_number', '', array('class' => 'form-control')) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('invoice_template', __('Invoice Template'),['class'=>'form-label']) }}
                                        <select class="form-control select2" name="invoice_template">
                                            @foreach(Utility::templateData()['templates'] as $key => $template)
                                                <option value="{{$key}}" {{(isset($settings['invoice_template']) && $settings['invoice_template'] == $key) ? 'selected' : ''}}>{{$template}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('currency', __('Currency'),['class'=>'form-label']) }}
                                        <select class="form-control select2 currency" name="currency" id="currency">
                                            <option value="{{ $siteCurrencySymbol }}">{{ $siteCurrencySymbol }}</option>
                                            <option value="{{ $siteCurrencySymbol2 }}">{{ $siteCurrencySymbol2 }}</option>
                                            <option value="{{ $siteCurrencySymbol3 }}">{{ $siteCurrencySymbol3 }}</option>
                                            <!-- Tambahkan opsi lain jika diperlukan -->
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('operator', __('Operator'), ['class' => 'form-label']) }}
                                        <select class="form-control operator" name="operator" id="operator">
                                            <option value="+" {{ old('operator', $invoice->operator ?? '-') == '+' ? 'selected' : '' }}>+</option>
                                            <option value="-" {{ old('operator', $invoice->operator ?? '-') == '-' ? 'selected' : '' }}>-</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- <div class="col-md-6">
                                    <div class="form-check custom-checkbox mt-4">
                                        <input class="form-check-input" type="checkbox" name="discount_apply" id="discount_apply">
                                        <label class="form-check-label " for="discount_apply">{{__('Discount Apply')}}</label>
                                    </div>
                                </div> --}}

                                @if(!$customFields->isEmpty())
                                    <div class="col-md-6">
                                        <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                                            @include('customFields.formBuilder')
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <h5 class=" d-inline-block mb-4">{{__('Project')}}</h5>
            <div class="card" data-value='{!! json_encode($invoice->items) !!}'>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 table-custom-style" id="sortable-table">
                            <thead>
                                <tr>
                                    <th>{{__('Items')}}</th>
                                    <th>{{__('Price')}}</th>
                                    <th>{{__('Tax')}}</th>
                                    <th></th>
                                    <th></th>
                                    <th class="text-end">{{__('Amount')}}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                    <tr>
                                        {{ Form::hidden('id[]', $item->id, array('class' => 'form-control id')) }}
                                        <td width="40%">
                                            {{ Form::select('item[]', $projects, $item->product_id, array('class' => 'form-control item select')) }}
                                        </td>
                                        <td width="35%">
                                            <div class="price-input input-group search-form">
                                                {{ Form::text('price[]', $item->price, array('class' => 'form-control price','required'=>'required','placeholder'=>__('Price'))) }}
                                                <span class="input-group-text bg-transparent currency-symbol">{{\Auth::user()->currencySymbol()}}</span>
                                            </div>
                                        </td>
                                        <td width="30%">
                                            <div class="input-group colorpickerinput">
                                                {{ Form::text('tax[]', $item->tax, array('class' => 'form-control tax','required'=>'required','placeholder'=>__('Tax'))) }}
                                                {{ Form::hidden('itemTaxPrice[]', $item->tax, array('class' => 'form-control itemTaxPrice')) }}
                                            </div>
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-end amount">{{ $item->price }}</td>
                                        <td>
                                            <a href="#" class="ti ti-trash text-black text-danger delete_item" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-confirm="{{ __('Are You Sure?')}}"></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            {{ Form::textarea('description[]', $item->description, ['class'=>'form-control pro_description','rows'=>'2','placeholder'=>__('Description')]) }}
                                        </td>
                                        <td colspan="5"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{__('Sub Total')}} (<span class="currency-symbol">{{\Auth::user()->currencySymbol()}}</span>)</strong></td>
                                    <td class="text-end subTotal">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{__('Tax')}} (<span class="currency-symbol">{{\Auth::user()->currencySymbol()}}</span>)</strong></td>
                                    <td class="text-end totalTax">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td><strong>{{__('Total Amount')}} (<span class="currency-symbol">{{\Auth::user()->currencySymbol()}}</span>)</strong></td>
                                    <td class="text-end totalAmount blue-text">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" onclick="location.href = '{{route("invoice.index")}}';" class="btn btn-light">
             <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
        </div>
        {{ Form::close() }}

    </div>
@endsection


