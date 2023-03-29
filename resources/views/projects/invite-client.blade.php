
<div class="modal-body">
    <div class="row">
        @if(count($users) > 0)
            @foreach($users as $user)
                <div class="col-6 mb-4">
                    <div class="list-group-item px-0">
                        <div class="row ">
                            <div class="col-auto">
                                <img @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset(Storage::url($user->name . ".png"))}}" @endif class="wid-40 rounded-circle ml-3" alt="avatar image">
                            </div>
                            <div class="col">
                                <h6 class="mb-0">{{ $user->name }}</h6>
                                <p class="mb-0"><span class="text-success">{{ $user->email }}</p>
                            </div>
                            <div class="col-auto">
                                <div class="action-btn bg-info ms-2 invite_client" data-id="{{ $user->id }}">
                                    <button type="button" class="mx-3 btn btn-sm  align-items-center">
                                        <span class="btn-inner--visible">
                                        <i class="ti ti-plus text-white" id="usr_icon_{{$user->id}}"></i>
                                        </span>

                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12 text-center">
                <h5>{{__('No User Exist')}}</h5>

            </div>
        @endif
        <p class="text-black">
            Users Don't exist?
            <a href="#" class="text-primary text-sm" data-size="lg" data-url="{{ route('projects.member_client.create', $project_id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Members Client')}}">
                {{ 'Add Members Client' }}
            </a>
        </p>
    </div>
    {{ Form::hidden('project_id', $project_id,['id'=>'project_id']) }}
</div>

