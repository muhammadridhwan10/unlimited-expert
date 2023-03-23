@extends('layouts.admin')
@php
    $profile=asset(Storage::url('uploads/avatar/'));
@endphp
@section('page-title')
    {{__('Profile Account')}}
@endsection
@push('script-page')
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300,
        })
        $(".list-group-item").click(function(){
            $('.list-group-item').filter(function(){
                return this.href == id;
            }).parent().removeClass('text-primary');
        });
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Profile')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-3">
            <div class="card sticky-top" style="top:30px">
                <div class="list-group list-group-flush" id="useradd-sidenav">
                    <a href="#project_and_task_info" class="list-group-item list-group-item-action border-0">{{__('Project and Task Info')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                    <a href="#employee_record" class="list-group-item list-group-item-action border-0">{{__('Employee Record')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                    <a href="#personal_info" class="list-group-item list-group-item-action border-0">{{__('Personal Info')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                </div>
            </div>
        </div>
        <div class="col-xl-9">
            <div id="project_and_task_info" class="card">
                <div class="card-header">
                    <h5>{{('Project and Task Info')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-xxl-6">
                            <div class="card">
                                <div class="card-header border-0 pb-0">
                                    <div class="d-flex align-items-center">
                                        <h5 class="col-12 text-center"><a class="text-dark"><i style="font-size: 45px" class="ti ti-share"></i></a></h5>
                                    </div>
                                </div>
                                <div class="card-body">                      
                                    <div class="card mb-0 mt-3">
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-12 text-center">
                                                    <h6 class="mb-0" style="font-size: 40px">{{ $total_project }}</h6>
                                                    <p class="mb-0" style="font-size: 20px">{{__('Total Project')}}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xxl-6">
                            <div class="card">
                                <div class="card-header border-0 pb-0">
                                    <div class="d-flex align-items-center">
                                    <h5 class="col-12 text-center"><a class="text-dark"><i style="font-size: 45px" class="ti ti-book"></i></a></h5>
                                    </div>
                                </div>
                                <div class="card-body">                      
                                    <div class="card mb-0 mt-3">
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-12 text-center">
                                                <h6 class="mb-0" style="font-size: 40px">{{ $total_user_task }}</h6>
                                                    <p class="mb-0" style="font-size: 20px">{{__('Total Task')}}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 mt-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{('Project Info')}}</h5>
                                </div>
                                <div class="card-body mt-3 mx-2">
                                    <div class="row mt-2">
                                        <div class="table-responsive">
                                            <table class="table datatable">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Project Name') }}</th>
                                                        <th>{{ __('Book Year') }}</th>                                        
                                                        <th>{{ __('Start Date') }}</th>
                                                        <th>{{ __('End Date') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                    </tr>
                                                </thead>
                                                    <tbody class="list">
                                                    @foreach($get_project as $project)
                                                        <tr>
                                                            <td>{{$project->project->project_name}}</td>
                                                            <td>{{$project->project->book_year}}</td>
                                                            <td>{{$project->project->start_date}}</td>
                                                            <td>{{$project->project->end_date}}</td>
                                                            <td>
                                                                @if($project->project->status == 'in_progress')
                                                                <div class="badge  bg-success p-2 px-3 rounded"> {{ __('In Progress')}}</div>
                                                                @elseif($project->project->status == 'on_hold')
                                                                <div class="badge  bg-secondary p-2 px-3 rounded">{{ __('On Hold')}}</div>
                                                                @elseif($project->project->status == 'Canceled')
                                                                <div class="badge  bg-success p-2 px-3 rounded"> {{ __('Canceled')}}</div>
                                                                @else
                                                                    <div class="badge bg-warning p-2 px-3 rounded">{{ __('Finished')}}</div>
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
                    </div>
                </div>

            </div>
            <div id="employee_record" class="card">
                <div class="card-header">
                    <h5>{{('Employee Record')}}</h5>
                </div>
                <div class="card-body">
                    {{Form::model($userDetail,array('route' => array('update.account'), 'method' => 'post', 'enctype' => "multipart/form-data"))}}
                        @csrf
                        <div class="row">
                            <div class="col-md-12 col-xxl-12">
                                <div class="card">
                                    <div class="card-header border-0 pb-0">
                                        <div class="d-flex align-items-center">
                                            <h5 class="col-12 text-center"><a class="text-dark"><i style="font-size: 45px" class="ti ti-star"></i></a></h5>
                                        </div>
                                    </div>
                                    <div class="card-body">                      
                                        <div class="card mb-0 mt-3">
                                            <div class="card-body p-3">
                                                <div class="row">
                                                    <div class="col-12 text-center">
                                                        <h6 class="mb-0" style="font-size: 40px">{{ $total_training }}</h6>
                                                        <p class="mb-0" style="font-size: 20px">{{__('Total Training or Sertifikasi')}}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 mt-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{('Training or Sertifikasi Info')}}</h5>
                                </div>
                                <div class="card-body mt-3 mx-2">
                                    <div class="row mt-2">
                                        <div class="table-responsive">
                                            <table class="table datatables">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Training Description') }}</th>
                                                        <th>{{ __('Training Type') }}</th>                                        
                                                        <th>{{ __('Employee') }}</th>
                                                        <th>{{ __('Trainer') }}</th>
                                                        <th>{{ __('Training Duration') }}</th>
                                                    </tr>
                                                </thead>
                                                    <tbody class="list">
                                                    @foreach($get_training as $training)
                                                        <tr>
                                                            <td>{{$training->description}}</td>
                                                            <td>{{ !empty($training->types)?$training->types->name:'' }}
                                                            <td>{{ !empty($training->employees)?$training->employees->name:'' }} </td>
                                                            <td>{{ !empty($training->trainers)?$training->trainers->firstname:'' }}</td>
                                                            <td>{{\Auth::user()->dateFormat($training->start_date) .' to '.\Auth::user()->dateFormat($training->end_date)}}</td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </form>

                </div>

            </div>
            <div id="personal_info" class="card">
                <div class="card-header">
                    <h5>{{('Personal Info')}}</h5>
                </div>
                <div class="card-body">
                    {{Form::model($userDetail,array('route' => array('update.account'), 'method' => 'post', 'enctype' => "multipart/form-data"))}}
                        @csrf
                        <div class="row">
                            <div class="col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <label class="col-form-label text-dark">{{__('Name')}}</label>
                                    <input class="form-control @error('name') is-invalid @enderror" name="name" type="text" id="name" placeholder="{{ __('Enter Your Name') }}" value="{{ $userDetail->name }}" required autocomplete="name">
                                    @error('name')
                                    <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <label for="email" class="col-form-label text-dark">{{__('Email')}}</label>
                                    <input class="form-control @error('email') is-invalid @enderror" name="email" type="text" id="email" placeholder="{{ __('Enter Your Email Address') }}" value="{{ $userDetail->email }}" required autocomplete="email">
                                    @error('email')
                                    <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12 col-sm-12">
                                <div class="form-group">
                                {{Form::label('personal_description',__('Personal Description'),['class'=>'form-label'])}}
                                {{Form::textarea('personal_description',null,array('class'=>'form-control','placeholder'=>__('Enter Description')))}}
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <div class="form-group">
                                    <div class="choose-files">
                                        <label for="avatar">
                                            <div class=" bg-primary profile_update"> <i class="ti ti-upload px-1"></i>{{__('Choose file here')}}</div>
                                            <input type="file" class="form-control file" accept=".png, .jpg, .jpeg" name="profile" id="avatar" data-filename="profile_update">
                                        </label>
                                    </div>
                                    <span class="text-xs text-muted">{{ __('Please upload a valid image file. Size of image should not be more than 2MB.')}}</span>
                                    @error('avatar')
                                    <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                                    @enderror

                                </div>

                            </div>
                            <div class="col-lg-12 text-end">
                                <input type="submit" value="{{__('Save Changes')}}" class="btn btn-print-invoice  btn-primary m-r-10">
                            </div>
                        </div>
                    </form>

                </div>

            </div>
            <div id="change_password" class="card">
                <div class="card-header">
                    <h5>{{('Change Password')}}</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{{route('update.password')}}">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 form-group">
                                <label for="old_password" class="col-form-label text-dark">{{ __('Old Password') }}</label>
                                <input class="form-control @error('old_password') is-invalid @enderror" name="old_password" type="password" id="old_password" required autocomplete="old_password" placeholder="{{ __('Enter Old Password') }}">
                                @error('old_password')
                                <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-6 col-sm-6 form-group">
                                <label for="password" class="col-form-label text-dark">{{ __('Password') }}</label>
                                <input class="form-control @error('password') is-invalid @enderror" name="password" type="New password" required autocomplete="new-password" id="password" placeholder="{{ __('Enter Your Password') }}">
                                @error('password')
                                <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-6 col-sm-6 form-group">
                                <label for="password_confirmation" class="col-form-label text-dark">{{ __('New Confirm Password') }}</label>
                                <input class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" type="password" required autocomplete="new-password" id="password_confirmation" placeholder="{{ __('Enter Your Password') }}">
                            </div>
                            <div class="col-lg-12 text-end">
                                <input type="submit" value="{{__('Change Password')}}" class="btn btn-print-invoice  btn-primary m-r-10">
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
@endsection
