@extends('layouts.admin')

@section('page-title')
    {{__('Manage Document Request')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Manage Document Request')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create document')
        <a href="#" data-size="lg" data-url="{{ route('document-request.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Document Request')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        @endcan
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
                                    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners')
                                        <th>{{__('Employee')}}</th>
                                    @endif
                                    <th>{{__('Approval By')}}</th>
                                    <th>{{__('Client Name')}}</th>
                                    <th>{{__('Request Date')}}</th>
                                    <th>{{__('Document Type')}}</th>
                                    <th>{{__('File')}}</th>
                                    <th>{{__('Status')}}</th>
                                    <th width="200px">{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($documents as $document)
                                    <tr>
                                        @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'client' || \Auth::user()->type == 'staff_client')
                                            <td>{{!empty($document->employee->name)?$document->employee->name:'-'}}</td>
                                        @endif
                                        <td>{{!empty($document->user->name)?$document->user->name:'-'}}</td>
                                        <td>{{!empty($document->client_name)?$document->client_name:'-'}}</td>
                                        <td>{{!empty($document->created_at)?$document->created_at:'-'}}</td>
                                        <td>{{ !empty($document->document_type)?$document->document_type:'-' }}</td>
                                        <td>
                                            @if($document->document_type == 'Contract Employee' || $document->document_type == 'Other Letters')
                                                <img alt="Image placeholder" src="{{ asset('assets/images/gallery.png')}}" class="avatar view-images rounded-circle avatar-sm" data-bs-toggle="tooltip" title="{{__('View File')}}" data-original-title="{{__('View File')}}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="{{$document->id}}" id="track-images-{{$document->id}}">
                                            @endif
                                        </td>
                                        <td>

                                            @if($document->status=="Pending")
                                                <div class="status_badge badge bg-warning p-2 px-3 rounded">{{ $document->status }}</div>
                                            @elseif($document->status=="Completed")
                                                <div class="status_badge badge bg-success p-2 px-3 rounded">{{ $document->status }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($document->status == "Pending")
                                                @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'client' || \Auth::user()->type == 'staff_client')
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" data-url="{{ URL::to('document-request/'.$document->id.'/action') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Document Request Action')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Document Request Action')}}" data-original-title="{{__('Document Request Action')}}">
                                                            <i class="ti ti-caret-right text-white"></i> 
                                                        </a>
                                                    </div>
                                                @endif
                                                @can('edit document')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" data-url="{{ URL::to('document-request/'.$document->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Docuement Request')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                                </div>
                                                @endcan
                                            @else
                                                @if($document->document_type == "Contract Employee" && $document->file_feedback == NULL)
                                                <div class="action-btn bg-success ms-2">
                                                    <a href="#" data-url="{{ URL::to('document-request/'.$document->id.'/action') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Document Request Action')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Document Request Action')}}" data-original-title="{{__('Document Request Action')}}">
                                                        <i class="ti ti-caret-right text-white"></i> 
                                                    </a>
                                                </div>
                                                @endif
                                                <div class="action-btn bg-success ms-2">
                                                    <a href="#" data-url="{{ URL::to('document-request/'.$document->id.'/action') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Document Request Detail')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Document Request Detail')}}" data-original-title="{{__('Document Request Detail')}}">
                                                        <i class="ti ti-eye text-white"></i> 
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {!! $documents->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg ss_modale " role="document">
          <div class="modal-content image_sider_div">

          </div>
        </div>
    </div>

@endsection

@push('script-page')
    <script src="{{url('js/swiper.min.js')}}"></script>
    <script>

        $(document).on('click', '.view-images', function () {

                var p_url = "{{route('document-request.image.view')}}";
                var data = {
                    'id': $(this).attr('data-id')
                };
                    postAjax(p_url, data, function (res) {
                        $('.image_sider_div').html(res);
                        $('#exampleModalCenter').modal('show');
                    });
        });

    </script>
@endpush
