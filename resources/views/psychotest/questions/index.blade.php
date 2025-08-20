@extends('layouts.admin')
@section('page-title')
    {{__('Manage Psychotest Questions')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Psychotest Questions')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
            <a href="{{ route('psychotest-question.create') }}" data-bs-toggle="tooltip" title="{{__('Create')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{__('Order')}}</th>
                                    <th>{{__('Title')}}</th>
                                    <th>{{__('Type')}}</th>
                                    <th>{{__('Category')}}</th>
                                    <th>{{__('Points')}}</th>
                                    <th>{{__('Status')}}</th>
                                    <th>{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($questions as $question)
                                    <tr>
                                        <td>{{ $question->order }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="mb-0">{{ \Str::limit($question->title, 50) }}</h6>
                                                    <small class="text-muted">{{ \Str::limit($question->question, 80) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ \App\Models\PsychotestQuestion::$types[$question->type] }}</span>
                                        </td>
                                        <td>{{ $question->category ?: '-' }}</td>
                                        <td>{{ $question->points }}</td>
                                        <td>
                                            @if($question->is_active)
                                                <span class="badge bg-success">{{__('Active')}}</span>
                                            @else
                                                <span class="badge bg-danger">{{__('Inactive')}}</span>
                                            @endif
                                        </td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="{{ route('psychotest-question.show', $question->id) }}" 
                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('View')}}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>

                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route('psychotest-question.edit', $question->id) }}" 
                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>

                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="{{ route('psychotest-question.toggle-status', $question->id) }}" 
                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" 
                                                    title="{{ $question->is_active ? __('Deactivate') : __('Activate') }}">
                                                        <i class="ti ti-toggle-{{ $question->is_active ? 'right' : 'left' }} text-white"></i>
                                                    </a>
                                                </div>

                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['psychotest-question.destroy', $question->id],'id'=>'delete-form-'.$question->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                                                    data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" 
                                                    data-bs-toggle="tooltip" title="{{__('Delete')}}" 
                                                    data-confirm-yes="document.getElementById('delete-form-{{$question->id}}').submit();">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            </span>
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