@extends('layouts.admin')
@section('page-title')
    {{__('Manage Form Client')}}
@endsection
@push('script-page')
    <script>
        $(document).ready(function () {
            $('.cp_link').on('click', function () {
                var value = $(this).attr('data-link');
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val(value).select();
                document.execCommand("copy");
                $temp.remove();
                show_toastr('success', '{{__('Link Copy on Clipboard')}}')
            });
        });

        $(document).ready(function () {
            $('.iframe_link').on('click', function () {
                var value = $(this).attr('data-link');
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val(value).select();
                document.execCommand("copy");
                $temp.remove();
                show_toastr('success', '{{__('Link Copy on Clipboard')}}')
            });
        });
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Form Client')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="md" data-link="{{url('/form/new-client')}}"  data-bs-toggle="tooltip" title="{{__('Click to copy link')}}" class="btn btn-sm btn-primary cp_link">
            <i class="ti ti-copy text-white"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Client Name')}}</th>
                                <th>{{__('Status')}}</th>
                                @if(\Auth::user()->type=='company' ||  \Auth::user()->type=='admin')
                                    <th class="text-center" width="200px">{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($client as $clients)
                                <tr>
                                    <td>{{ $clients->name }}</td>
                                    <td>

                                        @if($clients->status_client=="new")
                                            <div class="status_badge badge bg-info p-2 px-3 rounded">{{ $clients->status_client }}</div>
                                        @elseif($clients->status_client=="Approved")
                                            <div class="status_badge badge bg-success p-2 px-3 rounded">{{ $clients->status_client }}</div>
                                        @elseif($clients->status_client =="Rejected")
                                            <div class="status_badge badge bg-danger p-2 px-3 rounded">{{ $clients->status_client }}</div>
                                        @elseif($clients->status_client =="Pending")
                                            <div class="status_badge badge bg-primary p-2 px-3 rounded">{{ $clients->status_client }}</div>
                                        @endif
                                    </td>
                                    @if(\Auth::user()->type=='company' ||  \Auth::user()->type=='admin')
                                        <td class="text-end">

                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{route('form.client.views',$clients->id)}}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('View Response')}}"><i class="ti ti-eye text-white"></i></a>
                                            </div>
                                            @if($clients->budget == NULL)
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" data-size="lg" data-url="{{route('form.time.budget',$clients->id)}}" data-ajax-popup="true" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" data-title="{{__('Add Time Budget')}}"><i class="ti ti-clock text-white"></i></a>
                                            </div>
                                            @endif
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('form.client.edit',$clients->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Form Builder Edit')}}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
