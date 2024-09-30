@extends('layouts.admin')

@section('page-title')
    {{__('Manage Absence Request')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Manage Absence Request')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create leave')
        <a href="#" data-size="lg" data-url="{{ route('absence-request.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Absence Request')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                @if(\Auth::user()->type != 'intern')
                <div class="card-header"><h6 class="mb-0" style="font-size: 15px;">{{__('Leave Request')}}</h6></div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    @if(\Auth::user()->type!='employee')
                                        <th>{{__('Employee')}}</th>
                                    @endif
                                    <th>{{__('Attendance Type')}}</th>
                                    <th>{{__('Leave Type')}}</th>
                                    <th>{{__('Applied On')}}</th>
                                    <th>{{__('Start Date')}}</th>
                                    <th>{{__('End Date')}}</th>
                                    <th>{{__('Total Days')}}</th>
                                    <th>{{__('Leave Reason')}}</th>
                                    <th>{{__('status')}}</th>
                                    <th width="200px">{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($absence_leave as $leave)
                                    <tr>
                                        @if(\Auth::user()->type!='employee')
                                            <td>{{ !empty(\Auth::user()->getEmployee($leave->employee_id))?\Auth::user()->getEmployee($leave->employee_id)->name:'-' }}</td>
                                        @endif
                                        <td>{{ !empty($leave->absence_type)?$leave->absence_type:'-' }}</td>
                                        <td>{{ !empty(\Auth::user()->getLeaveType($leave->leave_type_id))?\Auth::user()->getLeaveType($leave->leave_type_id)->title:'-' }}</td>
                                        <td>{{ \Auth::user()->dateFormat($leave->applied_on )}}</td>
                                        <td>{{ \Auth::user()->dateFormat($leave->start_date ) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($leave->end_date )  }}</td>
                                        @php
                                            $startDate = new \DateTime($leave->start_date);
                                            $endDate = new \DateTime($leave->end_date);
                                            $total_leave_days = 0;

                                            while ($startDate <= $endDate) {
                                                if ($startDate->format('N') <= 5) { // Memeriksa apakah hari adalah Senin hingga Jumat
                                                    $total_leave_days++;
                                                }
                                                $startDate->add(new \DateInterval('P1D')); // Menambahkan 1 hari ke tanggal start_date
                                            }
                                        @endphp
                                        <td>{{ $total_leave_days }}</td>
                                        <td>{{ $leave->leave_reason }}</td>
                                        <td>

                                            @if($leave->status=="Pending")
                                                <div class="status_badge badge bg-warning p-2 px-3 rounded">{{ $leave->status }}</div>
                                            @elseif($leave->status=="Approved")
                                                <div class="status_badge badge bg-success p-2 px-3 rounded">{{ $leave->status }}</div>
                                            @else($leave->status=="Reject")
                                                <div class="status_badge badge bg-danger p-2 px-3 rounded">{{ $leave->status }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($leave->status == "Pending")
                                                @can('edit leave')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" data-url="{{ URL::to('absence-request/'.$leave->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Attendance Request')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                                </div>
                                                @endcan
                                            @endif
                                            {{-- @can('delete leave')
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['absence-request.destroy', $leave->id],'id'=>'delete-form-'.$leave->id]) !!}
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$leave->id}}').submit();">
                                                <i class="ti ti-trash text-white"></i></a>
                                                {!! Form::close() !!}
                                            </div> --}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                <div class="card-header"><h6 class="mb-0" style="font-size: 15px;">{{__('Sick Request')}}</h6></div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatables">
                            <thead>
                                <tr>
                                    @if(\Auth::user()->type!='employee')
                                        <th>{{__('Employee')}}</th>
                                    @endif
                                    <th>{{__('Total Days')}}</th>
                                    <th>{{__('Sick Letter')}}</th>
                                    <th>{{__('Date Sick Letter')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($absence_sick as $sick)
                                    <tr>
                                        @if(\Auth::user()->type!='employee')
                                            <td>{{ !empty(\Auth::user()->getEmployee($sick->employee_id))?\Auth::user()->getEmployee($sick->employee_id)->name:'-' }}</td>
                                        @endif
                                        <td>{{ !empty($sick->total_sick_days)?$sick->total_sick_days:'-' }}</td>
                                        <td>
                                            <img alt="Image placeholder" src="{{ asset('assets/images/gallery.png')}}" class="avatar view-images rounded-circle avatar-sm" data-bs-toggle="tooltip" title="{{__('View Sick Letter')}}" data-original-title="{{__('View Sick Letter')}}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="{{$sick->id}}" id="track-images-{{$sick->id}}">
                                        </td>
                                         <td>{{ !empty($sick->date_sick_letter)?$sick->date_sick_letter:'-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-3">
        {{ $absence_leave->links() }}
    </div>
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg ss_modale " role="document">
          <div class="modal-content image_sider_div">

          </div>
        </div>
    </div>

    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                <div class="card-header"><h6 class="mb-0">{{__('Request Approval Leave')}}</h6></div>
                <div class="card-body table-border-style">
                        <div class="table-responsive">
                        <table class="table datatabless">
                                <thead>
                                <tr>
                                    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
                                        <th>{{__('Employee')}}</th>
                                    @endif
                                    <th>{{__('Leave Type')}}</th>
                                    <th>{{__('Applied On')}}</th>
                                    <th>{{__('Start Date')}}</th>
                                    <th>{{__('End Date')}}</th>
                                    <th>{{__('Total Days')}}</th>
                                    <th>{{__('Leave Reason')}}</th>
                                    <th width="200px">{{__('Status')}}</th>
                                    <th width="100px">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($approval as $approvals)
                                    <tr>
                                        @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                            <td>{{!empty($approvals->employees->name)?$approvals->employees->name:'-'}}</td>
                                        @endif
                                        <td>{{ !empty(\Auth::user()->getLeaveType($approvals->leave_type_id))?\Auth::user()->getLeaveType($approvals->leave_type_id)->title:'' }}</td>
                                        <td>{{ \Auth::user()->dateFormat($approvals->applied_on )}}</td>
                                        <td>{{ \Auth::user()->dateFormat($approvals->start_date ) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($approvals->end_date )  }}</td>
                                        @php
                                            $startDate = new \DateTime($approvals->start_date);
                                            $endDate = new \DateTime($approvals->end_date);
                                            $total_leave_days = 0;

                                            while ($startDate <= $endDate) {
                                                if ($startDate->format('N') <= 5) { // Memeriksa apakah hari adalah Senin hingga Jumat
                                                    $total_leave_days++;
                                                }
                                                $startDate->add(new \DateInterval('P1D')); // Menambahkan 1 hari ke tanggal start_date
                                            }
                                        @endphp
                                        <td>{{ $total_leave_days }}</td>
                                        <td>{{ $approvals->leave_reason }}</td>
                                        <td>

                                        @if($approvals->status=="Pending")
                                            <div class="status_badge badge bg-warning p-2 px-3 rounded">{{ $approvals->status }}</div>
                                        @elseif($approvals->status=="Approved")
                                            <div class="status_badge badge bg-success p-2 px-3 rounded">{{ $approvals->status }}</div>
                                        @else($approvals->status=="Reject")
                                            <div class="status_badge badge bg-danger p-2 px-3 rounded">{{ $approvals->status }}</div>
                                        @endif
                                        </td>
                                        <td>
                                           <div class="action-btn bg-warning ms-2">
                                            <a href="#" data-url="{{ URL::to('absence-request/'.$approvals->id.'/action') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Leave Action')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Leave Action')}}" data-original-title="{{__('Leave Action')}}">
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
    @endif
@endsection

@push('script-page')
    <script src="{{url('js/swiper.min.js')}}"></script>
    <script>

        $(document).on('click', '.view-images', function () {

                var p_url = "{{route('sick-letter.image.view')}}";
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
                        url: '{{route('absence-request.jsoncount')}}',
                        type: 'POST',
                        data: {
                            "employee_id": employee_id, "_token": "{{ csrf_token() }}",
                        },
                        success: function (data) {
                            $('#leave_type_id').empty();
                            $('#leave_type_id').append('<option value="">{{__('Select Leave Type')}}</option>');

                            $.each(data, function (key, value) {
                                var optionText = value.title + ' (' + value.total_leave + '/' + value.days + ')';
                                var optionValue = value.id;

                                $('#leave_type_id').append('<option value="' + optionValue + '">' + optionText + '</option>');

                                {{-- if (value.total_leave >= value.days) {
                                    optionText = optionText + ' (No remaining leave)';
                                    $('#leave_type_id').append('<option value="' + optionValue + '" disabled>' + optionText + '</option>');
                                } else {
                                    $('#leave_type_id').append('<option value="' + optionValue + '">' + optionText + '</option>');
                                } --}}
                            });

                            // Reset start_date and end_date inputs
                            $('#start_date').val('');
                            $('#end_date').val('');
                        }
                    });
                });

                function isWeekend(date) {
                return date.getDay() === 0 || date.getDay() === 6;
                }

                function getNextWorkingDay(date) {
            while (isWeekend(date) || date.getDay() === 5) {
                date.setDate(date.getDate() + 1);
            }
            return date;
        }


        {{-- $(document).on('change', '#start_date', function () {
        var leaveTypeId = $('#leave_type_id').val();
        var leaveType = $('#leave_type_id').find(':selected').text();

        if (leaveTypeId) {
            var remainingLeave = leaveType.match(/\(([^)]+)\)/)[1].split('/')[1];
            var useLeave = leaveType.match(/\(([^)]+)\)/)[1].split('/')[0].replace('(', '');

            var remaining_leave = remainingLeave - useLeave;
            
            if (remaining_leave > 0) {
            var startDate = new Date($(this).val());
            var endDate = getNextWorkingDay(new Date(startDate));
            endDate.setDate(endDate.getDate() + parseInt(remaining_leave) - 1);

            var formattedEndDate = endDate.toISOString().split('T')[0];

            $('#end_date').attr('max', formattedEndDate);
            $('#end_date').val('');
            }
        }
        }); --}}

        {{-- $(document).on('change', '#end_date', function () {
        var startDate = new Date($('#start_date').val());
        var endDate = new Date($(this).val());

        if (endDate < startDate) {
            $(this).val('');
            alert('End date cannot be before start date.');
        }
        }); --}}

        {{-- $(document).on('focus', '#end_date', function () {
        var startDate = new Date($('#start_date').val());

        $(this).prop('disabled', false);
        $(this).attr('min', $('#start_date').val());

        var selectedDate = new Date($(this).val());
        if (selectedDate < startDate || isWeekend(selectedDate)) {
            $(this).val('');
        }
        });

        $(document).on('change', '#end_date', function () {
        var selectedDate = new Date($(this).val());
        var startDate = new Date($('#start_date').val());

        if (selectedDate < startDate || isWeekend(selectedDate)) {
            $(this).val('');
        }
        }); --}}

        {{-- $(document).ready(function () {
        $('#end_date').prop('disabled', true);
        }); --}}

    </script>
@endpush
