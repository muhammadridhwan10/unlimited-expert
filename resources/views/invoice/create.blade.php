@extends('layouts.admin')
@section('page-title')
    {{__('Invoice Create')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('invoice.index')}}">{{__('Invoice')}}</a></li>
    <li class="breadcrumb-item">{{__('Invoice Create')}}</li>
@endsection
@push('css-page')
<link rel="stylesheet" href="{{url('css/select2.min.css')}}">
@endpush
@push('script-page')
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script>
        $(document).ready(function() {
            console.log("Select2 initialized.");
            $('.item').select2({
                allowClear: true
            });
        });
        var selector = "body";
        if ($(selector + " .repeater").length) {
            var $dragAndDrop = $("body .repeater tbody").sortable({
                handle: '.sort-handler'
            });
            var $repeater = $(selector + ' .repeater').repeater({
                initEmpty: false,
                defaultValues: {
                    'status': 1
                },
                show: function () {
                    $(this).slideDown();
                    var file_uploads = $(this).find('input.multi');
                    if (file_uploads.length) {
                        $(this).find('input.multi').MultiFile({
                            max: 3,
                            accept: 'png|jpg|jpeg',
                            max_size: 2048
                        });
                    }
                    if($('.select2').length) {
                        $('.item').select2();
                    }

                },
                hide: function (deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                        $(this).remove();

                        var inputs = $(".amount");
                        var subTotal = 0;
                        for (var i = 0; i < inputs.length; i++) {
                            subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                        }
                        $('.subTotal').html(subTotal.toFixed(0));
                        $('.totalAmount').html(subTotal.toFixed(0));
                    }
                },
                ready: function (setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: true
            });
            var value = $(selector + " .repeater").attr('data-value');
            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
            }

        }

        $(document).on('change', '#customer', function () {
            $('#customer_detail').removeClass('d-none');
            $('#customer_detail').addClass('d-block');
            $('#customer-box').removeClass('d-block');
            $('#customer-box').addClass('d-none');
            var id = $(this).val();
            var url = $(this).data('url');
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'id': id
                },
                cache: false,
                success: function (data) {
                    if (data != '') {
                        $('#customer_detail').html(data);
                    } else {
                        $('#customer-box').removeClass('d-none');
                        $('#customer-box').addClass('d-block');
                        $('#customer_detail').removeClass('d-block');
                        $('#customer_detail').addClass('d-none');
                    }

                },

            });
        });

        $(document).on('click', '#remove', function () {
            $('#customer-box').removeClass('d-none');
            $('#customer-box').addClass('d-block');
            $('#customer_detail').removeClass('d-block');
            $('#customer_detail').addClass('d-none');
        })

        $(document).on('keyup', '.price', function () {
            var el = $(this).parent().parent().parent().parent();
            var price = $(this).val();
            var tax = $(el.find('.tax')).val();
            var totalItemPrice = (1 * price);

            var amount = (Math.round(totalItemPrice));
            $(el.find('.amount')).html(amount);


            var itemTaxPrice = parseFloat((tax / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(0));


            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var inputs = $(".amount");
            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }
            $('.totalTax').html(totalItemTaxPrice.toFixed(0));
            $('.subTotal').html(subTotal.toFixed(0));
            $('.totalAmount').html((parseFloat(subTotal) - parseFloat(totalItemTaxPrice)).toFixed(0));

        })

        $(document).on('keyup', '.tax', function () {
            var el = $(this).parent().parent().parent().parent();
            var tax = $(this).val();
            var price = $(el.find('.price')).val();

            var totalItemPrice = (1 * price);

            var amount = (Math.round(totalItemPrice));
            $(el.find('.amount')).html(amount);

            console.log(amount);

            var itemTaxPrice = parseFloat((tax / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(0));


            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }

            var inputs = $(".amount");
            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }
            $('.subTotal').html(subTotal.toFixed(0));
            $('.totalTax').html(totalItemTaxPrice.toFixed(0));

            $('.totalAmount').html((parseFloat(subTotal) - parseFloat(totalItemTaxPrice)).toFixed(0));
        })


        var customerId = '{{$customerId}}';
        if (customerId > 0) {
            $('#customer').val(customerId).change();
        }
    </script>
    <script>
        $(document).ready(function () {
            // Menangani perubahan pada dropdown currency
            $(document).on('change', '#currency', function () {
                var selectedCurrency = $(this).val();
                updateCurrencySymbol(selectedCurrency);
            });

            // Fungsi untuk memperbarui simbol mata uang pada elemen harga
            function updateCurrencySymbol(selectedCurrency) {
                var currencySymbolElement = $('.currency-symbol');
                if (selectedCurrency === 'Rp') {
                    currencySymbolElement.text("{{ \Auth::user()->currencySymbol() }}");
                } else if (selectedCurrency === '$') {
                    currencySymbolElement.text("{{ \Auth::user()->currencySymbol2() }}");
                }
                else if (selectedCurrency === '€') {
                    currencySymbolElement.text("{{ \Auth::user()->currencySymbol3() }}");
                }
                // Tambahkan logika untuk currency lain jika diperlukan
            }

            // Panggil fungsi updateCurrencySymbol saat halaman dimuat
            var initialCurrency = $('#currency').val();
            updateCurrencySymbol(initialCurrency);
        });
    </script>
