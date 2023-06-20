<div class="modal-body task-id" id="{{$task->id}}">
    <div class="card">
        <div class="card-body">
            <h5> {{__('Task Detail')}}</h5>
            <div class="row  mt-4">
                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start">
                        <div class="ms-2">
                            <p class="text-muted text-sm mb-0">{{__('Estimated Hours')}}</p>
                            <h3 class="mb-0 text-success">{{ (!empty($task->estimated_hrs)) ? $task->estimated_hrs : '-' }}</h3>

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
                    <h5> {{__('Sub Task Prosedur Analitis')}}</h5>
                </div>
            </div>
            <div class="checklist" id="checklist">

                @foreach($subtask as $category)
                    <div class="card border shadow-none checklist-member">
                        <div class="px-3 py-2 row align-items-center">
                            <div class="col-10">
                                <div class="form-check form-check-inline">
                                @if(\Auth::user()->type != 'client')
                                    <input type="checkbox" class="form-check-input" id="check-item-{{ $category->id }}" @if($category->status) checked @endif data-url="{{route('checklist.update',[$task->project_id,$category->id])}}">
                                @endif
                                    <?php
                                        if($category->name == 'Perbandingan Data Antar Periode')
                                        {
                                            $link = route('projects.tasks.keuangan.ringkas',[$task->project_id,\Crypt::encrypt($task->id)]);
                                        }
                                        elseif($category->name == 'Rasio Keuangan')
                                        {
                                            $link = route('projects.tasks.rasio.keuangan',[$task->project_id,\Crypt::encrypt($task->id)]);
                                        }
                                        elseif($category->name == 'Audit Memorandum')
                                        {
                                            $link = route('projects.tasks.audit.memorandum',[$task->project_id,\Crypt::encrypt($task->id)]);
                                        }
                                    ?>
                                    <label class="form-check-label h6 text-sm" for="check-item-{{ $category->id }}"><a href="{{ $link }}" target="_blank"> {{ $category->name }}</a></label> 
                                    @if(count($category->subtasks))
                                    <ul>
                                            @foreach($category->subtasks as $subtask)
                                                    <li class="form-check-label h6 text-sm">
                                                        <a href="{{ $subtask->link }}" target="_blank"> {{ $subtask->name }}</a>
                                                        <div class="action-btn h6">
                                                            <a href="#" data-size="lg" class="mx-3 btn btn-sm  align-items-center text-info" data-ajax-popup="true" data-url="{{ route('subtask.edit',[$task->project_id, $subtask->id]) }}" data-bs-original-title="{{__('Edit ').$category->name}}">
                                                                {{__('Edit')}}
                                                            </a>
                                                        </div>
                                                    </li>
                                            @endforeach
                                    </ul>
                                    @endif
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
                                    <img data-original-title="{{(!empty($user)?$user->name:'')}}" @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset(Storage::url($user->name . ".png"))}}" @endif title="{{ $comment->user->name }}" class="wid-40 rounded-circle ml-3">
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
                    <img data-original-title="{{(!empty(\Auth::user()) ? \Auth::user()->name:'')}}" @if(\Auth::user()->avatar) src="{{asset('/storage/uploads/avatar/'.\Auth::user()->avatar)}}" @else src="{{asset(Storage::url(\Auth::user()->name . ".png"))}}" @endif title="{{ Auth::user()->name }}" class="wid-40 rounded-circle ml-3">
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
