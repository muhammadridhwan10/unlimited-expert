@extends('layouts.admin')
@section('page-title')
    {{ucwords($project->project_name).__("'s Tasks")}}
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
@endpush
@push('script-page')
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
    <script>
        !function (a) {
            "use strict";
            var t = function () {
                this.$body = a("body")
            };
            t.prototype.init = function () {
                a('[data-plugin="dragula"]').each(function () {
                    var t = a(this).data("containers"), n = [];
                    if (t) for (var i = 0; i < t.length; i++) n.push(a("#" + t[i])[0]); else n = [a(this)[0]];
                    var r = a(this).data("handleclass");
                    r ? dragula(n, {
                        moves: function (a, t, n) {
                            return n.classList.contains(r)
                        }
                    }) : dragula(n).on('drop', function (el, target, source, sibling) {
                        var sort = [];
                        $("#" + target.id + " > div").each(function () {
                            sort[$(this).index()] = $(this).attr('id');
                        });

                        var id = el.id;
                        var old_stage = $("#" + source.id).data('status');
                        var new_stage = $("#" + target.id).data('status');
                        var project_id = '{{$project->id}}';

                        $("#" + source.id).parent().find('.count').text($("#" + source.id + " > div").length);
                        $("#" + target.id).parent().find('.count').text($("#" + target.id + " > div").length);
                        $.ajax({
                            url: '{{route('tasks.update.order',[$project->id])}}',
                            type: 'PATCH',
                            data: {id: id, sort: sort, new_stage: new_stage, old_stage: old_stage, project_id: project_id, "_token": "{{ csrf_token() }}"},
                            success: function (data) {
                            }
                        });
                    });
                })
            }, a.Dragula = new t, a.Dragula.Constructor = t
        }(window.jQuery), function (a) {
            "use strict";
            a.Dragula.init()
        }(window.jQuery);

        $(document).ready(function () {
            /*Set assign_to Value*/
            $(document).on('click', '.add_usr', function () {
                var ids = [];
                $(this).toggleClass('selected');
                var crr_id = $(this).attr('data-id');
                $('#usr_txt_' + crr_id).html($('#usr_txt_' + crr_id).html() == 'Add' ? '{{__('Added')}}' : '{{__('Add')}}');
                if ($('#usr_icon_' + crr_id).hasClass('fa-plus')) {
                    $('#usr_icon_' + crr_id).removeClass('fa-plus');
                    $('#usr_icon_' + crr_id).addClass('fa-check');
                } else {
                    $('#usr_icon_' + crr_id).removeClass('fa-check');
                    $('#usr_icon_' + crr_id).addClass('fa-plus');
                }
                $('.selected').each(function () {
                    ids.push($(this).attr('data-id'));
                });
                $('input[name="assign_to"]').val(ids);
            });

            $(document).on("click", ".del_task", function () {
                var id = $(this);
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'DELETE',
                    dataType: 'JSON',
                    data: {"_token": "{{ csrf_token() }}"},
                    success: function (data) {
                        $('#' + data.task_id).remove();
                        show_toastr('{{__('success')}}', '{{ __("Task Deleted Successfully!")}}');
                    },
                });
            });

            /*For Task Comment*/
            $(document).on('click', '#comment_submit', function (e) {
                var curr = $(this);

                var comment = $.trim($("#form-comment textarea[name='comment']").val());
                var tagword = /@(\w+)(?!.*@\w)/;
                if (comment != '') {
                    $.ajax({
                        url: $("#form-comment").data('action'),
                        data: {comment: comment, "_token": "{{ csrf_token() }}"},
                        type: 'POST',
                        success: function (data) {
                            data = JSON.parse(data);
                            console.log(data);
                            var html = "<div class='list-group-item px-0'>" +
                                "                    <div class='row align-items-center'>" +
                                "                        <div class='col-auto'>" +
                                "                            <a href='#' class='avatar avatar-sm rounded-circle ms-2'>" +
                                "                                <img src="+data.default_img+" alt='' class='avatar-sm rounded-circle'>" +
                                "                            </a>" +
                                "                        </div>" +
                                "                        <div class='col ml-n2'>" +
                                "                            <p class='d-block h6 text-sm font-weight-light mb-0 text-break'>" + data.comment + "</p>" +
                                "                            <small class='d-block'>"+data.current_time+"</small>" +
                                "                        </div>" +
                                "                        <div class='action-btn bg-danger me-4'><div class='col-auto'><a href='#' class='mx-3 btn btn-sm  align-items-center delete-comment' data-url='" + data.deleteUrl + "'><i class='ti ti-trash text-white'></i></a></div></div>" +
                                "                    </div>" +
                                "                </div>";

                            $("#comments").prepend(html);
                            $("#form-comment textarea[name='comment']").val('');
                            // load_task(curr.closest('.task-id').attr('id'));
                            show_toastr('{{__('success')}}', '{{ __("Comment Added Successfully!")}}');
                        },
                        error: function (data) {
                            show_toastr('error', '{{ __("Some Thing Is Wrong!")}}');
                        }
                    });
                } else {
                    show_toastr('error', '{{ __("Please write comment!")}}');
                }
            });
            $(document).on("click", ".delete-comment", function () {
                var btn = $(this);

                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'DELETE',
                    dataType: 'JSON',
                    data: {"_token": "{{ csrf_token() }}"},
                    success: function (data) {
                        // load_task(btn.closest('.task-id').attr('id'));
                        show_toastr('{{__('success')}}', '{{ __("Comment Deleted Successfully!")}}');
                        btn.closest('.list-group-item').remove();
                    },
                    error: function (data) {
                        data = data.responseJSON;
                        if (data.message) {
                            show_toastr('error', data.message);
                        } else {
                            show_toastr('error', '{{ __("Some Thing Is Wrong!")}}');
                        }
                    }
                });
            });

            /*For Task Link*/
            $(document).on('click', '#link_submit', function () {
                var link = $("#form-link input[name=link]").val();
                if (link != '') {
                    $.ajax({
                        url: $("#form-link").data('action'),
                        data: {link: link, "_token": "{{ csrf_token() }}"},
                        type: 'POST',
                        success: function (data) {
                            data = JSON.parse(data);
                            console.log('form-link', data);
                            // load_task($('.task-id').attr('id'));
                            show_toastr('{{__('success')}}', '{{ __("Link Added Successfully!")}}');
                            var html = '<div class="card border shadow-none checklist-member">' +
                                '                    <div class="px-3 py-2 row align-items-center">' +
                                '                        <div class="col">' +
                                '                            <div class="form-check form-check-inline">' +
                                '                                <label class="form-check-label h6 text-sm" for="check-item-' + data.id + '">' + data.link + '" target="_blank"</label>' +
                                '                            </div>' +
                                '                        </div>' +
                                '                        <div class="col-auto"> <div class="action-btn bg-danger ms-2">' +
                                '                            <a href="#" class="mx-3 btn btn-sm  align-items-center delete-link" role="button" data-url="' + data.deleteUrl + '">' +
                                '                                <i class="ti ti-trash text-white"></i>' +
                                '                            </a>' +
                                '                        </div></div>' +
                                '                    </div>' +
                                '                </div>'

                            $("#link").append(html);
                            $("#form-link input[name=link]").val('');
                            $("#form-link").collapse('toggle');
                        },
                        error: function (data) {
                            data = data.responseJSON;
                            show_toastr('error', data.message);
                        }
                    });
                } else {
                    show_toastr('error', '{{ __("Please write link!")}}');
                }
            });

            $(document).on("click", ".delete-link", function () {
                var btn = $(this);
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'DELETE',
                    dataType: 'JSON',
                    data: {"_token": "{{ csrf_token() }}"},
                    success: function (data) {
                        // load_task($('.task-id').attr('id'));
                        show_toastr('{{__('success')}}', '{{ __("Sub task Deleted Successfully!")}}');
                        btn.closest('.link-member').remove();
                    },
                    error: function (data) {
                        data = data.responseJSON;
                        if (data.message) {
                            show_toastr('error', data.message);
                        } else {
                            show_toastr('error', '{{ __("Some Thing Is Wrong!")}}');
                        }
                    }
                });
            });

            /*For Task Checklist*/
            $(document).on('click', '#checklist_submit', function () {
                var name = $("#form-checklist input[name=name]").val();
                var link = $("#form-checklist input[name=link]").val();
                var parent_id = $("#form-checklist select[name=parent_id]").val();
                var description = $("#form-checklist input[name=description]").val();
                if (name != '') {
                    $.ajax({
                        url: $("#form-checklist").data('action'),
                        data: {parent_id: parent_id, link: link, description: description, name: name, "_token": "{{ csrf_token() }}"},
                        type: 'POST',
                        success: function (data) {
                            data = JSON.parse(data);
                            console.log('form-checklist', data);
                            // load_task($('.task-id').attr('id'));
                            show_toastr('{{__('success')}}', '{{ __("Sub Task Added Successfully!")}}');
                            var html = '<div class="card border shadow-none checklist-member">' +
                            '                    <div class="px-3 py-2 row align-items-center">' +
                            '                        <div class="col">' +
                            '                            <div class="form-check form-check-inline">' +
                            '                                <input type="checkbox" class="form-check-input" id="check-item-' + data.id + '" value="' + data.id + '" data-url="' + data.updateUrl + '">' +
                            '                                <label class="form-check-label h6 text-sm" for="check-item-' + data.id + '"><a>' + data.name + '</a></label>' +
                            '                            </div>' +
                            '                            <div class="form-check form-check-inline">' +
                            '                                <label class="form-check-label h6 text-sm" for="check-item-' + data.id + '"><a href="' + data.link + '" target="_blank">' + data.link + '</a></label>' +
                            '                            </div>' +
                            '                            <div class="form-check form-check-inline">' +
                            '                                <label class="form-check-label h6 text-sm text-black" for="check-item-' + data.id + '"><a>' + data.description + '</a></label>' +
                            '                            </div>' +
                            '                            <div class="form-check form-check-inline">' +
                            '                                <label class="form-check-label h6 text-sm text-black" for="check-item-' + data.id + '"><a>' + data.parent_id + '</a></label>' +
                            '                            </div>' +
                            '                        </div>' +
                            '                        <div class="col-auto"> <div class="action-btn bg-danger ms-2">' +
                            '                            <a href="#" class="mx-3 btn btn-sm  align-items-center delete-link" role="button" data-url="' + data.deleteUrl + '">' +
                            '                                <i class="ti ti-trash text-white"></i>' +
                            '                            </a>' +
                            '                        </div></div>' +
                            '                    </div>' +
                            '                </div>'

                            $("#checklist").append(html);
                            $("#form-checklist input[name=name]").val('');
                            $("#form-checklist input[name=link]").val('');
                            $("#form-checklist input[name=description]").val('');
                            $("#form-checklist input[select=parent_id]").val('');
                            $("#form-checklist").collapse('toggle');
                        },
                        error: function (data) {
                            data = data.responseJSON;
                            show_toastr('error', data.message);
                        }
                    });
                } else {
                    show_toastr('error', '{{ __("Please write checklist name!")}}');
                }
            });
            $(document).on("change", "#checklist input[type=checkbox]", function () {
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'POST',
                    dataType: 'JSON',
                    data: {"_token": "{{ csrf_token() }}"},
                    success: function (data) {
                        // load_task($('.task-id').attr('id'));
                        show_toastr('{{__('Success')}}', '{{ __("Sub Task Updated Successfully!")}}', 'success');
                    },
                    error: function (data) {
                        data = data.responseJSON;
                        if (data.message) {
                            show_toastr('error', data.message);
                        } else {
                            show_toastr('error', '{{ __("Some Thing Is Wrong!")}}');
                        }
                    }
                });
            });
            $(document).on("click", ".delete-checklist", function () {
                var btn = $(this);
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'DELETE',
                    dataType: 'JSON',
                    data: {"_token": "{{ csrf_token() }}"},
                    success: function (data) {
                        // load_task($('.task-id').attr('id'));
                        show_toastr('{{__('success')}}', '{{ __("Checklist Deleted Successfully!")}}');
                        btn.closest('.checklist-member').remove();
                    },
                    error: function (data) {
                        data = data.responseJSON;
                        if (data.message) {
                            show_toastr('error', data.message);
                        } else {
                            show_toastr('error', '{{ __("Some Thing Is Wrong!")}}');
                        }
                    }
                });
            });

            /*For Task Attachment*/
            $(document).on('click', '#file_attachment_submit', function () {
                var file_data = $("#task_attachment").prop("files")[0];
                if (file_data != '' && file_data != undefined) {
                    var formData = new FormData();
                    formData.append('file', file_data);
                    formData.append('_token', "{{ csrf_token() }}");
                    $.ajax({
                        url: $("#file_attachment_submit").data('action'),
                        type: 'POST',
                        data: formData,
                        cache: false,
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            $('#task_attachment').val('');
                            $('.attachment_text').html('{{__('Choose a file…')}}');
                            data = JSON.parse(data);
                            // load_task(data.task_id);
                            show_toastr('{{__('success')}}', '{{ __("File Added Successfully!")}}');

                            var delLink = '';
                            if (data.deleteUrl.length > 0) {
                                delLink = ' <div class="action-btn bg-danger "><a href="#" class="action-item delete-comment-file" role="button" data-url="' + data.deleteUrl + '">' +
                                    '                                        <i class="ti ti-trash text-white"></i>' +
                                    '                                    </a></div>';
                            }

                            var html = '<div class="card mb-3 border shadow-none task-file">' +
                                '                    <div class="px-3 py-3">' +
                                '                        <div class="row align-items-center">' +
                                '                            <div class="col ml-n2">' +
                                '                                <h6 class="text-sm mb-0">' +
                                '                                    <a href="#">' + data.name + '</a>' +
                                '                                </h6>' +
                                '                                <p class="card-text small text-muted">' + data.file_size + '</p>' +
                                '                           </div>' +
                                '                            <div class="col-auto"> <div class="action-btn bg-secondary ">' +
                                '                                <a href="{{asset(Storage::url('tasks'))}}/' + data.file + '" download class="action-item" role="button">' +
                                '                                    <i class="ti ti-download text-white"></i>' +
                                '                                </a>' +
                                '                            </div></div>' +
                                delLink +
                                '                        </div>' +
                                '                    </div>' +
                                '                </div>'

                            $("#comments-file").prepend(html);
                        },
                        error: function (data) {
                            data = data.responseJSON;
                            console.log('error', data);
                            if (data.message) {
                                show_toastr('error', data.errors.file[0]);
                                $('#file-error').text(data.errors.file[0]).show();
                            } else {
                                show_toastr('error', '{{ __("Some Thing Is Wrong!")}}');
                            }
                        }
                    });
                } else {
                    show_toastr('error', '{{ __("Please select file!")}}');
                }
                console.log('not working');
            });
            $(document).on("click", ".delete-comment-file", function () {
                var btn = $(this);
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'DELETE',
                    dataType: 'JSON',
                    data: {"_token": "{{ csrf_token() }}"},
                    success: function (data) {
                        // load_task(btn.closest('.task-id').attr('id'));
                        show_toastr('{{__('success')}}', '{{ __("File Deleted Successfully!")}}');
                        btn.closest('.task-file').remove();
                    },
                    error: function (data) {
                        data = data.responseJSON;
                        if (data.message) {
                            show_toastr('error', data.message);
                        } else {
                            show_toastr('error', '{{ __("Some Thing Is Wrong!")}}');
                        }
                    }
                });
            });

            /*For Favorite*/
            $(document).on('click', '#add_favourite', function () {
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'POST',
                    data: {"_token": "{{ csrf_token() }}"},
                    success: function (data) {
                        if (data.fav == 1) {
                            $('#add_favourite').addClass('action-favorite');
                        } else if (data.fav == 0) {
                            $('#add_favourite').removeClass('action-favorite');
                        }
                    }
                });
            });

            /*For Complete*/
            $(document).on('change', '#complete_task', function () {
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'POST',
                    data: {"_token": "{{ csrf_token() }}"},
                    success: function (data) {
                        if (data.com == 1) {
                            $("#complete_task").prop("checked", true);
                        } else if (data.com == 0) {
                            $("#complete_task").prop("checked", false);
                        }
                        $('#' + data.task).insertBefore($('#task-list-' + data.stage + ' .empty-container'));
                        load_task(data.task);
                    }
                });
            });

            $("#browser").treeview();

            // second example
            $("#navigation").treeview({
                persist: "location",
                collapsed: true,
                unique: true
            });

            // third example
            $("#red").treeview({
                animated: "fast",
                collapsed: true,
                unique: true,
                persist: "cookie",
                toggle: function() {
                    window.console && console.log("%o was toggled", this);
                }
            });

            // fourth example
            $("#black, #gray").treeview({
                control: "#treecontrol",
                persist: "cookie",
                cookieId: "treeview-black"
            });

            /*Progress Move*/
            $(document).on('change', '#task_progress', function () {
                var progress = $(this).val();
                $('#t_percentage').html(progress);
                $.ajax({
                    url: $(this).attr('data-url'),
                    data: {progress: progress, "_token": "{{ csrf_token() }}"},
                    type: 'POST',
                    success: function (data) {
                        load_task(data.task_id);
                    }
                });
            });        
        });

        function load_task(id) {
            $.ajax({
                url: "{{route('projects.tasks.get','_task_id')}}".replace('_task_id', id),
                dataType: 'html',
                data: {"_token": "{{ csrf_token() }}"},
                success: function (data) {
                    $('#' + id).html('');
                    $('#' + id).html(data);
                }
            });
        }

        // var INITIAL_WAIT = 3000;
        // var INTERVAL_WAIT = 10000;
        // var ONE_SECOND = 1000;

        // var events = ["mouseup", "keydown", "scroll"];
        // // var clickCount = 0;
        // // var linkClickCount = 0;

        // document.addEventListener("DOMContentLoaded", function () {
        //     var linkClickCount = 0
        //     events.forEach(function (e) {
        //         document.addEventListener(e, function () {
        //             endTime = Date.now() + INTERVAL_WAIT;
        //             if (e === "mouseup") {
        //                 if (event.target.nodeName === 'A') {
        //                     linkClickCount++;
        //                     // document.getElementById("tasklinks").innerHTML = linkClickCount;
        //                     // console.log(linkClickCount);
        //                 }
        //             }
        //         });
        //     });
        // });


        // function formatTime(ms) {
        //     return Math.floor(ms / 1000);
        // }
    </script>

