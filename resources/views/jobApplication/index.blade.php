@extends('layouts.admin')
@section('page-title')
    {{__('Manage Job Application')}}
@endsection
@push('css-page')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
@endpush
@push('script-page')
    {{-- <script src="{{ asset('libs/dragula/dist/dragula.min.js') }}"></script>
    <script src="{{ asset('libs/autosize/dist/autosize.min.js') }}"></script> --}}
    <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>

    <script>
        $(document).on('change', '#jobs', function () {

            var id = $(this).val();

            $.ajax({
                url: "{{ route('get.job.application') }}",
                type: 'POST',
                data: {
                    "id": id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    var job = JSON.parse(data);
                    // console.log(job)
                    var applicant = job.applicant;
                    var visibility = job.visibility;
                    var question = job.custom_question;

                    (applicant.indexOf("gender") != -1) ? $('.gender').removeClass('d-none') : $(
                        '.gender').addClass('d-none');
                    (applicant.indexOf("dob") != -1) ? $('.dob').removeClass('d-none') : $('.dob')
                        .addClass('d-none');
                    (applicant.indexOf("country") != -1) ? $('.country').removeClass('d-none') : $(
                        '.country').addClass('d-none');

                    (visibility.indexOf("profile") != -1) ? $('.profile').removeClass('d-none') : $(
                        '.profile').addClass('d-none');
                    (visibility.indexOf("resume") != -1) ? $('.resume').removeClass('d-none') : $(
                        '.resume').addClass('d-none');
                    (visibility.indexOf("kk") != -1) ? $('.kk').removeClass('d-none') : $(
                        '.kk').addClass('d-none');
                    (visibility.indexOf("ktp") != -1) ? $('.ktp').removeClass('d-none') : $(
                        '.ktp').addClass('d-none');
                    (visibility.indexOf("transkrip_nilai") != -1) ? $('.transkrip_nilai').removeClass('d-none') : $(
                        '.transkrip_nilai').addClass('d-none');
                    (visibility.indexOf("ijazah") != -1) ? $('.ijazah').removeClass('d-none') : $(
                        '.ijazah').addClass('d-none');
                    (visibility.indexOf("certificate") != -1) ? $('.certificate').removeClass('d-none') : $(
                        '.certificate').addClass('d-none');
                    (visibility.indexOf("letter") != -1) ? $('.letter').removeClass('d-none') : $(
                        '.letter').addClass('d-none');

                    $('.question').addClass('d-none');
                    // $('.question').removeAttr('required');

                    if (question.length > 0) {
                        question.forEach(function (id) {
                            $('.question_' + id + '').removeClass('d-none');
                        });
                    }


                }
            });
        });

        @can('move job application')
            !function (a) {
            "use strict";

            var t = function () {
                this.$body = a("body")
            };
            t.prototype.init = function () {
                // console.log(t);
                a('[data-plugin="dragula"]').each(function () {

                    //   console.log(t);
                    var t = a(this).data("containers"),

                        n = [];
                    if (t)
                        for (var i = 0; i < t.length; i++) n.push(a("#" + t[i])[0]);
                    else n = [a(this)[0]];
                    var r = a(this).data("handleclass");
                    r ? dragula(n, {
                        moves: function (a, t, n) {
                            return n.classList.contains(r)
                        }
                    }) : dragula(n).on('drop', function (el, target, source, sibling) {
                        var order = [];
                        $("#" + target.id + " > div").each(function () {
                            order[$(this).index()] = $(this).attr('data-id');
                        });

                        var id = $(el).attr('data-id');

                        var old_status = $("#" + source.id).data('status');
                        var new_status = $("#" + target.id).data('status');
                        var stage_id = $(target).attr('data-id');


                        $("#" + source.id).parent().find('.count').text($("#" + source.id +
                            " > div").length);
                        $("#" + target.id).parent().find('.count').text($("#" + target.id +
                            " > div").length);
                        $.ajax({
                            url: '{{ route('job.application.order') }}',
                            type: 'POST',
                            data: {
                                application_id: id,
                                stage_id: stage_id,
                                order: order,
                                new_status: new_status,
                                old_status: old_status,
                                "_token": $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (data) {
                                show_toastr('Success', 'Lead successfully updated',
                                    'success');
                            },
                            error: function (data) {
                                data = data.responseJSON;
                                show_toastr('Error', data.error, 'error')
                            }
                        });
                    });
                })
            }, a.Dragula = new t, a.Dragula.Constructor = t
        }(window.jQuery),
            function (a) {
                "use strict";

                a.Dragula.init()

            }(window.jQuery);
        @endcan
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Job Application')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
        {{--            <i class="ti ti-filter"></i>--}}
        {{--        </a>--}}

        {{-- @can('create job application')
            <a href="#" data-size="lg" data-url="{{ route('job-application.create')}}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Job Application')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan --}}

    </div>
@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('job-application.index'),'method'=>'get','id'=>'job_application')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto" style = "width:500px;">
                                        <div class="btn-box">
                                            {{ Form::label('university', __('Univercity'), ['class' => 'form-label']) }}
                                            {{ Form::select('university', $univercity, isset($_GET['university']) ? $_GET['university'] : null, ['class' => 'form-control select2', 'placeholder' => 'Select Univercity']) }}
                                        </div>
                                    </div>
                                    <div class="col-auto" style = "width:200px;">
                                        <div class="btn-box">
                                            {{ Form::label('ipk', __('Ipk'), ['class' => 'form-label']) }}
                                            {{ Form::select('ipk', $ipk, isset($_GET['ipk']) ? $_GET['ipk'] : null, ['class' => 'form-control select2', 'placeholder' => 'Select Ipk']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('job_application').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('job-application.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th scope="col">{{__('Name')}}</th>
                                    <th scope="col">{{__('DoB')}}</th>
                                    <th scope="col">{{__('Gender')}}</th>
                                    <th scope="col">{{__('Phone')}}</th>
                                    <th scope="col">{{__('Email')}}
                                    <th scope="col">{{__('City')}}</th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody class="list">
                                @if(count($applicants) > 0)
                                    @foreach($applicants as $applicant)
                                        <tr>
                                            <td>
                                                <div class="avatar-group">
                                                    <a href="#" class="avatar rounded-circle avatar-sm">
                                                        <img data-original-title="{{(!empty($applicant)?$applicant->name:'')}}" @if($applicant->profile) src="{{asset('/storage/uploads/job/profile'.$applicant->profile)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif title="{{ $applicant->name }}" class="hweb">
                                                    </a>
                                                    {{ $applicant->name }}
                                                </div>
                                            </td>
                                            <td>{{!empty($applicant->dob)?$applicant->dob:'-'}}</td>
                                            <td>{{!empty($applicant->gender)?$applicant->gender:'-'}}</td>
                                            <td>{{!empty($applicant->phone)?$applicant->phone:'-'}}</td>
                                            <td>{{!empty($applicant->email)?$applicant->email:'-'}}</td>
                                            <td>{{!empty($applicant->city)?$applicant->city:'-'}}</td>
                                            <td>
                                           <select class="form-control select" name="stage" id="stage" style = "width: 100px;" onchange="updateStage(this.value, {{ $applicant->id }})">
                                                <option value="0" hidden>{{$applicant->stage_status->title}}</option>
                                                @foreach($stages as $stage)
                                                    <option value="{{ $stage->id }}">{{ $stage->title }}</option>
                                                @endforeach
                                            </select>
                                            </td>
                                            <td class="Action">
                                                <span>
                                                    @can('show job application')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="{{ route('job-application.show',\Crypt::encrypt($applicant->id)) }}" 
                                                            class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-bs-original-title="{{__('View ').$applicant->name}}">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete job application')
                                                        <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['job-application.destroy', $applicant->id],'id'=>'delete-form-'.$applicant->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm-yes="document.getElementById('delete-form-{{$applicant->id}}').submit();">
                                                            <i class="ti ti-trash text-white"></i></a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
    
                                                </span>
                                        </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th scope="col" colspan="7"><h6 class="text-center">{{__('No applicants found')}}</h6></th>
                                    </tr>
                                @endif
                                </tbody>
                            </table>

                            <script>

                                function updateStage(stage, id) {
                                    // Kirim request POST ke server dengan nilai status dan id data yang dipilih
                                    $.ajax({
                                        url: "{{route("update-stage-job")}}",
                                        type: "POST",
                                        data: { 
                                            id: id,
                                            stage: stage,
                                            // Add the CSRF token to the request data
                                            _token: "{{ csrf_token() }}"
                                        },
                                        success: function (data) {
                                            console.log(data);
                                        },
                                    });
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
