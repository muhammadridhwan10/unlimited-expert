@extends('layouts.admin')
@section('page-title')
    {{__('Edit '. $task->name)}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.index',$project->id)}}">{{__('Task')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.identifiedmisstatements',[$project->id, \Crypt::encrypt($task->id)])}}">{{ucwords($task->name)}}</a></li>
    <li class="breadcrumb-item">{{__('Edit '. $task->name)}}</li>
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
        {{ Form::model($identifiedmisstatements, array('route' => array('summary.identified.misstatements.update', [$project->id, \Crypt::encrypt($task->id), $identifiedmisstatements->id]), 'method' => 'PUT','class'=>'w-100')) }}
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <input type="hidden" name="code" class = "form-control code">
        <div class="col-12">
            <div class="card repeater">
                <div class="card-body table-border-style mt-2">
                    <div class="table-responsive">
                        <table class="table  mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                            <tr>
                                <th style="text-align: center;">{{__('Description of misstatements')}}</th>
                                {{-- <th style="text-align: center;">{{__('Period')}}</th>
                                <th style="text-align: center;">{{__('Type of misstatement')}}</th>
                                <th style="text-align: center;">{{__('Corrected / Uncorrected')}}</th> --}}
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
                                        {{ Form::textarea('description',null, array('class' => 'form-control description','style' => 'width: 500px; height: 30px;','placeholder'=>__('Description'))) }}
                                </td>
                                {{-- <td class="form-group">
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
                                </td> --}}
                                <td class="form-group">
                                    {{ Form::text('assets',(!empty(number_format($identifiedmisstatements->assets))) ? (number_format($identifiedmisstatements->assets) < 0 ? '('.number_format(abs($identifiedmisstatements->assets)).')' : number_format($identifiedmisstatements->assets)) : 0,
                                        array('class' => 'form-control assets', 'style' => 'width: 250px;  text-align: right;', 'placeholder' => __('Assets'))
                                    )}}
                                </td>
                                <td class="form-group">
                                    {{ Form::text('liability',(!empty(number_format($identifiedmisstatements->liability))) ? (number_format($identifiedmisstatements->liability) < 0 ? '('.number_format(abs($identifiedmisstatements->liability)).')' : number_format($identifiedmisstatements->liability)) : 0,
                                        array('class' => 'form-control liability', 'style' => 'width: 250px;  text-align: right;', 'placeholder' => __('Liability'))
                                    )}}
                                </td>
                                <td class="form-group">
                                    {{ Form::text('equity',(!empty(number_format($identifiedmisstatements->equity))) ? (number_format($identifiedmisstatements->equity) < 0 ? '('.number_format(abs($identifiedmisstatements->equity)).')' : number_format($identifiedmisstatements->equity)) : 0,
                                        array('class' => 'form-control equity', 'style' => 'width: 250px;  text-align: right;', 'placeholder' => __('Equity'))
                                    )}}
                                </td>
                                <td class="form-group">
                                    {{ Form::text('income',(!empty(number_format($identifiedmisstatements->income))) ? (number_format($identifiedmisstatements->income) < 0 ? '('.number_format(abs($identifiedmisstatements->income)).')' : number_format($identifiedmisstatements->income)) : 0,
                                        array('class' => 'form-control income', 'style' => 'width: 250px;  text-align: right;', 'placeholder' => __('Income'))
                                    )}}
                                </td>
                                <td class="form-group">
                                    {{ Form::text('re',(!empty(number_format($identifiedmisstatements->re))) ? (number_format($identifiedmisstatements->re) < 0 ? '('.number_format(abs($identifiedmisstatements->re)).')' : number_format($identifiedmisstatements->re)) : 0,
                                        array('class' => 'form-control re', 'style' => 'width: 250px;  text-align: right;', 'placeholder' => __('RE'))
                                    )}}
                                </td>
                                <td class="form-group">
                                        {{ Form::textarea('cause_of_misstatement',null, array('class' => 'form-control cause_of_misstatement','style' => 'width: 500px; height: 30px;','placeholder'=>__('Cause Of Misstatement and Related Control Deficiency'))) }}
                                </td>
                                <td class="form-group">
                                        {{ Form::textarea('managements_reason',null, array('class' => 'form-control managements_reason','style' => 'width: 500px; height: 30px;','placeholder'=>__('Management Reason'))) }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" onclick="location.href = '{{route('projects.tasks.identifiedmisstatements',[$project->id, \Crypt::encrypt($task->id)])}}';" class="btn btn-light">
            <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
        </div>
        {{ Form::close() }}

    </div>
@endsection