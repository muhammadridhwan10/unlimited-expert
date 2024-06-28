@extends('layouts.admin')

@section('page-title')
    {{__('Manage Reimbursment Client')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Manage Reimbursment Client')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="lg" data-url="{{ route('reimbursment-client.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Reimbursment Client')}}" class="btn btn-sm btn-primary">
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
                        {{ Form::open(array('route' => array('reimbursment-client.index'),'method'=>'get','id'=>'report_monthly_reimbursment-client')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto" style = "width:300px;">
                                        <div class="btn-box">
                                            {{ Form::label('client_id', __('Client'), ['class' => 'form-label']) }}
                                            {{ Form::select('client_id', $client, isset($_GET['client_id']) ? $_GET['client_id'] : null, ['class' => 'form-control select2', 'placeholder' => __('Select client')]) }}
                                        </div>
                                    </div>
                                    <div class="col-auto"  style = "width:300px;">
                                        <div class="btn-box">
                                            {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                            {{ Form::select('employee_id', $employees, isset($_GET['employee_id']) ? $_GET['employee_id'] : null, ['class' => 'form-control select2', 'placeholder' => __('Select employee')]) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="btn-box">
                                            {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                            {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control'))}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_monthly_reimbursment-client').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('reimbursment-client.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <div>
        <p style="color:red">
            *Maximum client reimbursement input is done  <strong>1 WEEK AFTER TRANSACTION OUT</strong>.
        </p>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
            <div class="card-body table-border-style">
                    <div class="table-responsive">
                    <table class="table datatable">
                            <thead>
                            <tr>
                                @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                    <th>{{__('Employee')}}</th>
                                @endif
                                <th>{{__('Client')}}</th>
                                <th>{{__('Approval By')}}</th>
                                <th>{{__('Reimbursment Type')}}</th>
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
                                                <a href="#" data-url="{{ URL::to('reimbursment-client/'.\Crypt::encrypt($reimbursment->id).'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Medical Allowance')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                            </div>
                                        @endif
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


    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior accounting')
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                <div class="card-header"><h6 class="mb-0">{{__('Request Approval Reimbursment')}}</h6></div>
                <div class="card-body table-border-style">
                        <div class="float-end">
                            <button class="btn btn-primary" id="approve-selected">Approve Selected</button>
                        </div>
                        <div class="table-responsive">
                        <table class="table datatables">
                                <thead>
                                <tr>
                                    <th></th>
                                    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                        <th>{{__('Employee')}}</th>
                                    @endif
                                    <th>{{__('Client')}}</th>
                                    <th>{{__('Approval By')}}</th>
                                    <th>{{__('Reimbursment Type')}}</th>
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
                                    <td><input type="checkbox" class="approval-checkbox" data-id="{{ $approvals->id }}"></td>
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
                                            <a href="#" data-url="{{ URL::to('reimbursment-client/'.$approvals->id.'/action') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Overtime Action')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Overtime Action')}}" data-original-title="{{__('Overtime Action')}}">
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

                var p_url = "{{route('reimbursment-client.image.view')}}";
                var data = {
                    'id': $(this).attr('data-id')
                };
                    postAjax(p_url, data, function (res) {
                        $('.image_sider_div').html(res);
                        $('#exampleModalCenter').modal('show');
                    });
        });


    </script>
    <script>
        $(document).ready(function () {
            $('#approve-selected').click(function () {
                var selectedIds = [];
                $('.approval-checkbox:checked').each(function () {
                    selectedIds.push($(this).data('id'));
                });

                if (selectedIds.length === 0) {
                    alert('Pilih setidaknya satu item untuk di-approve.');
                    return;
                }

                var url = "{{ route('approve-multiple-client') }}";
                var data = {
                    selectedIds: selectedIds,
                    _token: "{{ csrf_token() }}"
                };

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: data,
                    success: function (response) {
                        alert('Items have been approved successfully.');
                        window.location.reload();
                    },
                    error: function () {
                        alert('Something went wrong. Please try again later.');
                    },
                });
            });
        });
    </script>
@endpush
