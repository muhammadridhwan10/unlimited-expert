@extends('layouts.admin')
@section('page-title')
    {{__('Manage Projects')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Projects')}}</li>
@endsection
@section('action-btn')

    <div class ="float-start">
                <div class="input-group">
                    <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
                    <input id="project_keyword" name="keyword" type="search" class="form-control" placeholder="Search...">
                </div>
    </div>

    <div class="float-end">
        @if($view == 'list')
            <a href="{{ route('projects.grid','grid') }}"  data-bs-toggle="tooltip" title="{{__('Grid View')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-layout-grid"></i>
            </a>

        @else
            <a href="{{ route('projects.index') }}"  data-bs-toggle="tooltip" title="{{__('List View')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-list"></i>
            </a>
        @endif


        {{------------ Start Filter ----------------}}
                <a href="#" class="btn btn-sm btn-primary action-item" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ti ti-filter"></i>
                </a>
                <div class="dropdown-menu  dropdown-steady" id="project_sort">
                    <a class="dropdown-item active" href="#" data-val="created_at-desc">
                        <i class="ti ti-sort-descending"></i>{{__('Newest')}}
                    </a>
                    <a class="dropdown-item" href="#" data-val="created_at-asc">
                        <i class="ti ti-sort-ascending"></i>{{__('Oldest')}}
                    </a>

                    <a class="dropdown-item" href="#" data-val="project_name-desc">
                        <i class="ti ti-sort-descending-letters"></i>{{__('From Z-A')}}
                    </a>
                    <a class="dropdown-item" href="#" data-val="project_name-asc">
                        <i class="ti ti-sort-ascending-letters"></i>{{__('From A-Z')}}
                    </a>
                </div>

            {{------------ End Filter ----------------}}

            {{------------ Start Status Filter ----------------}}
                <a href="#" class="btn btn-sm btn-primary action-item" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="btn-inner--icon">{{__('Status')}}</span>
                </a>
                <div class="dropdown-menu  project-filter-actions dropdown-steady" id="project_status">
                    <a class="dropdown-item filter-action filter-show-all pl-4" href="#">{{__('Show All')}}</a>
                    @foreach(\App\Models\Project::$project_status as $key => $val)
                        <a class="dropdown-item filter-action pl-4 {{ $key == 'in_progress' ? 'active' : '' }}" href="#" data-val="{{ $key }}">{{__($val)}}</a>
                    @endforeach
                </div>
            {{------------ End Status Filter ----------------}}

            {{------------ Start Tags Filter ----------------}}
                <a href="#" class="btn btn-sm btn-primary action-item" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="btn-inner--icon">{{__('Tags')}}</span>
                </a>
                <div class="dropdown-menu  project-filter-actions-tags dropdown-steady" id="tags">
                    <a class="dropdown-item filter-action-tags filter-show-all-tags pl-4 active" href="#">{{__('Show All')}}</a>
                    @foreach(\App\Models\Project::$tags as $key => $val)
                        <a class="dropdown-item filter-action-tags pl-4" href="#" data-val="{{ $key }}">{{__($val)}}</a>
                    @endforeach
                </div>
            {{------------ End Status Filter ----------------}}

            {{------------ Start Label Filter ----------------}}
                <a href="#" class="btn btn-sm btn-primary action-item" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="btn-inner--icon">{{__('Label')}}</span>
                </a>
                <div class="dropdown-menu  project-filter-actions-label dropdown-steady" id="label">
                    <a class="dropdown-item filter-action-label filter-show-all-label pl-4 active" href="#">{{__('Show All')}}</a>
                    @foreach(\App\Models\Project::$label as $key => $val)
                        <a class="dropdown-item filter-action-label pl-4" href="#" data-val="{{ $key }}">{{__($val)}}</a>
                    @endforeach
                </div>
            {{------------ End Label Filter ----------------}}

        @can('create project')
            <a href="#" data-size="lg" data-url="{{ route('projects.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Project')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row min-750" id="project_view"></div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function () {
            var sort = 'created_at-desc';
            var status = '';
            var tags = '';
            var label = '';
            ajaxFilterProjectView('created_at-desc', '',['in_progress']);
            $(".project-filter-actions").on('click', '.filter-action', function (e) {
                if ($(this).hasClass('filter-show-all')) {
                    $('.filter-action').removeClass('active');
                    $(this).addClass('active');
                } else {
                    $('.filter-show-all').removeClass('active');
                    if ($(this).hasClass('active')) {
                        $(this).removeClass('active');
                        $(this).blur();
                    } else {
                        $(this).addClass('active');
                    }
                }

                var filterArray = [];
                var url = $(this).parents('.project-filter-actions').attr('data-url');
                $('div.project-filter-actions').find('.active').each(function () {
                    filterArray.push($(this).attr('data-val'));
                });

                status = filterArray;

                ajaxFilterProjectView(sort, $('#project_keyword').val(), status, tags, label);
            });

            $(".project-filter-actions-tags").on('click', '.filter-action-tags', function (e) {
                if ($(this).hasClass('filter-show-all-tags')) {
                    $('.filter-action-tags').removeClass('active');
                    $(this).addClass('active');
                } else {
                    $('.filter-show-all-tags').removeClass('active');
                    if ($(this).hasClass('active')) {
                        $(this).removeClass('active');
                        $(this).blur();
                    } else {
                        $(this).addClass('active');
                    }
                }

                var filterArray = [];
                var url = $(this).parents('.project-filter-actions-tags').attr('data-url');
                $('div.project-filter-actions-tags').find('.active').each(function () {
                    filterArray.push($(this).attr('data-val'));
                });

                tags = filterArray;

                ajaxFilterProjectView(sort, $('#project_keyword').val(), status, tags, label);
            });

            $(".project-filter-actions-label").on('click', '.filter-action-label', function (e) {
                if ($(this).hasClass('filter-show-all-label')) {
                    $('.filter-action-label').removeClass('active');
                    $(this).addClass('active');
                } else {
                    $('.filter-show-all-label').removeClass('active');
                    if ($(this).hasClass('active')) {
                        $(this).removeClass('active');
                        $(this).blur();
                    } else {
                        $(this).addClass('active');
                    }
                }

                var filterArray = [];
                var url = $(this).parents('.project-filter-actions-label').attr('data-url');
                $('div.project-filter-actions-label').find('.active').each(function () {
                    filterArray.push($(this).attr('data-val'));
                });

                label = filterArray;

                ajaxFilterProjectView(sort, $('#project_keyword').val(), status,tags,label);
            });

            // when change sorting order
            $('#project_sort').on('click', 'a', function () {
                sort = $(this).attr('data-val');
                ajaxFilterProjectView(sort, $('#project_keyword').val(), status, tags, label);
                $('#project_sort a').removeClass('active');
                $(this).addClass('active');
            });

            // when searching by project name
            $(document).on('keyup', '#project_keyword', function () {
                ajaxFilterProjectView(sort, $(this).val(), status, tags, label);
            });


            $(document).on('click', '.invite_usr', function () {
                var project_id = $('#project_id').val();
                var user_id = $(this).attr('data-id');

                $.ajax({
                    url: '{{ route('invite.project.user.member') }}',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        'project_id': project_id,
                        'user_id': user_id,
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function (data) {
                        if (data.code == '200') {
                            show_toastr(data.status, data.success, 'success')
                            setInterval('location.reload()', 5000);
                        } else if (data.code == '404') {
                            show_toastr(data.status, data.errors, 'error')
                        }
                    }
                });
            });

            $(document).on('click', '.invite_client', function () {
                var project_id = $('#project_id').val();
                var user_id = $(this).attr('data-id');

                $.ajax({
                    url: '{{ route('invite.project.client.member') }}',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        'project_id': project_id,
                        'user_id': user_id,
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function (data) {
                        if (data.code == '200') {
                            show_toastr(data.status, data.success, 'success')
                            setInterval('location.reload()', 5000);
                        } else if (data.code == '404') {
                            show_toastr(data.status, data.errors, 'error')
                        }
                    }
                });
            });

            $(document).on('click', '.pagination a', function (event) {
                event.preventDefault();
                var url = $(this).attr('href');
                var page = url.split('page=')[1];
                ajaxFilterProjectView(sort, $('#project_keyword').val(), status, tags, label, page);
            });
        });


        var currentRequest = null;

        function ajaxFilterProjectView(project_sort, keyword = '', status = '', tags = '', label = '', page = 1) {
            var mainEle = $('#project_view');
            var view = '{{$view}}';
            var data = {
                view: view,
                sort: project_sort,
                keyword: keyword,
                status: status,
                tags: tags,
                label: label,
                page: page
            }

            currentRequest = $.ajax({
                url: '{{ route('filter.project.view') }}',
                data: data,
                beforeSend: function () {
                    if (currentRequest != null) {
                        currentRequest.abort();
                    }
                },
                success: function (data) {
                    mainEle.html(data.html);
                    $('[id^=fire-modal]').remove();
                    loadConfirm();
                }
            });
        }
    </script>
@endpush
