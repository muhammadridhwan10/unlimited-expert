@extends('layouts.admin')
@section('page-title')
    {{__('Mapping Account Create')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.index',$project->id)}}">{{__('Task')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.financial.statement',[$project->id, \Crypt::encrypt($task->id)])}}">{{ucwords($task->name)}}</a></li>
    <li class="breadcrumb-item">{{__('Mapping Account Create')}}</li>
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

        document.addEventListener('DOMContentLoaded', function() {
        var dueSelect = document.getElementById('due');
        var accountGroupSelect = document.querySelector('select[name="account_group"]');

        // Tambahkan event listener untuk pemilihan pada elemen accountGroupSelect
        accountGroupSelect.addEventListener('change', function() {
            var selectedValue = accountGroupSelect.value;

            if (selectedValue === 'ASET' || selectedValue === 'LIABILITAS') {
                // Jika memilih 'ASET' atau 'LIABILITAS', aktifkan kembali elemen dueSelect
                dueSelect.disabled = false;
                dueSelect.removeAttribute('disabled');
            } else {
                // Jika memilih selain 'ASET' atau 'LIABILITAS', nonaktifkan elemen dueSelect
                dueSelect.disabled = true;
                dueSelect.setAttribute('disabled', 'disabled');
            }
        });
    });
    </script>


@endpush
@section('content')
    <div class="row">
        {{ Form::open(['route' => ['save-mappingaccount',[$project->id, \Crypt::encrypt($task->id)]], 'method' => 'post']) }}
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
                                <th>{{__('Kode')}}</th>
                                <th>{{__('Nama Mapping Account')}}</th>
                                <th>{{__('Group Akun')}}</th>
                            </tr>
                            </thead>

                            <tbody class="ui-sortable" data-repeater-item>
                            <tr>

                                <td width="15%">
                                    <div class="form-group input-group search-form">
                                        {{ Form::text('account_code','', array('class' => 'form-control account_code','required'=>'required','placeholder'=>__('Kode Akun'), 'required'=>'required')) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group input-group search-form">
                                        {{ Form::text('name','', array('class' => 'form-control name','required'=>'required','placeholder'=>__('Nama Mapping Account'),'required'=>'required')) }}
                                    </div>
                                </td>
                                <td width="20%">
                                    <div class="form-group input-group search-form">
                                        {{ Form::select('account_group',$materialitas,null, array('class' => 'form-control account_group','required'=>'required','required'=>'required')) }}
                                    </div>
                                </td>
                                <td>
                                    <a href="#" class="ti ti-trash text-white repeater-action-btn bg-danger ms-2 bs-pass-para" data-repeater-delete></a>
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


