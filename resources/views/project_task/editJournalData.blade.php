@extends('layouts.admin')
@section('page-title')
    {{__('Journal Data Edit')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.index',$project->id)}}">{{__('Task')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.journal.entries',[$project->id, \Crypt::encrypt($task->id)])}}">{{ucwords($task->name)}}</a></li>
    <li class="breadcrumb-item">{{__('Journal Data Edit')}}</li>
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
                    //console.log(item)
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

        function numberWithCommas(value) {
        // Menghilangkan semua karakter kecuali digit
        value = value.replace(/\D/g, '');

        // Memformat angka dengan pemisah ribuan
        value = Number(value).toLocaleString();

        return value;
    }



    </script>
@endpush
@section('content')
    {{-- <div class="row">
        {{ Form::model($summary_journaldata, array('route' => array('tasktemplate.update', $tasktemplate->id), 'method' => 'PUT','class'=>'w-100')) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    {{ Form::label('category_id', __('Category'),['class'=>'form-label']) }}
                                    {{ Form::select('category_id', $category,null, array('class' => 'form-control select','required'=>'required')) }}
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('category_template_id', __('Category Template'),['class'=>'form-label']) }}
                                        {{ Form::select('category_template_id', $category_template,null, array('class' => 'form-control select','required'=>'required')) }}
                                    </div>
                                </div>

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
            <h5 class=" d-inline-block mb-4">{{__('Task Details')}}</h5>
            <div class="card repeater">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                            <tr>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Estimated Hours')}} </th>
                                <!-- <th>{{__('Start Date')}}</th>
                                <th>{{__('End Date')}}</th> -->
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                            <tr>
                                {{ Form::hidden('id',null, array('class' => 'form-control id')) }}
                                <td>
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('name',null, array('class' => 'form-control','required'=>'required','required'=>'required')) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('estimated_hrs',null, array('class' => 'form-control','required'=>'required','required'=>'required')) }}
                                    </div>
                                </td>
                                <!-- <td>
                                    <div class="form-group price-input input-group search-form">
                                    {{Form::date('start_date',null,array('class'=>'form-control','required'=>'required'))}}
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group price-input input-group search-form">
                                    {{Form::date('end_date',null,array('class'=>'form-control','required'=>'required'))}}
                                    </div>
                                </td> -->
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
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" onclick="location.href = '{{route("tasktemplate.index")}}';" class="btn btn-light">
            <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
        </div>
        {{ Form::close() }}
    </div> --}}
    <div class="row">
    {{ Form::model($summary_journaldata, array('route' => array('journal.data.update', [$project->id, \Crypt::encrypt($task->id), $summary_journaldata->id]), 'method' => 'PUT','class'=>'w-100')) }}
        {{-- {{ Form::open(['route' => ['journal.data.update',[$project->id, \Crypt::encrypt($task->id), $summary_journaldata->id]], 'method' => 'PUT']) }} --}}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="row">
                                <div class="col-sm-6 col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('adj_code', __('AJE/RJE Code'),['class'=>'form-label']) }}
                                        <select name="adj_code" id="adj_code" class="form-control main-element">
                                            @foreach(\App\Models\ProjectTask::$category as $k => $v)
                                                <option value="{{$k}}">{{__($k)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card repeater">
                <div class="card-header">
                    <h6 class="mb-0" id="balance-heading">
                        <span id="balance-text"></span>
                        <i id="balance-icon"></i>
                    </h6>
                </div>
                {{-- <div class="item-section py-2">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                            <div class="all-button-box me-2">
                                <a href="#" data-repeater-create="" class="btn btn-primary" data-bs-toggle="modal" data-target="#add-bank">
                                    <i class="ti ti-plus"></i> {{__('Add item')}}
                                </a>
                            </div>
                        </div>
                    </div>
                </div> --}}
                <div class="card-body table-border-style mt-2">
                    <div class="table-responsive">
                        <table class="table  mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                            <tr>
                                <th>{{__('Account Name')}}</th>
                                <th>{{__('Dr.')}}</th>
                                <th>{{__('Cr.')}} </th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>

                            <tbody class="ui-sortable" data-repeater-item>
                            <tr>
                                <td width="50%">
                                    <div class="form-group">
                                        {{ Form::select('coa', $financial_statement, null, array('class' => 'form-control select2 item', 'required' => 'required')) }}
                                        {{-- {{ Form::select('item', $financial_statement, '', ['class' => 'form-control select2 item','data-url' => route('journal.data'), 'required' => 'required']) }} --}}
                                    </div>
                                </td>
                               <td width="25%">
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('dr', null, array('class' => 'form-control quantity dr-input', 'style' => 'text-align: right;',  'required' => 'required', 'placeholder' => __('Dr.'), 'required' => 'required', 'oninput' => "this.value = numberWithCommas(this.value);")) }}
                                    </div>
                                </td>
                                <td width="25%">
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('cr', null, array('class' => 'form-control price cr-input', 'style' => 'text-align: right;', 'required' => 'required', 'placeholder' => __('Cr.'), 'required' => 'required', 'oninput' => "this.value = numberWithCommas(this.value);")) }}
                                    </div>
                                </td>
                                 {{-- <td>
                                    <a href="#" class="ti ti-trash text-white repeater-action-btn bg-danger ms-2 bs-pass-para" data-repeater-delete></a>
                                </td> --}}


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
            <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
        </div>
        {{ Form::close() }}

    </div>
@endsection


