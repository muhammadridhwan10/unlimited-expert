
<div class="modal-body">
    <div class="row">
        @if(count($users) > 0)
            @foreach($users as $user)
                <div class="col-12 col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <!-- Avatar -->
                            <div class="me-3">
                                <img 
                                    src="{{ $user->avatar ? asset('/storage/uploads/avatar/' . $user->avatar) : asset('assets/images/user/avatar-4.jpg') }}" 
                                    class="rounded-circle" 
                                    style="width: 50px; height: 50px; object-fit: cover;" 
                                    alt="Avatar"
                                >
                            </div>

                            <!-- User Details -->
                            <div class="flex-grow-1">
                                <h5 class="mb-0" style="font-size:13px">{{ $user->name }}</h5>
                                <p class="mb-0 text-muted" style="font-size:10px">{{ $user->email }}</p>
                            </div>

                            <!-- Action Button -->
                            <div>
                                <button 
                                    type="button" 
                                    class="btn btn-primary btn-sm invite_usr" 
                                    data-id="{{ $user->id }}"
                                >
                                    <i class="ti ti-plus"></i> Invite
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12 text-center">
                <h5 class="text-muted">{{ __('No Active Users Available') }}</h5>
            </div>
        @endif
    </div>
    {{ Form::hidden('project_id', $project_id, ['id' => 'project_id']) }}
</div>