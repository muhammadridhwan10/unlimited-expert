@extends('layouts.admin')

@section('page-title')
    {{__('Manage Medical Allowance')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Manage Medical Allowance')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="lg" data-url="{{ route('medical-allowance.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Medical Allowance')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{url('css/swiper.min.css')}}">

    <link rel="stylesheet" href="{{url('css/swiper.min.css')}}">


    <style>
        .product-thumbs .swiper-slide img {
        border:2px solid transparent;
        object-fit: cover;
        cursor: pointer;
        }
        .product-thumbs .swiper-slide-active img {
        border-color: #bc4f38;
        }

        .product-slider .swiper-button-next:after,
        .product-slider .swiper-button-prev:after {
            font-size: 20px;
            color: #000;
            font-weight: bold;
        }
        .modal-dialog.modal-md {
            background-color: #fff !important;
        }

        .no-image{
            min-height: 300px;
            align-items: center;
            display: flex;
            justify-content: center;
        }
    </style>
@endpush

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('medical-allowance.index'),'method'=>'get','id'=>'report_monthly_medical_allowance')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-auto">

                                <div class="row">
                                    <div class="col-auto">
                                        <div class="btn-box" style = "width:300px;">
                                            {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                            {{ Form::select('employee_id', $employees, isset($_GET['employee_id']) ? $_GET['employee_id'] : null, ['class' => 'form-control select2', 'placeholder' => __('Select employee')]) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="btn-box">
                                            {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                            {{Form::month('month',isset($_GET['month'])?$_GET['month']:null,array('class'=>'month-btn form-control'))}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_monthly_medical_allowance').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('medical-allowance.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-auto">
                        <div class="btn-box">
                            {{ Form::label('show_entries', __('Show Entries'), ['class' => 'form-label']) }}
                            {{ Form::select('show_entries', [10 => '10', 25 => '25', 50 => '50', 100 => '100'], request('show_entries', 10), ['class' => 'form-select', 'onchange' => 'this.form.submit()']) }}
                        </div>
                    </div>
                </div>
            </div>
            {{ Form::close() }}
            <div class="card-body table-border-style">
                    <div class="table-responsive">
                    <table class="table">
                            <thead>
                            <tr>
                                @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                    <th>{{__('Employee')}}</th>
                                @endif
                                <th>{{__('Client')}}</th>
                                <th>{{__('Approval By')}}</th>
                                <th>{{__('Reimbursement Type')}}</th>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th width="200px">{{__('Description')}}</th>
                                <th>{{__('Image')}}</th>
                                <th>{{__('Status')}}</th>
                                <th width="200px">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($employeeReimbursment as $reimbursment)
                                <tr>
                                    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                        <td>{{!empty($reimbursment->employee->name)?$reimbursment->employee->name:'-'}}</td>
                                    @endif
                                    <td>{{!empty($reimbursment->client->name) ? $reimbursment->client->name:'-'}}</td>
                                    <td>{{!empty($reimbursment->approvals->name)?$reimbursment->approvals->name:'-'}}</td>
                                     <td>{{!empty($reimbursment->reimbursment_type)?$reimbursment->reimbursment_type:'-'}}</td>
                                    <td>{{date("l, d-m-Y",strtotime($reimbursment->date))}}</td>
                                    <td>{{!empty(number_format($reimbursment->amount))?number_format($reimbursment->amount):'-'}}</td>
                                    <td>{{!empty($reimbursment->description)?$reimbursment->description:'-'}}</td>
                                    <td>
                                        <img alt="Image placeholder" src="{{ asset('assets/images/gallery.png')}}" class="avatar view-images rounded-circle avatar-sm" data-bs-toggle="tooltip" title="{{__('View Medical Allowance Images')}}" data-original-title="{{__('View Medical Allowance Images')}}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="{{$reimbursment->id}}" id="track-images-{{$reimbursment->id}}">
                                    </td>
                                    <td>

                                        @if($reimbursment->status=="Pending")
                                            <div class="status_badge badge bg-warning p-2 px-3 rounded">{{ $reimbursment->status }}</div>
                                        @elseif($reimbursment->status=="Paid")
                                            <div class="status_badge badge bg-success p-2 px-3 rounded">{{ $reimbursment->status }}</div>
                                        @else($reimbursment->status =="Reject")
                                            <div class="status_badge badge bg-danger p-2 px-3 rounded">{{ $reimbursment->status }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reimbursment->status == "Pending")
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="#" data-url="{{ URL::to('medical-allowance/'.\Crypt::encrypt($reimbursment->id).'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Medical Allowance')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $employeeReimbursment->appends(['month' => request('month'), 'employee_id' => request('employee_id'), 'show_entries' => request('show_entries')])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg ss_modale " role="document">
          <div class="modal-content image_sider_div">

          </div>
        </div>
    </div>


    @if(\Auth::user()->type == 'senior accounting')
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                <div class="card-header"><h6 class="mb-0">{{__('Request Approval Reimbursement')}}</h6></div>
                <div class="card-body table-border-style">
                        <div class="table-responsive">
                        <table class="table datatables">
                                <thead>
                                <tr>
                                    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                        <th>{{__('Employee')}}</th>
                                    @endif
                                    <th>{{__('Client')}}</th>
                                    <th>{{__('Approval By')}}</th>
                                    <th>{{__('Reimbursement Type')}}</th>
                                    <th>{{__('Date')}}</th>
                                    <th>{{__('Amount')}}</th>
                                    <th width="200px">{{__('Description')}}</th>
                                    <th>{{__('Image')}}</th>
                                    <th width="200px">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($approval as $approvals)
                                    <tr>
                                    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                        <td>{{!empty($approvals->employee->name)?$approvals->employee->name:'-'}}</td>
                                    @endif
                                    <td>{{!empty($approvals->client->name) ? $approvals->client->name:'-'}}</td>
                                    <td>{{!empty($approvals->approvals->name)?$approvals->approvals->name:'-'}}</td>
                                     <td>{{!empty($approvals->reimbursment_type)?$approvals->reimbursment_type:'-'}}</td>
                                    <td>{{date("l, d-m-Y",strtotime($approvals->date))}}</td>
                                     <td>{{!empty(number_format($approvals->amount))?number_format($approvals->amount):'-'}}</td>
                                    <td>{{!empty($approvals->description)?$approvals->description:'-'}}</td>
                                    <td>
                                        <img alt="Image placeholder" src="{{ asset('assets/images/gallery.png')}}" class="avatar view-images rounded-circle avatar-sm" data-bs-toggle="tooltip" title="{{__('View Screenshot images')}}" data-original-title="{{__('View Screenshot images')}}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="{{$approvals->id}}" id="track-images-{{$approvals->id}}">
                                    </td>
                                    <td>
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="#" data-url="{{ URL::to('medical-allowance/'.$approvals->id.'/action') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Overtime Action')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Overtime Action')}}" data-original-title="{{__('Overtime Action')}}">
                                                <i class="ti ti-caret-right text-white"></i> </a>
                                        </div>
                                    </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg ss_modale " role="document">
                <div class="modal-content image_sider_div">

                </div>
            </div>
        </div>

    @endif
@endsection
@push('script-page')
    <script src="{{url('js/swiper.min.js')}}"></script>
    <script type="text/javascript">



        $(document).on('click', '.view-images', function () {

                var p_url = "{{route('medical-allowance.image.view')}}";
                var data = {
                    'id': $(this).attr('data-id')
                };
                    postAjax(p_url, data, function (res) {
                        $('.image_sider_div').html(res);
                        $('#exampleModalCenter').modal('show');
                    });
        });
        
        $(document).on('change', '#employee_id', function () {
    var employee_id = $(this).val();

    $.ajax({
        url: '{{route('medical-allowance.jsoncount')}}',
        type: 'POST',
        data: {
            "employee_id": employee_id,
            "_token": "{{ csrf_token() }}",
        },
        success: function (data) {
            $('#reimbursment_type').empty();
            $('#reimbursment_type').append('<option value="">{{__('Select Reimbursement Type')}}</option>');

            $.each(data, function (key, value) {
                var optionText = value.title + ' (' + value.total_amount + '/' + value.amount + ')';
                var optionValue = value.title;

                if (value.total_amount >= value.amount) {
                    optionText = optionText + ' (No money left over)';
                    $('#reimbursment_type').append('<option value="' + optionValue + '" disabled>' + optionText + '</option>');
                } else {
                    $('#reimbursment_type').append('<option value="' + optionValue + '">' + optionText + '</option>');
                }
            });

            // Cek apakah jumlah yang dimasukkan melebihi sisa reimbursement
            $('#amount').on('input', function() {
                var selectedReimbursementType = $('#reimbursment_type').val();
                var reimbursementAmount = 0;
                var totalAmount = 0;

                // Cari reimbursement type yang dipilih
                $.each(data, function (key, value) {
                    if (value.title === selectedReimbursementType) {
                        reimbursementAmount = value.amount;
                        totalAmount = value.total_amount;
                        return false; // Hentikan pencarian
                    }
                });

                var inputAmount = parseFloat($(this).val());
                var remainingAmount = parseFloat(reimbursementAmount) - parseFloat(totalAmount);

                if (inputAmount > remainingAmount) {
                    $('#amount').addClass('is-invalid');
                    $('#amount-error').text('Must not exceed the remaining reimbursement amount.').show();
                    // $(this).val(remainingAmount.toFixed(0)); // Set nilai input menjadi sisa reimbursement
                } else {
                    $('#amount').removeClass('is-invalid');
                    $('#amount-error').hide();
                }

            });
        }
    });
});


    </script>
@endpush