@endpush
@section('content')
    <div class="row">
        {{ Form::open(array('url' => 'invoice','class'=>'w-100')) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="form-group" id="customer-box">
                                {{ Form::label('client_id', __('Client'),['class'=>'form-label']) }}<span class="text-danger">*</span>  <a style="font-size:10px" href="#" data-size="lg" data-url="{{ route('clients.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="" data-bs-original-title="{{__('Create Client')}}">{{__('Create Client')}}</a>
                                {{ Form::select('client_id', $customers,$customerId, array('class' => 'form-control select2','id'=>'customer','data-url'=>route('invoice.customer'),'required'=>'required')) }}

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
                                            <input name="invoice_id" type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('category_invoice', __('Category Invoice'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                                        <select name="category_invoice" id="category_invoice" class="form-control main-element" required>
                                            <option value="0">{{__('Select Category Invoice')}}</option>
                                            @foreach(\App\Models\Invoice::$categoryInvoice as $k => $v)
                                                <option value="{{$k}}">{{__($k)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('category_id', __('Category'), ['class' => 'form-label']) }}
                                        <select class="form-control select2" id="category_id" name="category_id[]" multiple="multiple" required>
                                            @foreach($category as $categoryId => $categoryName)
                                                <option value="{{ $categoryId }}">{{ $categoryName }}</option>
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
                                        <select name="company" id="company" class="form-control main-element" required>
                                            <option value="0">{{__('Select Company')}}</option>
                                            @foreach(\App\Models\Invoice::$company as $k => $v)
                                                <option value="{{$k}}">{{__($k)}}</option>
                                            @endforeach
                                        </select>
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
                                        <select class="form-control" name="invoice_template">
                                            @foreach(Utility::templateData()['templates'] as $key => $template)
                                                <option value="{{$key}}" {{(isset($settings['invoice_template']) && $settings['invoice_template'] == $key) ? 'selected' : ''}}>{{$template}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('currency', __('Currency'),['class'=>'form-label']) }}
                                        <select class="form-control currency" name="currency" id="currency">
                                            <option value="{{ $siteCurrencySymbol }}">{{ $siteCurrencySymbol }}</option>
                                            <option value="{{ $siteCurrencySymbol2 }}">{{ $siteCurrencySymbol2 }}</option>
                                            <option value="{{ $siteCurrencySymbol3 }}">{{ $siteCurrencySymbol3 }}</option>
                                            <!-- Tambahkan opsi lain jika diperlukan -->
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
            <div class="card repeater">
                <div class="item-section py-2">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                            <div class="all-button-box me-2">
                                <a href="#" data-repeater-create="" class="btn btn-primary" data-bs-toggle="modal" data-target="#add-bank">
                                    <i class="ti ti-plus"></i> {{__('Add item')}}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style mt-2">
                    <div class="table-responsive">
                        <table class="table  mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                            <tr>
                                <th>{{__('Items')}}</th>
                                <th>{{__('Price')}} </th>
                                <th>{{__('Tax')}} (%)</th>
                                <th></th>
                                <th></th>
                                <th class="text-end">{{__('Amount')}} <br><small class="text-danger font-weight-bold">{{__('before tax & discount')}}</small></th>
                                <th></th>
                            </tr>
                            </thead>

                            <tbody class="ui-sortable" data-repeater-item>
                            <tr>

                                <td>
                                    {{ Form::select('item', $projects,'', array('class' => 'select2 item custom-select-style','style'=>'max-width:250px','required'=>'required')) }}
                                </td>


                                <td>
                                    <div class="price-input input-group search-form" style="width:200px">
                                        {{ Form::text('price','', array('class' => 'form-control price','required'=>'required','placeholder'=>__('Price'),'required'=>'required')) }}
                                        <span class="input-group-text bg-transparent currency-symbol">{{\Auth::user()->currencySymbol()}}</span>
                                    </div>
                                </td>



                                <td>
                                        <div class="input-group colorpickerinput" style="width:100px">
                                            {{ Form::text('tax','', array('class' => 'form-control tax','required'=>'required','placeholder'=>__('Tax'))) }}
                                            {{ Form::hidden('itemTaxPrice','', array('class' => 'form-control itemTaxPrice')) }}
                                            <span class="input-group-text bg-transparent">{{'%'}}</span>
                                        </div>
                                </td>
                                <td></td>
                                <td></td>
                                <td class="text-end amount">0.00</td>
                                <td>
                                    <a href="#" class="ti ti-trash text-white repeater-action-btn bg-danger ms-2 bs-pass-para" data-repeater-delete></a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="form-group">
                                        {{ Form::textarea('description', null, ['class'=>'form-control pro_description','rows'=>'2','placeholder'=>__('Description')]) }}
                                    </div>
                                </td>
                                <td colspan="5"></td>
                            </tr>
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
                            {{-- <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{__('Discount')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                <td class="text-end totalDiscount">0.00</td>
                                <td></td>
                            </tr> --}}
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
                                <td class="text-end totalAmount blue-text"></td>
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
            <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
        </div>
        {{ Form::close() }}

    </div>
@endsection


