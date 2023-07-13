@extends('layouts.admin')
@section('page-title')
    {{__('Create '. $task->name)}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.index',$project->id)}}">{{__('Task')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.identifiedmisstatements',[$project->id, \Crypt::encrypt($task->id)])}}">{{ucwords($task->name)}}</a></li>
    <li class="breadcrumb-item">{{__('Create '. $task->name)}}</li>
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
            console.log(url);
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'mappingaccountdata_id': iteams_id
                },
                cache: false,
                success: function (data) {
                    var item = JSON.parse(data);
                    console.log(item)
                    $(el.parent().parent().find('.account_code')).val(item.mappingaccountdata.code);
                    $(el.parent().parent().find('.account_group')).val(item.mappingaccountdata.materialitas.code);
                    $(el.parent().parent().find('.materialitas')).val(item.mappingaccountdata.materialitas.name);
                    $(el.parent().parent().find('.name')).val(item.mappingaccountdata.name);
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
        {{ Form::open(['route' => ['save-summary-identified-misstatements',[$project->id, \Crypt::encrypt($task->id)]], 'method' => 'post']) }}
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <input type="hidden" name="code" class = "form-control code">
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
                                <th style="text-align: center;">{{__('Description of misstatements')}}</th>
                                <th style="text-align: center;">{{__('Period')}}</th>
                                <th style="text-align: center;">{{__('Type of misstatement')}}</th>
                                <th style="text-align: center;">{{__('Corrected / Uncorrected')}}</th>
                                <th style="text-align: center;">{{__('Assets')}}</th>
                                <th style="text-align: center;">{{__('Liability')}}</th>
                                <th style="text-align: center;">{{__('Equity')}}</th>
                                <th style="text-align: center;">{{__('Income')}}</th>
                                <th style="text-align: center;">{{__('RE')}}</th>
                                <th style="text-align: center;">{{__('Cause of misstatement and related control deficiency')}}</th>
                                <th style="text-align: center;">{{__('Managements reasons for not correcting')}}</th>
                            </tr>
                            </thead>

                            <tbody class="ui-sortable" data-repeater-item>
                            <tr>

                                <td class="form-group">
                                        {{ Form::textarea('description','', array('class' => 'form-control description','style' => 'width: 500px; height: 30px;','placeholder'=>__('Description'))) }}
                                </td>
                                <td class="form-group">
                                        <select class="form-control" style="width: 250px;" name="period" id="period" required>
                                            <option value="0">{{ 'Select Period' }}</option>
                                            @foreach(\App\Models\ProjectTask::$period as $key => $val)
                                                <option value="{{ $key }}">{{ __($val) }}</option>
                                            @endforeach
                                        </select>
                                </td>
                                 <td class="form-group">
                                        <select class="form-control" style="width: 250px;" name="type_misstatement" id="type_misstatement" required>
                                            <option value="0">{{ 'Select Type' }}</option>
                                            @foreach(\App\Models\ProjectTask::$type as $key => $val)
                                                <option value="{{ $key }}">{{ __($val) }}</option>
                                            @endforeach
                                        </select>
                                </td>
                                 <td class="form-group">
                                        <select class="form-control" style="width: 250px;" name="corrected" id="corrected" required>
                                            <option value="0">{{ 'Select Corrected / Uncorrected' }}</option>
                                            @foreach(\App\Models\ProjectTask::$corrected as $key => $val)
                                                <option value="{{ $key }}">{{ __($val) }}</option>
                                            @endforeach
                                        </select>
                                </td>
                                <td class="form-group">
                                        {{ Form::text('assets','', array('class' => 'form-control assets','style' => 'width: 250px;','placeholder'=>__('Assets'))) }}
                                </td>
                                <td class="form-group">
                                        {{ Form::text('liability','', array('class' => 'form-control liability','style' => 'width: 250px;','placeholder'=>__('Liability'))) }}
                                </td>
                                <td class="form-group">
                                        {{ Form::text('equity','', array('class' => 'form-control equity','style' => 'width: 250px;','placeholder'=>__('Equity'))) }}
                                </td>
                                <td class="form-group">
                                        {{ Form::text('income','', array('class' => 'form-control income','style' => 'width: 250px;','placeholder'=>__('Income'))) }}
                                </td>
                                <td class="form-group">
                                        {{ Form::text('re','', array('class' => 'form-control re','style' => 'width: 250px;','placeholder'=>__('RE'))) }}
                                </td>
                                <td class="form-group">
                                        {{ Form::textarea('cause_of_misstatement','', array('class' => 'form-control cause_of_misstatement','style' => 'width: 500px; height: 30px;','placeholder'=>__('Cause Of Misstatement and Related Control Deficiency'))) }}
                                </td>
                                <td class="form-group">
                                        {{ Form::textarea('managements_reason','', array('class' => 'form-control managements_reason','style' => 'width: 500px; height: 30px;','placeholder'=>__('Management Reason'))) }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
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