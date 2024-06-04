@extends('layouts.admin')
@section('page-title')
    {{__('Manage Bill')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Bill')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">

        @can('create payment')
            <a href="#" data-url="{{ route('payment.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip"  data-size="lg" data-title="{{__('Create New Bill')}}"  title="{{__('Create')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@push('script-page')
    <script src="{{url('js/swiper.min.js')}}"></script>
    <script type="text/javascript">



        $(document).on('click', '.view-images-receipt', function () {

                var p_url = "{{route('payment-receipt.image.view')}}";
                var data = {
                    'id': $(this).attr('data-id')
                };
                    postAjax(p_url, data, function (res) {
                        $('.image_sider_div').html(res);
                        $('#exampleModalCenter').modal('show');
                    });
        });

        $(document).on('click', '.view-images-bill', function () {

                var p_url = "{{route('payment-bill.image.view')}}";
                var data = {
                    'id': $(this).attr('data-id')
                };
                    postAjax(p_url, data, function (res) {
                        $('.image_sider_div').html(res);
                        $('#exampleModalCenter').modal('show');
                    });
        });


    </script>
@endpush


@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('payment.index'),'method' => 'GET','id'=>'payment_form')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}
                                            {{ Form::date('date', isset($_GET['date'])?$_GET['date']:'', array('class' => 'form-control month-btn ','id'=>'pc-daterangepicker-1')) }}
                                        </div>
                                    </div>
                                    {{-- <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('account', __('Account'),['class'=>'form-label']) }}
                                            {{ Form::select('account',$account,isset($_GET['account'])?$_GET['account']:'', array('class' => 'form-control select' ,'id'=>'choices-multiple')) }}
                                        </div>
                                    </div> --}}
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('vender', __('Client'),['class'=>'form-label']) }}
                                            {{ Form::select('vender',$vender,isset($_GET['vender'])?$_GET['vender']:'', array('class' => 'form-control select','id'=>'choices-multiple1')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('category', __('Category'),['class'=>'form-label']) }}
                                            {{ Form::select('category',$category,isset($_GET['category'])?$_GET['category']:'', array('class' => 'form-control select','id'=>'choices-multiple2')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div clas="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('payment_form').submit(); return false;" data-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>

                                        <a href="{{ route('productservice.index') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                           title="{{ __('Reset') }}">
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Issue Date')}}</th>
                                <th>{{__('Partner Name')}}</th>
                                {{-- <th>{{__('Account')}}</th> --}}
                                <th>{{__('Vendor')}}</th>
                                <th>{{__('Category')}}</th>
                                <th>{{__('Amount Before Tax')}}</th>
                                <th>{{__('Tax (%)')}}</th>
                                <th>{{__('Amount After Tax')}}</th>
                                <th>{{__('Description')}}</th>
                                <th>{{__('Payment Receipt')}}</th>
                                <th>{{__('Bills')}}</th>
                                <th>{{__('Status')}}</th>
                                @if(Gate::check('edit payment') || Gate::check('delete payment'))
                                    <th>{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @php
                               $totalAmountRp = 0;
                               $totalAmountEuro = 0;
                            @endphp

                            @foreach ($payments as $payment)
                                @php
                                    if($payment->currency == 'Rp')
                                    {
                                        $totalAmountRp += $payment->amount;
                                    }
                                    else
                                    {
                                         $totalAmountEuro += $payment->amount;
                                    }

                                @endphp
                                <tr class="font-style">
                                    <td>{{  Auth::user()->dateFormat($payment->date)}}</td>
                                    <td>{{  !empty($payment->user)?$payment->user->name:'-'}}</td>
                                    {{-- <td>{{ !empty($payment->account)?$payment->account->code .' '. $payment->account->name:''}}</td> --}}
                                    <td>{{  !empty($payment->vender)?$payment->vender->name:'-'}}</td>
                                    <td>{{  !empty($payment->category)?$payment->category->name:'-'}}</td>
                                    <td>{{  $payment->currency . '' . number_format($payment->amount_before_tax)}}</td>
                                    <td>{{  !empty($payment->tax)?$payment->tax:'-'}}</td>
                                    <td>{{  $payment->currency . '' . number_format($payment->amount)}}</td>
                                    <td>{{  !empty($payment->description)?$payment->description:'-'}}</td>
                                    <td>
                                        @if(!empty($payment->add_receipt))
                                            <img alt="Image placeholder" src="{{ asset('assets/images/gallery.png')}}" class="avatar view-images-receipt rounded-circle avatar-sm" data-bs-toggle="tooltip" title="{{__('View Screenshot images')}}" data-original-title="{{__('View Screenshot images')}}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="{{$payment->id}}" id="track-images-{{$payment->id}}">
                                        @else
                                            
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($payment->add_bill))
                                            <img alt="Image placeholder" src="{{ asset('assets/images/gallery.png')}}" class="avatar view-images-bill rounded-circle avatar-sm" data-bs-toggle="tooltip" title="{{__('View Screenshot images')}}" data-original-title="{{__('View Screenshot images')}}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="{{$payment->id}}" id="track-images-{{$payment->id}}">
                                        @else
                                            
                                        @endif
                                    </td>
                                    <td>
                                        @if ($payment->status == 0)
                                            <span
                                                class="status_badge badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Payment::$statues[$payment->status]) }}</span>
                                        @elseif($payment->status == 1)
                                            <span
                                                class="status_badge badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Payment::$statues[$payment->status]) }}</span>
                                        @elseif($payment->status == 2)
                                            <span
                                                class="status_badge badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Payment::$statues[$payment->status]) }}</span>
                                        @elseif($payment->status == 3)
                                            <span
                                                class="status_badge badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Payment::$statues[$payment->status]) }}</span>
                                        @endif
                                    </td>
                                    @if(Gate::check('edit payment') || Gate::check('delete payment'))
                                        <td class="action text-end">
                                            @can('edit payment')
                                                @if ($payment->status == 0 || $payment->status == 2) 
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="#" data-url="{{ URL::to('payment/'.$payment->id.'/action') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Expense Action')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Expense Action')}}" data-original-title="{{__('Expense Action')}}">
                                                        <i class="ti ti-caret-right text-white"></i> </a>
                                                </div>
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('payment.edit',$payment->id) }}" data-ajax-popup="true" data-title="{{__('Edit Bill')}}" data-size="lg" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                                @else

                                                @endif
                                            @endcan
                                            @can('delete payment')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['payment.destroy', $payment->id],'id'=>'delete-form-'.$payment->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" data-original-title="{{__('Delete')}}" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$payment->id}}').submit();">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="12" style="border: 1px solid black; text-align: center; background-color:#008b8b; color:white; font-weight: bold;"><strong>Total Bill (Rp) : {{ \Auth::user()->priceFormat($totalAmountRp) }} </strong><strong>|</strong> <strong>Total Bill (€) : {{ \Auth::user()->priceFormat2($totalAmountEuro) }} </strong></td>
                            </tr>
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
@endsection
