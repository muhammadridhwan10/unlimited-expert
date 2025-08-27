@extends('layouts.admin')
@section('page-title')
    {{__('Category Details')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('psychotest-category.index')}}">{{__('Test Categories')}}</a></li>
    <li class="breadcrumb-item">{{__('Details')}}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Category Details -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Category Information')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>{{__('Name')}}:</strong></td>
                                    <td>{{ $category->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Code')}}:</strong></td>
                                    <td><code class="bg-light p-1 rounded">{{ $category->code }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Type')}}:</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ \App\Models\PsychotestCategory::$types[$category->type] ?? $category->type }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Job Specific')}}:</strong></td>
                                    <td>
                                        @if($category->is_job_specific)
                                            <span class="badge bg-warning">{{__('Yes')}}</span>
                                        @else
                                            <span class="badge bg-light text-dark">{{__('No')}}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>{{__('Status')}}:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $category->is_active ? 'success' : 'danger' }} p-2">
                                            {{ $category->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Duration')}}:</strong></td>
                                    <td>
                                        <span class="badge bg-primary">{{ $category->duration_minutes }} minutes</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Questions')}}:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $category->questions->count() >= $category->total_questions ? 'success' : 'warning' }}">
                                            {{ $category->questions->count() }}/{{ $category->total_questions }}
                                        </span>
                                        @if($category->questions->count() < $category->total_questions)
                                            <small class="text-muted">({{ $category->total_questions - $category->questions->count() }} more needed)</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{__('Order')}}:</strong></td>
                                    <td><span class="badge bg-secondary">{{ $category->order }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($category->description)
                        <div class="mt-3">
                            <h6>{{__('Description')}}</h6>
                            <div class="p-3 bg-light rounded">
                                <p class="mb-0">{!! nl2br(e($category->description)) !!}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Job Specific Settings -->
            @if($category->is_job_specific)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>{{__('Job Specific Settings')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>{{__('Target Job Keywords')}}</h6>
                            @if($category->target_job_keywords && is_array($category->target_job_keywords))
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($category->target_job_keywords as $keyword)
                                        <span class="badge bg-primary">{{ $keyword }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">{{__('No keywords specified')}}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Category Settings -->
            @if($category->settings && is_array($category->settings) && count($category->settings) > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>{{__('Category Settings')}}</h5>
                    </div>
                    <div class="card-body">
                        @if($category->type === 'kraeplin')
                            <div class="alert alert-info">
                                <h6><i class="ti ti-settings me-2"></i>{{__('Kraeplin Test Configuration')}}</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>{{__('Columns')}}:</strong> {{ $category->getKraeplinColumns() }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>{{__('Time per Column')}}:</strong> {{ $category->getKraeplinTimePerColumn() }}s
                                    </div>
                                    <div class="col-md-4">
                                        <strong>{{__('Show Instructions')}}:</strong> 
                                        {{ $category->getSetting('show_instructions', true) ? __('Yes') : __('No') }}
                                    </div>
                                </div>
                            </div>
                        @elseif($category->type === 'field_specific')
                            <div class="alert alert-warning">
                                <h6><i class="ti ti-briefcase me-2"></i>{{__('Field Specific Configuration')}}</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>{{__('Topics')}}:</strong>
                                        @if(isset($category->settings['topics']))
                                            @foreach($category->settings['topics'] as $topic)
                                                <span class="badge bg-warning me-1">{{ str_replace('_', ' ', ucfirst($topic)) }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">General</span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{__('Passing Score')}}:</strong> {{ $category->getSetting('passing_score', 70) }}%
                                    </div>
                                </div>
                            </div>
                        @elseif($category->type === 'personality')
                            <div class="alert alert-success">
                                <h6><i class="ti ti-user me-2"></i>{{__('Personality Test Configuration')}}</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>{{__('Scoring Method')}}:</strong> 
                                        {{ ucfirst(str_replace('_', ' ', $category->getEPPSScoringMethod())) }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{__('Show Progress')}}:</strong> 
                                        {{ $category->shouldShowProgress() ? __('Yes') : __('No') }}
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <strong>{{__('Personality Dimensions')}}:</strong><br>
                                    <div class="row mt-2">
                                        @foreach($category->getEPPSDimensions() as $dimension)
                                            <div class="col-md-4 mb-1">
                                                <span class="badge bg-success me-1">{{ ucfirst($dimension) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-light">
                                <h6><i class="ti ti-info-circle me-2"></i>{{__('General Settings')}}</h6>
                                <div class="row">
                                    @foreach($category->settings as $key => $value)
                                        <div class="col-md-6 mb-2">
                                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                            @if(is_bool($value))
                                                {{ $value ? __('Yes') : __('No') }}
                                            @elseif(is_array($value))
                                                {{ implode(', ', $value) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Questions in Category -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{__('Questions in Category')}}</h5>
                    <a href="{{ route('psychotest-question.create', ['category' => $category->id]) }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-plus me-1"></i>{{__('Add Question')}}
                    </a>
                </div>
                <div class="card-body">
                    @if($category->questions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{__('Order')}}</th>
                                        <th>{{__('Title')}}</th>
                                        <th>{{__('Type')}}</th>
                                        <th>{{__('Points')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category->questions()->orderBy('order')->get() as $question)
                                        <tr>
                                            <td><span class="badge bg-secondary">{{ $question->order }}</span></td>
                                            <td>
                                                <div>
                                                    <strong>{{ Str::limit($question->title, 40) }}</strong>
                                                    @if($question->question)
                                                        <br><small class="text-muted">{{ Str::limit($question->question, 60) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ \App\Models\PsychotestQuestion::$types[$question->type] ?? $question->type }}</span>
                                            </td>
                                            <td>{{ $question->points }}</td>
                                            <td>
                                                <span class="badge bg-{{ $question->is_active ? 'success' : 'danger' }}">
                                                    {{ $question->is_active ? __('Active') : __('Inactive') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('psychotest-question.show', $question->id) }}" class="btn btn-sm btn-outline-primary" title="{{__('View')}}">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <a href="{{ route('psychotest-question.edit', $question->id) }}" class="btn btn-sm btn-outline-info" title="{{__('Edit')}}">
                                                        <i class="ti ti-pencil"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-question-mark display-4 text-muted"></i>
                            <h6 class="mt-2">{{__('No Questions Yet')}}</h6>
                            <p class="text-muted">{{__('This category needs')}} {{ $category->total_questions }} {{__('questions to be complete')}}</p>
                            <a href="{{ route('psychotest-question.create', ['category' => $category->id]) }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>{{__('Add First Question')}}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Usage Statistics -->
            @if($category->sessions()->exists())
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>{{__('Usage Statistics')}}</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $totalSessions = $category->sessions()->count();
                            $completedSessions = $category->sessions()->where('status', 'completed')->count();
                            $inProgressSessions = $category->sessions()->where('status', 'in_progress')->count();
                            $successRate = $completedSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0;
                        @endphp
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h3>{{ $totalSessions }}</h3>
                                        <small>{{__('Total Sessions')}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h3>{{ $completedSessions }}</h3>
                                        <small>{{__('Completed')}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h3>{{ $inProgressSessions }}</h3>
                                        <small>{{__('In Progress')}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3>{{ $successRate }}%</h3>
                                        <small>{{__('Completion Rate')}}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Actions Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Actions')}}</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('psychotest-category.edit', $category->id) }}" class="btn btn-info btn-sm w-100 mb-2">
                        <i class="ti ti-pencil"></i> {{__('Edit Category')}}
                    </a>

                    <a href="{{ route('psychotest-question.create', ['category' => $category->id]) }}" class="btn btn-success btn-sm w-100 mb-2">
                        <i class="ti ti-plus"></i> {{__('Add Question')}}
                    </a>

                    <a href="{{ route('psychotest-question.index', ['category_id' => $category->id]) }}" class="btn btn-primary btn-sm w-100 mb-2">
                        <i class="ti ti-list"></i> {{__('View All Questions')}}
                    </a>

                    <a href="{{ route('psychotest-category.toggle-status', $category->id) }}" 
                       class="btn btn-{{ $category->is_active ? 'warning' : 'success' }} btn-sm w-100 mb-2">
                        <i class="ti ti-toggle-{{ $category->is_active ? 'right' : 'left' }}"></i> 
                        {{ $category->is_active ? __('Deactivate') : __('Activate') }}
                    </a>

                    @if(!$category->sessions()->exists())
                        {!! Form::open(['method' => 'DELETE', 'route' => ['psychotest-category.destroy', $category->id],'id'=>'delete-form-'.$category->id]) !!}
                        <a href="#" class="btn btn-danger btn-sm w-100 bs-pass-para" 
                           data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" 
                           data-confirm-yes="document.getElementById('delete-form-{{$category->id}}').submit();">
                            <i class="ti ti-trash"></i> {{__('Delete')}}
                        </a>
                        {!! Form::close() !!}
                    @else
                        <button class="btn btn-danger btn-sm w-100" disabled>
                            <i class="ti ti-lock"></i> {{__('Cannot Delete')}}
                        </button>
                        <small class="text-muted">{{__('Category is used in tests')}}</small>
                    @endif
                </div>
            </div>

            <!-- Category Metadata -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>{{__('Category Metadata')}}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>{{__('Created By')}}:</strong><br>
                        <small class="text-muted">{{ $category->creator->name ?? 'N/A' }}</small>
                    </div>

                    <div class="mb-3">
                        <strong>{{__('Created At')}}:</strong><br>
                        <small class="text-muted">{{ $category->created_at ? $category->created_at->format('d M Y H:i:s') : 'N/A' }}</small>
                    </div>

                    @if($category->updated_at && $category->updated_at != $category->created_at)
                        <div class="mb-3">
                            <strong>{{__('Last Updated')}}:</strong><br>
                            <small class="text-muted">{{ $category->updated_at->format('d M Y H:i:s') }}</small>
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong>{{__('Category ID')}}:</strong><br>
                        <small class="text-muted">#{{ $category->id }}</small>
                    </div>

                    <div class="mb-3">
                        <strong>{{__('Completion Status')}}:</strong><br>
                        @php
                            $completionPercentage = $category->total_questions > 0 ? round(($category->questions->count() / $category->total_questions) * 100, 1) : 0;
                        @endphp
                        <div class="progress mb-2">
                            <div class="progress-bar bg-{{ $completionPercentage >= 100 ? 'success' : ($completionPercentage >= 50 ? 'warning' : 'danger') }}" 
                                 style="width: {{ $completionPercentage }}%">
                                {{ $completionPercentage }}%
                            </div>
                        </div>
                        <small class="text-muted">{{ $category->questions->count() }} of {{ $category->total_questions }} questions</small>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>{{__('Quick Statistics')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4>{{ $category->questions()->where('is_active', true)->count() }}</h4>
                                <small class="text-muted">{{__('Active Questions')}}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4>{{ $category->questions()->where('is_active', false)->count() }}</h4>
                            <small class="text-muted">{{__('Inactive Questions')}}</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4>{{ $category->sessions()->count() }}</h4>
                                <small class="text-muted">{{__('Total Tests')}}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4>{{ $category->sessions()->where('status', 'completed')->count() }}</h4>
                            <small class="text-muted">{{__('Completed')}}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection