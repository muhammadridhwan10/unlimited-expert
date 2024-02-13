@foreach($project->users as $user)
    @if($user->type !== "staff_client")
        <li class="list-group-item px-0">
            <div class="row align-items-center justify-content-between">
                <div class="col-sm-auto mb-3 mb-sm-0">
                    <div class="d-flex align-items-center">
                        <div class="avatar rounded-circle avatar-sm me-3">
                            <img @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset(Storage::url($user->name . ".jpg"))}}"  @endif  alt="image" >

                        </div>
                        <div class="div">
                            <h5 class="m-0">{{ $user->name }}  <span class="badge rounded-pill bg-info">{{ $user->type }}</span></h5>
                            <small class="text-muted">{{ $user->email }}</small>
                            @php
                                    $hasRatingData = array_key_exists($user->employee->id, $overallRatings);
                                    $rating = $hasRatingData ? $overallRatings[$user->employee->id] : 0; 
                            @endphp
                            <h5 class="text-muted">
                                @for($i=1; $i<=5; $i++)
                                            @if($rating < $i)
                                                @if(is_float($rating) && (round($rating) == $i))
                                                    <i class="text-warning fas fa-star-half-alt"></i>
                                                @else
                                                    <i class="fas fa-star"></i>
                                                @endif
                                            @else
                                                <i class="text-warning fas fa-star"></i>
                                            @endif
                                        @endfor
                                        <span class="theme-text-color">({{number_format($rating,1)}})</span>
                            </h5>
                        </div>
                    </div>
                </div>
                @if(Auth::user()->type !== "client" && Auth::user()->type !== "staff_client")
                    <div class="col-sm-auto text-sm-end d-flex align-items-center">
                        <div class="action-btn bg-danger ms-2">
                            {!! Form::open(['method' => 'DELETE', 'route' => ['projects.user.destroy',  [$project->id,$user->id]]]) !!}
                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                            {!! Form::close() !!}
                        </div>
                        @php
                            // Check if the user ID exists in the $reviewedUsers array
                            $hasAppraisalData = in_array($user->employee->id, $reviewedUsers);
                        @endphp
                       @if($project->status == "complete" && !$hasAppraisalData)
                                @if(Auth::user()->type == "company" || Auth::user()->type == "admin" || Auth::user()->type == "partners")
                                    <!-- Allow review for all user types except "company", "admin", and "partners" -->
                                    @if(Auth::user()->id !== $user->id) <!-- Make sure not reviewing self -->
                                        <div class="action-btn bg-success ms-2">
                                            <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg" data-url="{{ route('appraisal.create', [$user->id,$project->id]) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Review')}}" data-title="{{__('Review')}}"><i class="ti ti-star text-white"></i></a>
                                        </div>
                                    @endif
                                @elseif(Auth::user()->type == "senior audit")
                                    <!-- Allow review for "intern" and "junior audit" user types -->
                                    @if($user->type == "intern" || $user->type == "junior audit")
                                        @if(Auth::user()->id !== $user->id) <!-- Make sure not reviewing self -->
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg" data-url="{{ route('appraisal.create', [$user->id,$project->id]) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Review')}}" data-title="{{__('Review')}}"><i class="ti ti-star text-white"></i></a>
                                            </div>
                                        @endif
                                    @endif
                                @elseif(Auth::user()->type == "junior audit")
                                    <!-- Allow review for "intern" user type -->
                                    @if($user->type == "intern")
                                        @if(Auth::user()->id !== $user->id) <!-- Make sure not reviewing self -->
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg" data-url="{{ route('appraisal.create', [$user->id,$project->id]) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Review')}}" data-title="{{__('Review')}}"><i class="ti ti-star text-white"></i></a>
                                            </div>
                                        @endif
                                    @endif
                                @elseif(Auth::user()->type == "senior accounting")
                                    <!-- Allow review for "intern" and "junior audit" user types -->
                                    @if($user->type == "intern" || $user->type == "junior accounting")
                                        @if(Auth::user()->id !== $user->id) <!-- Make sure not reviewing self -->
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg" data-url="{{ route('appraisal.create', [$user->id,$project->id]) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Review')}}" data-title="{{__('Review')}}"><i class="ti ti-star text-white"></i></a>
                                            </div>
                                        @endif
                                    @endif
                                @elseif(Auth::user()->type == "junior accounting")
                                    <!-- Allow review for "intern" user type -->
                                    @if($user->type == "intern")
                                        @if(Auth::user()->id !== $user->id) <!-- Make sure not reviewing self -->
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg" data-url="{{ route('appraisal.create', [$user->id,$project->id]) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Review')}}" data-title="{{__('Review')}}"><i class="ti ti-star text-white"></i></a>
                                            </div>
                                        @endif
                                    @endif
                                @endif
                        @endif
                    </div>
                @endif
            </div>
        </li>
    @endif
@endforeach
