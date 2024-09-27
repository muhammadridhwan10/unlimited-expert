@extends('layouts.admin')

@section('page-title')
    {{__('Manage Personel Assessment')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Manage Personel Assessment')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('form-response.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Create')}}">
            <i class="ti ti-plus"></i>
         </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners')
                                        <th>{{__('Employee')}}</th>
                                    @endif
                                    <th>{{__('Title')}}</th>
                                    <th>{{__('Year')}}</th>
                                    <th width="200px">{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assessment as $assessments)
                                    <tr>
                                        @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners')
                                            <td>{{!empty($assessments->user->name)?$assessments->user->name:'-'}}</td>
                                        @endif
                                        <td>{{'Self Assessment ' . $assessments->year}}</td>
                                        <td>{{!empty($assessments->year)?$assessments->year:'-'}}</td>
                                        <td>
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="{{ URL::to('form-response/'. $assessments->user_id . '/' . $assessments->year) }}" title="{{__('Assessment Action')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Assessment Action')}}" data-original-title="{{__('Assessment Action')}}">
                                                <i class="ti ti-caret-right text-white"></i> 
                                            </a>
                                        </div>
                                        <div class="action-btn bg-success ms-2">
                                            {{-- <a href="{{ route('form-response.assessment',[$assessments->user_id,$assessments->year]) }}" title="{{__('Show Final Assessment')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Show Final Assessment')}}" data-original-title="{{__('Show Final Assessment')}}">
                                                <i class="ti ti-star text-white"></i> 
                                            </a> --}}
                                            <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg" data-url="{{ route('form-response.assessment',[$assessments->user_id,$assessments->year]) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Show Final Assessment')}}" data-title="{{__('Show Final Assessment')}}"><i class="ti ti-star text-white"></i></a>
                                        </div>
                                        
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
            <div class="card-header"><h6 class="mb-0">{{__('Appraiser Assessment')}}</h6></div>
            <div class="card-body table-border-style">
                    <div class="table-responsive">
                    <table class="table datatabless">
                            <thead>
                                <tr>
                                    <th>{{__('Employee')}}</th>
                                    <th>{{__('Year')}}</th>
                                    <th width="200px">{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($approvalAsAppraisal as $approvalappraisal)
                                <tr>
                                    <td>{{!empty($approvalappraisal->user->name)?$approvalappraisal->user->name:'-'}}</td>
                                    <td>{{!empty($approvalappraisal->year)?$approvalappraisal->year:'-'}}</td>
                                    <td>
                                        <div class="action-btn bg-warning ms-2">
                                        <a href="{{ URL::to('form-response/'. $approvalappraisal->user_id . '/' . $approvalappraisal->year) }}" title="{{__('Assessment Action')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Assessment Action')}}" data-original-title="{{__('Assessment Action')}}">
                                            <i class="ti ti-caret-right text-white"></i> 
                                        </a>
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

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
            <div class="card-header"><h6 class="mb-0">{{__('A2 Assessment')}}</h6></div>
            <div class="card-body table-border-style">
                    <div class="table-responsive">
                    <table class="table datatablesss">
                            <thead>
                                <tr>
                                    <th>{{__('Employee')}}</th>
                                    <th>{{__('Year')}}</th>
                                    <th width="200px">{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($approvalAsSupervisor as $approvalsupervisor)
                                <tr>
                                    <td>{{!empty($approvalsupervisor->user->name)?$approvalsupervisor->user->name:'-'}}</td>
                                    <td>{{!empty($approvalsupervisor->year)?$approvalsupervisor->year:'-'}}</td>
                                    <td>
                                        <div class="action-btn bg-warning ms-2">
                                        <a href="{{ URL::to('form-response/'. $approvalsupervisor->user_id . '/' . $approvalsupervisor->year) }}" title="{{__('Assessment Action')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Assessment Action')}}" data-original-title="{{__('Assessment Action')}}">
                                            <i class="ti ti-caret-right text-white"></i> 
                                        </a>
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

@endsection