@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item">{{__('Task')}}</li>
@endsection
@push('script-page')
@endpush
@section('action-btn')
    <div class="float-end">
            @can('create project task')
                <!-- <a href="#" data-size="lg" data-url="{{ route('projects.tasks.invite',$project->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Task')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-user"></i>
                </a> -->
                <a href="#" data-size="lg" data-url="{{ route('projects.tasks.create',$project->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Task')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-plus"></i>
                </a>
            @endcan
            <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">
                <i class="ti ti-filter"></i>
            </a>
    </div>

@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('projects.tasks.index',$project->id),'method' => 'GET','id'=>'frm_submit')) }}
                        <div class="d-flex align-items-center justify-content-end">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 me-2">
                                    <div class="btn-box">
                                        {{ Form::label('category_template_id', __('Group Name'),['class'=>'form-label']) }}
                                        {{ Form::select('category_template_id',$category_template_id,null, array('class' => 'form-control select')) }}
                                    </div>
                                </div>
                            <div class="col-auto float-end ms-2 mt-4">

                                <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('frm_submit').submit(); return false;" data-bs-toggle="tooltip" data-original-title="{{__('apply')}}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{ route('projects.tasks.index',$project->id) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                   title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-off text-white "></i></span>
                                </a>
                            </div>

                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th scope="col">{{__('Name')}}</th>
                                    <th scope="col">{{__('End Date')}}</th>
                                    <th scope="col">{{__('Assigned To')}}</th>
                                    <th scope="col">{{__('Priority')}}</th>
                                    <th scope="col">{{__('Completion')}}
                                    <div class="text-danger" style="font-size:8px;">
                                    {{ __('from sub task') }}
                                    </div>
                                    </th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th scope="col"></th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody class="list">
                                @if(count($tasks) > 0)
                                    @foreach($tasks as $task)
                                        <tr>
                                            <td>
                                                <p>
                                                @if ($task->category_templates->name === "00. Client Data")
                                                <span class="badge-xs badge bg-info  p-2 px-3 rounded">{{ $task->category_templates->name }}</span>
                                                @elseif ($task->category_templates->name === "A. Pre engagement")
                                                <span class="badge-xs badge bg-warning  p-2 px-3 rounded">{{ $task->category_templates->name }}</span>
                                                @elseif ($task->category_templates->name === "B. Risk Assessment")
                                                <span class="badge-xs badge bg-danger  p-2 px-3 rounded">{{ $task->category_templates->name }}</span>
                                                @elseif ($task->category_templates->name === "C. Risk Response")
                                                <span class="badge-xs badge bg-success  p-2 px-3 rounded">{{ $task->category_templates->name }}</span>
                                                @elseif ($task->category_templates->name === "D. Conclution and Completion")
                                                <span class="badge-xs badge bg-dark  p-2 px-3 rounded">{{ $task->category_templates->name }}</span>
                                                @endif
                                                </p>
                                                <p class="h6 text-sm font-weight-bold mb-0"><a href="#" data-url="{{ route('projects.tasks.show',[$project->id,$task->id]) }}" data-ajax-popup="true" data-size="lg" data-bs-original-title="{{$task->name}}">{{$task->name}}</a></p>
                                                <span class="d-flex text-sm text-muted justify-content-between">
                                                <span style="font-size: 10px" class="m-0">{{ $task->project->project_name }}</span>
                                                
                                                <!-- @if ($task->stage_id == 1)
                                                <span class="me-5 badge bg-primary p-2 px-3 rounded">{{ $task->stage->name }}</span>
                                                @elseif ($task->stage_id == 2)
                                                <span class="me-5 badge bg-info p-2 px-3 rounded">{{ $task->stage->name }}</span>
                                                @elseif ($task->stage_id == 3)
                                                <span class="me-5 badge bg-warning p-2 px-3 rounded">{{ $task->stage->name }}</span>
                                                @elseif ($task->stage_id == 4)
                                                <span class="me-5 badge bg-success p-2 px-3 rounded">{{ $task->stage->name }}</span>
                                                @endif -->
                                            </td>
                                            <td class="{{ (strtotime($task->end_date) < time()) ? 'text-danger' : '' }}">{{ Utility::getDateFormated($task->end_date) }}</td>
                                            <td>
                                                <div class="avatar-group">
                                                    @if($task->users()->count() > 0)
                                                        @if($users = $task->users())
                                                            @foreach($users as $key => $user)
                                                                @if($key< 3)
                                                                    <a href="#" class="avatar rounded-circle avatar-sm">
                                                                        <img data-original-title="{{(!empty($user)?$user->name:'')}}" @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif title="{{ $user->name }}" class="hweb">
                                                                    </a>
                                                                    {{ $user->name }}
                                                                @else
                                                                    @break
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                        @if(count($users) > 3)
                                                            <a href="#" class="avatar rounded-circle avatar-sm">
                                                                <img  data-original-title="{{(!empty($user)?$user->name:'')}}" @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif class="hweb">
                                                            </a>
                                                            {{ $user->name }}
                                                        @endif
                                                    @else
                                                        {{ __('-') }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                            <select class="form-control select" name="priority" id="priority" onchange="updateStatus(this.value, {{ $task->id }})">
                                                @foreach(\App\Models\ProjectTask::$priority as $key => $val)
                                                    <option value="{{ $key }}" {{ ($key == $task->priority) ? 'selected' : '' }} >{{ __($val) }}</option>
                                                @endforeach
                                            </select>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="completion mr-2">{{ $task->taskProgress()['percentage'] }}</span>
                                                    {{--<div>
                                                        <div class="progress" style="width: 100px;">
                                                            <div class="progress-bar bg-{{ $task->taskProgress()['color'] }}" role="progressbar" aria-valuenow="{{ $task->taskProgress()['percentage'] }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $task->taskProgress()['percentage'] }};"></div>
                                                        </div>
                                                    </div>--}}
                                                </div>
                                            </td>
                                            <td>
                                            <select class="form-control select" name="stage_id" id="stage_id" onchange="updateStage(this.value, {{ $task->id }})">
                                                <option value="0" hidden>{{$task->stage->name}}</option>
                                                @foreach($taskstage as $stage)
                                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                                @endforeach
                                            </select>
                                            </td>
                                            <td class="text-end w-15">
                                                <div class="actions">
                                                    <a class="action-item px-1" data-bs-toggle="tooltip" title="{{__('Attachment')}}" data-original-title="{{__('Attachment')}}">
                                                        <i class="ti ti-paperclip mr-2"></i>{{ count($task->taskFiles) }}
                                                    </a>
                                                    <a href="#" data-url="{{ route('projects.tasks.comment',[$project->id,$task->id]) }}" data-ajax-popup="true" data-size="lg" data-bs-original-title="{{$task->name}} class="action-item px-1" data-bs-toggle="tooltip" title="{{__('Comment')}}" data-original-title="{{__('Comment')}}">
                                                        <i class="ti ti-brand-hipchat mr-2"></i>{{ count($task->comments) }}
                                                    </a>
                                                    <a class="action-item px-1" data-bs-toggle="tooltip" title="{{__('Checklist')}}" data-original-title="{{__('Checklist')}}">
                                                        <i class="ti ti-list-check mr-2"></i>{{ $task->countTaskChecklist() }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="Action">
                                                <span>
                                                    @if(Auth::user()->type == "client")    
                                                        @can('edit project task')
                                                            <div class="action-btn bg-primary ms-2">
                                                                <a href="#!" data-size="lg" data-url="{{ route('projects.tasks.edit',[$project->id,$task->id]) }}" data-ajax-popup="true" class="dropdown-item" data-bs-original-title="{{__('Invite Member To ').$task->name}}"
                                                                class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="Edit "
                                                                data-original-title="{{ __('Invite User') }}">
                                                                    <i class="ti ti-send text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                    @else
                                                        @can('edit project task')
                                                            <div class="action-btn bg-primary ms-2">
                                                                <a href="#!" data-size="lg" data-url="{{ route('projects.tasks.edit',[$project->id,$task->id]) }}" data-ajax-popup="true" 
                                                                class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-bs-original-title="{{__('Edit ').$task->name}}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                    @endif
                                                    @can('delete project task')
                                                        <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['projects.tasks.destroy', [$project->id,$task->id]]]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para " data-bs-toggle="tooltip" title="{{__('Delete')}}"
                                                                       data-original-title="{{ __('Delete') }}"
                                                                       data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                       data-confirm-yes="document.getElementById('delete-form-{{ $task->id }}').submit();">
                                                                        <i class="ti ti-trash text-white"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                    @endcan
                                                </span>
                                        </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th scope="col" colspan="7"><h6 class="text-center">{{__('No tasks found')}}</h6></th>
                                    </tr>
                                @endif
                                </tbody>
                            </table>

                            <script>
                                function updateStatus(priority, id) {
                                    // Kirim request POST ke server dengan nilai status dan id data yang dipilih
                                    $.ajax({
                                        url: "{{route("update-priority")}}",
                                        type: "POST",
                                        data: { 
                                            id: id,
                                            priority: priority,
                                            // Add the CSRF token to the request data
                                            _token: "{{ csrf_token() }}"
                                        },
                                        success: function (data) {
                                            console.log(data);
                                        },
                                    });
                                }

                                function updateStage(stage_id, id) {
                                    // Kirim request POST ke server dengan nilai status dan id data yang dipilih
                                    $.ajax({
                                        url: "{{route("update-stage")}}",
                                        type: "POST",
                                        data: { 
                                            id: id,
                                            stage_id: stage_id,
                                            // Add the CSRF token to the request data
                                            _token: "{{ csrf_token() }}"
                                        },
                                        success: function (data) {
                                            console.log(data);
                                        },
                                    });
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
