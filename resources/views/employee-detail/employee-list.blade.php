@if ($users->isEmpty())
    <div class="col-12 text-center">
        <p>No employees found.</p>
    </div>
@else
    @foreach ($users as $user)
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body full-card">
                    <div class="card-avatar">
                        <img src="{{ (!empty($user->avatar)) ? asset(Storage::url('uploads/avatar/' . $user->avatar)) : asset(Storage::url('uploads/avatar/avatar.png')) }}" 
                             class="wid-80" style="width: 72px; height: 72px; object-fit: cover; object-position: center; border-radius: 50%;">
                    </div>
                    <h4 class="mt-2 text-primary">{{ $user->name }}</h4>
                    <small class="text-primary">{{ $user->email }}</small>
                    <div class="align-items-center h6 mt-2" data-bs-toggle="tooltip" title="{{ __('Detail User') }}">
                        <a href="{{ route('employee-reports.show', $user->id) }}" class="btn btn-primary" data-bs-original-title="{{ __('View') }}">
                            <span>{{ __('Detail') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

<div class="d-flex justify-content-center mt-4">
    {{ $users->links() }}
</div>