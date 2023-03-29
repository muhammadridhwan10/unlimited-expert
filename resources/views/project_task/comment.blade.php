<div class="modal-body task-id" id="{{$task->id}}">
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
