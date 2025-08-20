@extends('layouts.admin')
@section('page-title')
    {{__('Manage Test Categories')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Test Categories')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
            <a href="{{ route('psychotest-category.seed-defaults') }}" data-bs-toggle="tooltip" title="{{__('Create Default Categories')}}" class="btn btn-sm btn-info">
                <i class="ti ti-database-import"></i> {{__('Seed Defaults')}}
            </a>
            <a href="{{ route('psychotest-category.create') }}" data-bs-toggle="tooltip" title="{{__('Create')}}" class="btn btn-sm btn-primary">
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
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('Type')}}</th>
                                    <th>{{__('Duration')}}</th>
                                    <th>{{__('Questions')}}</th>
                                    <th>{{__('Status')}}</th>
                                    <th>{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">{{ $category->order }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <h6 class="mb-0">{{ $category->name }}</h6>
                                                <small class="text-muted">{{ $category->code }}</small>
                                                @if($category->description)
                                                    <br><small class="text-muted">{{ \Str::limit($category->description, 60) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        {{-- <td>
                                            <span class="badge bg-info">{{ \App\Models\PsychotestCategory::$types[$category->type] }}</span>
                                        </td> --}}
                                        <td>{{ $category->duration_minutes }} {{__('min')}}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $category->questions->count() }}/{{ $category->total_questions }}</span>
                                                @if($category->questions->count() < $category->total_questions)
                                                    <span class="badge bg-warning">{{__('Incomplete')}}</span>
                                                @else
                                                    <span class="badge bg-success">{{__('Complete')}}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($category->is_active)
                                                <span class="badge bg-success">{{__('Active')}}</span>
                                            @else
                                                <span class="badge bg-danger">{{__('Inactive')}}</span>
                                            @endif
                                        </td>
                                        <td class="Action">
                                            <span>
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="{{ route('psychotest-category.show', $category->id) }}" 
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('View')}}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>

                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="{{ route('psychotest-category.edit', $category->id) }}" 
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>

                                                    <div class="action-btn bg-warning ms-2">
                                                        <a href="{{ route('psychotest-category.toggle-status', $category->id) }}" 
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" 
                                                        title="{{ $category->is_active ? __('Deactivate') : __('Activate') }}">
                                                            <i class="ti ti-toggle-{{ $category->is_active ? 'right' : 'left' }} text-white"></i>
                                                        </a>
                                                    </div>

                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="{{ route('psychotest-question.create', ['category' => $category->id]) }}" 
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Add Questions')}}">
                                                            <i class="ti ti-plus text-white"></i>
                                                        </a>
                                                    </div>

                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['psychotest-category.destroy', $category->id],'id'=>'delete-form-'.$category->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                                                        data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" 
                                                        data-bs-toggle="tooltip" title="{{__('Delete')}}" 
                                                        data-confirm-yes="document.getElementById('delete-form-{{$category->id}}').submit();">
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