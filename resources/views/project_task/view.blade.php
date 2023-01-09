<div class="modal-body task-id" id="{{$task->id}}">
    <div class="card">
        <div class="card-body">
            <h5> {{__('Task Detail')}}</h5>
            <div class="row  mt-4">
                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start">
                        <div class="ms-2">
                            <p class="text-muted text-sm mb-0">{{__('Estimated Hours')}}</p>
                            <h3 class="mb-0 text-success">{{ (!empty($task->estimated_hrs)) ? number_format($task->estimated_hrs) : '-' }}</h3>

                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 my-3 my-sm-0">
                    <div class="d-flex align-items-start">
                        <div class="ms-2">
                            <p class="text-muted text-sm mb-0">{{__('Milestone')}}</p>
                            <h3 class="mb-0 text-primary">{{ (!empty($task->milestone)) ? $task->milestone->title : '-' }}</h3>

                        </div>
                    </div>
                </div>
                @if($allow_progress == 'false')
                    <div class="col-md-4 col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="ms-2">
                                <p class="text-muted text-sm mb-0"> {{__('Task Progress')}}</p>
                                <h3 class="mb-0 text-danger"><b id="t_percentage">{{ $task->progress }}</b>%</h3>
                                <div class="progress mb-0">
                                    <div id="progress-result" class="tab-pane tab-example-result fade show active" role="tabpanel" aria-labelledby="progress-result-tab">
                                        <input type="range" class="task_progress custom-range" value="{{ $task->progress }}" id="task_progress" name="progress" data-url="{{ route('change.progress',[$task->project_id,$task->id]) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="row mt-4">
                <div class="col">
                    <p class="text-sm text-muted mb-2">{{ (!empty($task->description)) ? $task->description : '-' }}</p>
                </div>
            </div>


        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row mb-4 align-items-center">
                <div class="col-6">
                    <h5> {{__('Sub Task')}}</h5>
                </div>
                <div class="col-6 ">
                    @if(\Auth::user()->type != 'client')
                    <div class="float-end">
                        <a data-bs-toggle="collapse" href="#form-checklist" role="button" aria-expanded="false" aria-controls="form-checklist" data-bs-toggle="tooltip" title="{{__('Add item')}}" class="btn btn-sm btn-primary">
                            <i class="ti ti-plus"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            <div class="checklist" id="checklist">
                <form id="form-checklist" class="collapse pb-2" data-action="{{route('checklist.store',[$task->project_id,$task->id])}}">
                    <div class="card border shadow-none">
                        <div class="px-3 py-2 row align-items-center">
                            @csrf
                            <div class="col-10">
                                <input type="text" name="name" required class="form-control" placeholder="{{__('Sub Task Name')}}"/>
                            </div>
                            <div class="col-10">
                                <input type="text" name="description" required class="form-control" placeholder="{{__('Description')}}"/>
                            </div>
                            <div class="col-2 card-meta d-inline-flex align-items-center pb-5">
                                <button class="btn btn-sm btn-primary" type="button" id="checklist_submit" data-bs-toggle="tooltip" title="{{__('Create')}}">
                                    <i class="ti ti-check"></i>
                                </button>
                            </div>
                            <div class="col-10 mb-3">
                                <input type="url" name="link" required class="form-control" placeholder="{{__('https://example.com')}}"/>
                            </div>
                            <div class="col-10">
                                <select id="parent_id" name="parent_id" class="form-control" required>
                                    <option value="0">Parent</option>
                                    @foreach ($task->checklist as $childtask)
                                    <option value="{{ $childtask->id }}">{{ $childtask->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>

                @foreach($subtask as $category)
                    <div class="card border shadow-none checklist-member">
                        <div class="px-3 py-2 row align-items-center">
                            <div class="col-10">
                                <div class="form-check form-check-inline">
                                @if(\Auth::user()->type != 'client')
                                    <input type="checkbox" class="form-check-input" id="check-item-{{ $category->id }}" @if($category->status) checked @endif data-url="{{route('checklist.update',[$task->project_id,$category->id])}}">
                                @endif
                                    <label class="form-check-label h6 text-sm" for="check-item-{{ $category->id }}"><a href="{{ $category->link }}" target="_blank"> {{ $category->name }}</a></label> 
                                    @if(count($category->subtasks))
                                    <ul>
                                            @foreach($category->subtasks as $subtask)
                                                    <li class="form-check-label h6 text-sm"><a href="{{ $subtask->link }}" target="_blank"> {{ $subtask->name }}</a></li>
                                            @endforeach
                                    </ul>
                                    @endif
                                </div>
                            </div>
                            <!-- <div class="col-10">
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label h6 text-sm" for="check-item-{{ $category->id }}"><a href="{{ $category->link }}" target="_blank"> {{ $category->link }}</a></label> 
                                </div>
                            </div> -->
                            <div class="col-auto">
                            @if(\Auth::user()->type != 'client')
                                <div class="action-btn bg-danger ms-2">
                                    <a href="#" class="mx-3 btn btn-sm  align-items-center delete-checklist" data-url="{{ route('checklist.destroy',[$task->project_id,$category->id]) }}">
                                        <i data-bs-toggle="tooltip" title="{{__('Delete')}}" class="ti ti-trash text-white"></i>
                                    </a>
                                </div>
                            @endif
                            </div>
                            <div class="col-10">
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label h6 text-sm text-black" for="check-item-{{ $category->id }}"><a>{{ $category->description }}</a></label> 
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="card border shadow-none checklist-member">
                        <div class="px-3 py-2 row align-items-center">
                            <div class="col">
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label h6 text-sm" for="check-item-{{ $category->id }}">{{ $category->link }}</label>
                                </div>
                            </div>
                        </div>
                    </div> -->
                @endforeach
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row mb-4 align-items-center">
                <div class="col-6">
                    <h5> {{__('Link')}}</h5>
                </div>
                <div class="col-6 ">
                    <div class="float-end">
                        <a data-bs-toggle="collapse" href="#form-link" role="button" aria-expanded="false" aria-controls="form-link" data-bs-toggle="tooltip" title="{{__('Add item')}}" class="btn btn-sm btn-primary">
                            <i class="ti ti-plus"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="link" id="link">
                <form id="form-link" class="collapse pb-2" data-action="{{route('link.store',[$task->project_id,$task->id])}}">
                    <div class="card border shadow-none">
                        <div class="px-3 py-2 row align-items-center">
                            @csrf
                            <div class="col-10">
                                <input type="url" name="link" required class="form-control" placeholder="{{__('https://example.com')}}"/>
                            </div>
                            <div class="col-2 card-meta d-inline-flex align-items-center">
                                <button class="btn btn-sm btn-primary" type="button" id="link_submit" data-bs-toggle="tooltip" title="{{__('Create')}}">
                                    <i class="ti ti-check"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                @foreach($task->link as $link)
                    <div class="card border shadow-none link-member">
                        <div class="px-3 py-2 row align-items-center">
                            <div class="col">
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label h6 text-sm" for="check-item-{{ $link->id }}">
                                    <a id="tasklinks" href="{{ $link->link }}" target="_blank">{{ $link->link }}</a>

                                    </label>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="action-btn bg-danger ms-2">
                                    <a href="#" class="mx-3 btn btn-sm  align-items-center delete-link" data-url="{{ route('link.destroy',[$task->project_id,$link->id]) }}">
                                        <i data-bs-toggle="tooltip" title="{{__('Delete')}}" class="ti ti-trash text-white"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row mb-4 align-items-center">
                <div class="col-6">
                    <h5> {{__('Attachments')}}</h5>
                </div>
                <div class="col-6 ">
                    <div class="float-end">
                        <a data-bs-toggle="collapse" href="#add_file" role="button" aria-expanded="false" aria-controls="add_file" data-bs-toggle="tooltip" title="{{__('Add attachment')}}" class="btn btn-sm btn-primary">
                            <i class="ti ti-plus"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="attachments" id="attachments">
                <form id="add_file" class="collapse pb-2">
                    <div class="card border shadow-none">
                        <div class="px-3 py-2 row align-items-center">
                            @csrf
                            <div class="col-10">
                                <input type="file" accept=".pdf, .xls, .csv, .xlsx" name="task_attachment" id="task_attachment" required class="form-control"/>
                            </div>
                            <div class="col-2 card-meta d-inline-flex align-items-center">
                                <button class="btn btn-sm btn-primary" type="button" id="file_attachment_submit" data-action="{{ route('comment.store.file',[$task->project_id,$task->id]) }}" data-bs-toggle="tooltip" title="{{__('Create')}}">
                                    <i class="ti ti-check"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="comments-file">
                    @foreach($task->taskFiles as $file)
                        <div class="card mb-3 border shadow-none task-file">
                            <div class="px-3 py-3">
                                <div class="row align-items-center">

                                    <div class="col ml-n2">
                                        <h6 class="text-sm mb-0">
                                            <a href="#">{{ $file->name }}</a>
                                        </h6>
                                        <p class="card-text small text-muted">{{ $file->file_size }}</p>
                                    </div>
                                    <div class="col-auto actions">
                                        <div class="action-btn bg-secondary ms-2">
                                            <a href="{{asset(Storage::url('tasks/'.$file->file))}}" download class="action-item" role="button">
                                                <i class="ti ti-download text-white"></i>
                                            </a>
                                        </div>
                                        @auth('web')
                                            <div class="action-btn bg-danger ms-2">
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center delete-comment-file" data-url="{{ route('comment.destroy.file',[$task->project_id,$task->id,$file->id]) }}">
                                                    <i data-bs-toggle="tooltip" title="{{__('Delete')}}" class="ti ti-trash text-white"></i>
                                                </a>
                                            </div>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row mb-4 align-items-center">
                <div class="col-6">
                    <h5> {{__('Activity')}}</h5>
                </div>

            </div>
            <div class="activity" id="activity">
                @foreach($task->activity_log() as $activity)
                    @php $user = \App\Models\User::find($activity->user_id); @endphp

                    <div class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <a href="#" class="avatar avatar-sm ms-2">
                                    <img data-toggle="tooltip" data-original-title="{{(!empty($user)?$user->name:'')}}" @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif title="{{ $user->name }}" class="wid-40 rounded-circle ml-3">
                                </a>
                            </div>
                            <div class="col ml-n2">
                                <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                <a class="d-block h6 text-sm font-weight-light mb-0">{!! $activity->getRemark() !!}</a>
                                <small class="d-block">{{$activity->created_at->diffForHumans()}}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row mb-4 align-items-center">
                <div class="col-6">
                    <h5> {{__('Comments')}}</h5>
                </div>

            </div>
            <div class="activity" id="comments">
                @foreach($task->comments as $comment)
                    @php $user = \App\Models\User::find($comment->user_id); @endphp
                    <div class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <a href="#" class="avatar avatar-sm rounded-circle ms-2">
                                    <img data-original-title="{{(!empty($user)?$user->name:'')}}" @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif title="{{ $comment->user->name }}" class="wid-40 rounded-circle ml-3">
                                </a>
                            </div>
                            <div class="col-auto">
                                {{ $user->name }}
                            </div>
                            <div class="col ml-n2">
                                <p class="d-block h6 text-sm font-weight-light mb-0 text-break">{{ $comment->comment }}</p>
                                <small class="d-block">{{$comment->created_at->diffForHumans()}}</small>
                            </div>
                            <div class="col-auto">
                                <div class="action-btn bg-danger me-2">
                                    <a href="#" class="mx-3 btn btn-sm  align-items-center delete-comment" data-url="{{ route('comment.destroy',[$task->project_id,$task->id,$comment->id]) }}">
                                        <i data-bs-toggle="tooltip" title="{{__('Delete')}}" class="ti ti-trash text-white"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer">

            <div class="col-12 d-flex">
                <div class="avatar me-3">
                    <img data-original-title="{{(!empty(\Auth::user()) ? \Auth::user()->name:'')}}" @if(\Auth::user()->avatar) src="{{asset('/storage/uploads/avatar/'.\Auth::user()->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif title="{{ Auth::user()->name }}" class="wid-40 rounded-circle ml-3">
                </div>
                <div class="form-group mb-0 form-send w-100">
                    <form method="post" class="card-comment-box" id="form-comment" data-action="{{route('comment.store',[$task->project_id,$task->id])}}" onkey="javascript:load_data(this.value)">
                        <textarea rows="1" class="form-control" name="comment" data-toggle="autosize" placeholder="{{__('Add a comment...')}}"></textarea>
                    </form>
                </div>

                <button id="comment_submit" class="btn btn-send"><i class="f-16 text-primary ti ti-brand-telegram"></i></button>

            </div>
        </div>
    </div>
</div>
@push('script-page')
    <script>
        $(document).ready(function () {
            $(".colorPickSelector").colorPick({
                'onColorSelected': function () {
                    var task_id = this.element.parents('.side-modal').attr('id');
                    var color = this.color;

                    if (task_id) {
                        this.element.css({'backgroundColor': color});
                        $.ajax({
                            url: '{{ route('update.task.priority.color') }}',
                            method: 'PATCH',
                            data: {
                                'task_id': task_id,
                                'color': color,
                            },
                            success: function (data) {
                                $('.task-list-items').find('#' + task_id).attr('style', 'border-left:2px solid ' + color + ' !important');
                            }
                        });
                    }
                }
            });
        });
        

        // function load_data(query)
        // {
        //     if(query.length > 2)
        //     {
        //         var form_data = new FormData();

        //         form_data.append('query', query);

        //         var ajax_request = new XMLHttpRequest();

        //         ajax_request.open('POST', );

        //         ajax_request.send(form_data);

        //         ajax_request.onreadystatechange = function()
        //         {
        //             if(ajax_request.readyState == 4 && ajax_request.status == 200)
        //             {
        //                 var response = JSON.parse(ajax_request.responseText);

        //                 var html = '<div class ="list-group">';

        //                 if(response.length > 0)
        //                 {
        //                     for(var count = 0; count < response.length; count++)
        //                     {
        //                         html += '<a href="#" class="list-group-item list-group-item-action">'+response[count].users+'</a>';
        //                     }
        //                 }
        //                 else
        //                 {
        //                     html += '<a href="#" class="list-group-item list-group-item-action disabled"> No Data Found</a>'
        //                 }

        //                 html += '</div>'
        //                 document.getElementById('search_result').innerHTML = html;
        //             }
        //         }
        //     }
        //     else
        //     {
        //         document.getElementById('textarea').innerHTML = '';
        //     }
        // }
    </script>
@endpush
