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
        var selector = "body";
        if ($(selector + " .repeater").length) {
            var $dragAndDrop = $("body .repeater tbody").sortable({
                handle: '.sort-handler'
            });
            var $repeater = $(selector + ' .repeater').repeater({
                initEmpty: true,
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
                        $('.select2').select2();
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
                for (var i = 0; i < value.length; i++) {
                    var tr = $('#sortable-table .id[value="' + value[i].id + '"]').parent();
                    tr.find('.item').val(value[i].product_id);
                    changeItem(tr.find('.item'));
                }
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

        $(document).on('change', '.item', function () {
            changeItem($(this));
        });

        var invoice_id = '{{$invoice->id}}';

        function changeItem(element) {


            var iteams_id = element.val();


            var url = element.data('url');
            var el = element;
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'product_id': iteams_id
                },
                cache: false,
                success: function (data) {
                    var item = JSON.parse(data);

                    $.ajax({
                        url: '{{route('invoice.items')}}',
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': jQuery('#token').val()
                        },
                        data: {
                            'invoice_id': invoice_id,
                            'product_id': iteams_id,
                        },
                        cache: false,
                        success: function (data) {
                            var invoiceItems = JSON.parse(data);


                            if (invoiceItems != null) {


                                var amount = (invoiceItems.price);

                                $(el.parent().parent().find('.price')).val(invoiceItems.price);
                                $('.pro_description').text(invoiceItems.description);

                            } else {
                                $(el.parent().parent().find('.price')).val(item.product.sale_price);
                                $(el.parent().parent().find('.pro_description')).val(item.product.sale_price);
                                $('.pro_description').text(item.product.sale_price);

                            }


                            $(el.parent().parent().find('.itemTaxPrice')).val(itemTaxPrice.toFixed(0));
                            $(el.parent().parent().find('.itemTaxRate')).val(totalItemTaxRate.toFixed(0));
                            $(el.parent().parent().find('.taxes')).html(taxes);
                            $(el.parent().parent().find('.tax')).val(tax);
                            $(el.parent().parent().find('.unit')).html(item.unit);


                            if (invoiceItems != null) {
                                $(el.parent().parent().find('.amount')).html(amount);
                            } else {
                                $(el.parent().parent().find('.amount')).html(item.totalAmount);
                            }

                            var inputs = $(".amount");
                            var subTotal = 0;
                            for (var i = 0; i < inputs.length; i++) {
                                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                            }
                            $('.subTotal').html(subTotal.toFixed(0));



                            var totalItemPrice = 0;
                            var priceInput = $('.price');
                            for (var j = 0; j < priceInput.length; j++) {
                                totalItemPrice += parseFloat(priceInput[j].value);
                            }

                            var totalItemTaxPrice = 0;
                            var itemTaxPriceInput = $('.itemTaxPrice');
                            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
                            }

                            $('.totalTax').html(totalItemTaxPrice.toFixed(0));
                            $('.totalAmount').html((parseFloat(subTotal) - parseFloat(totalItemDiscountPrice) - parseFloat(totalItemTaxPrice)).toFixed(2));
                            $('.totalDiscount').html(totalItemDiscountPrice.toFixed(0));

                        }
                    });


                },
            });
        }

        $(document).on('keyup', '.price', function () {
            var el = $(this).parent().parent().parent().parent();
            var price = $(this).val();
            var tax = $(el.find('.tax')).val();
            var totalItemPrice = (1 * price);

            var amount = (totalItemPrice);
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

            var itemTaxPrice = parseFloat((tax / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(0));


            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }

            var amount = (totalItemPrice);
            $(el.find('.amount')).html(amount);

            var inputs = $(".amount");
            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }
            $('.subTotal').html(subTotal.toFixed(0));
            $('.totalTax').html(totalItemTaxPrice.toFixed(0));

            $('.totalAmount').html((parseFloat(subTotal) - parseFloat(totalItemTaxPrice)).toFixed(0));
        })

         $(document).on('click', '[data-repeater-create]', function () {
            $('.item :selected').each(function () {
                var id = $(this).val();
                $(".item option[value=" + id + "]").prop("disabled", true);
            });
        })

        $('.delete_item').click(function () {
            if (confirm('Are you sure you want to delete this element?')) {
                var el = $(this).parent().parent();
                var id = $(el.find('.id')).val();

                $.ajax({
                    url: '{{route('invoice.product.destroy')}}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': jQuery('#token').val()
                    },
                    data: {
                        'id': id
                    },
                    cache: false,
                    success: function (data) {

                    },
                });

            }
        });


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
            <div class="card repeater" data-value='{!! json_encode($invoice->items) !!}'>
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
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                            <tr>
                                <th>{{__('Items')}}</th>
                                <th>{{__('Price')}} </th>
                                <th>{{__('Tax')}}</th>
                                <th></th>
                                <th></th>
                                <th class="text-end">{{__('Amount')}} </th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                            <tr>
                                {{ Form::hidden('id',null, array('class' => 'form-control id')) }}
                                <td width="40%">
                                    {{ Form::select('item', $projects,null, array('class' => 'form-control item select')) }}

                                </td>
                                <td width="35%">
                                    <div class="price-input input-group search-form">
                                        {{ Form::text('price',null, array('class' => 'form-control price','required'=>'required','placeholder'=>__('Price'),'required'=>'required')) }}
                                        <span class="input-group-text bg-transparent currency-symbol">{{\Auth::user()->currencySymbol()}}</span>
                                    </div>
                                </td>
                                <td width="30%">
                                        <div class="input-group colorpickerinput">
                                            {{ Form::text('tax','', array('class' => 'form-control tax','required'=>'required','placeholder'=>__('Tax'))) }}
                                            {{ Form::hidden('itemTaxPrice','', array('class' => 'form-control itemTaxPrice')) }}
                                        </div>
                                </td>
                                <td></td>
                                <td></td>
                                <td class="text-end amount">0.00</td>

                                <td>
                                    @can('delete invoice product')
                                        <a href="#" class="ti ti-trash text-white text-danger delete_item" data-repeater-delete></a>
                                    @endcan
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                        {{ Form::textarea('description', null, ['class'=>'form-control pro_description','rows'=>'2','placeholder'=>__('Description')]) }}
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


