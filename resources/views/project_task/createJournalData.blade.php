@extends('layouts.admin')
@section('page-title')
    {{__('Journal Data Create')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.index',$project->id)}}">{{__('Task')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.journal.entries',[$project->id, \Crypt::encrypt($task->id)])}}">{{ucwords($task->name)}}</a></li>
    <li class="breadcrumb-item">{{__('Journal Data Create')}}</li>
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
            }

        }

        $(document).on('change', '.item', function () {

            var iteams_id = $(this).val();
            var url = $(this).data('url');
            var el = $(this);
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'id': iteams_id
                },
                cache: false,
                success: function (data) {
                    var item = JSON.parse(data);
                    // console.log(item)
                    $(el.parent().parent().find('.inhouse')).val(item.journaldata.inhouse);
                    $(el.parent().parent().find('.account')).val(item.journaldata.account);
                    $(el.parent().parent().find('.amount')).html(item.journaldata.audited);


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

                },
            });
        });

        $(document).on('keyup', '.quantity', function () {
            var quntityTotalTaxPrice = 0;

            var el = $(this).parent().parent().parent().parent();
            var quantity = parseInt($(this).val().replace(/,/g, ''));
            var price = parseInt($(el.find('.price')).val().replace(/,/g, ''));
            var inhouse2022 = parseInt($(el.find('.inhouse')).val().replace(/,/g, ''));

            var totalItemPrice = (inhouse2022 + quantity - price);
            var amount = (totalItemPrice);
            $(el.find('.amount')).val(amount);


            var inputs = $(".amount");
            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

        })

        $(document).on('keyup', '.price', function () {
            var el = $(this).parent().parent().parent().parent();
            var price = parseInt($(this).val().replace(/,/g, ''));
            var quantity = parseInt($(el.find('.quantity')).val().replace(/,/g, ''));
            var inhouse2022 = parseInt($(el.find('.inhouse')).val().replace(/,/g, ''));
            var totalItemPrice = (inhouse2022 + quantity - price);

            var amount = (totalItemPrice);
            console.log(amount);
            $(el.find('.amount')).val(amount);


            var inputs = $(".amount");
            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

        })

    </script>
@endpush
@section('content')
    <div class="row">
        {{ Form::open(['route' => ['save-journal-data',[$project->id, \Crypt::encrypt($task->id)]], 'method' => 'post']) }}
        <div class="col-12">
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
                                <th>{{__('CoA')}}</th>
                                <th>{{__('Account')}}</th>
                                <th>{{__('Adj Dr')}}</th>
                                <th>{{__('Adj Cr')}} </th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>

                            <tbody class="ui-sortable" data-repeater-item>
                            <tr>

                                <td width="15%" class="form-group pt-0">
                                    {{ Form::select('item', $financial_statement,'', array('class' => 'form-control select2 item','data-url'=>route('journal.data'),'required'=>'required')) }}
                                </td>
                                <td width="50%">
                                    <div class="form-group input-group search-form">
                                        {{ Form::text('account','', array('class' => 'form-control account','required'=>'required','placeholder'=>__('Account Name'),'readonly' => true, 'required'=>'required')) }}
                                    </div>
                                </td>
                                <td width="18%">
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('dr','', array('class' => 'form-control quantity','required'=>'required','placeholder'=>__('Adj Dr.'),'required'=>'required')) }}
                                        
                                    </div>
                                </td>

                                <td width="20%">
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('cr','', array('class' => 'form-control price','required'=>'required','placeholder'=>__('Adj Cr.'),'required'=>'required')) }}
                                        
                                    </div>
                                </td>

                                <td>
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::hidden('inhouse','', array('class' => 'form-control inhouse','required'=>'required','placeholder'=>__('Inhouse 2022'),'readonly' => true, 'required'=>'required')) }}
                                    </div>
                                </td>

                                <td>
                                    <div class="form-group">
                                        {{ Form::hidden('audited','', array('class' => 'form-control amount','required'=>'required','placeholder'=>__('Audited 2022'),'required'=>'required')) }}
                                        
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-sm-12 col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('notes', __('Notes'),['class'=>'form-label']) }}
                                        {{ Form::textarea('notes',null, array('class' => 'form-control select','required'=>'required')) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" onclick="location.href = '{{route("tasktemplate.index")}}';" class="btn btn-light">
            <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
        </div>
        {{ Form::close() }}

    </div>
@endsection


