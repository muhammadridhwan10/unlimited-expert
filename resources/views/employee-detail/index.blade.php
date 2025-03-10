@extends('layouts.admin')
@php
    $profile=asset(Storage::url('uploads/avatar/'));
@endphp
@section('page-title')
    {{__('Employee List')}}
@endsection
@push('script-page')
<script>
    $(document).ready(function () {
        function ajaxFilterEmployeeView(keyword = '', page = 1) {
            $.ajax({
                url: '{{ route('filter.employee.view') }}',
                method: 'GET',
                data: { keyword: keyword, page: page },
                beforeSend: function () {
                    $('#employee-list').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
                },
                success: function (data) {
                    $('#employee-list').html(data.html);
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching employees:', error);
                    $('#employee-list').html('<div class="text-center text-danger">Error loading data.</div>');
                }
            });
        }


        $(document).on('keyup', '#employee_keyword', function () {
            const keyword = $(this).val();
            ajaxFilterEmployeeView(keyword);
        });

        ajaxFilterEmployeeView();
    });
</script>
    <script>
        $(document).ready(function() {

            $('.form-check-input').change(function() {
                var userId = $(this).data('user-id');
                var isActive = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: "{{route("update-active")}}",
                    method: 'POST',
                    data: {
                        user_id: userId,
                        is_active: isActive,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        console.log('User successfully updated.');
                    },
                    error: function(xhr, status, error) {
                        console.error('Something Error: ' + error);
                    }
                });
            });
        });
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Employee List')}}</li>
@endsection
@section('action-btn')
<div class="float-end">
    <a href="#" class="btn btn-sm btn-primary action-item" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="btn-inner--icon"><i class="fas fa-search"></i></span>
        <span class="btn-inner--text"></span>
    </a>
    <div class="dropdown-menu dropdown-steady p-3" id="search_box">
        <div class="input-group">
            <span class="input-group-text text-body">
                <i class="fas fa-search" aria-hidden="true"></i>
            </span>
            <input id="employee_keyword" name="keyword" type="search" class="form-control" placeholder="{{ __('Search Employee...') }}">
        </div>
    </div>
</div>
@endsection
@section('content')
    <div id="employee-list" class="row">

    </div>
@endsection
