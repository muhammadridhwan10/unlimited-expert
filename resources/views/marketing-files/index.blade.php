@extends('layouts.admin')

@section('page-title')
    {{__('Manage Marketing Files')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Manage Marketing Files')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create document')
            @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'partners'|| \Auth::user()->type == 'company')
                <a href="#" data-size="lg" data-url="{{ route('marketing-files.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Marketing Files')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-plus"></i>
                </a>
            @endif
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
                                    <th>{{__('File Name')}}</th>
                                    <th>{{__('File')}}</th>
                                    <th width="200px">{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($marketing_files as $marketing_file)
                                    <tr>
                                        <td>{{!empty($marketing_file->name)?$marketing_file->name:'-'}}</td>
                                        <td>
                                            <img alt="Image placeholder" src="{{ asset('assets/images/gallery.png')}}" class="avatar view-images rounded-circle avatar-sm" data-bs-toggle="tooltip" title="{{__('View File')}}" data-original-title="{{__('View File')}}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="{{$marketing_file->id}}" id="track-images-{{$marketing_file->id}}">
                                        </td>
                                        <td>
                                            @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'partners'|| \Auth::user()->type == 'company')
                                               <div class="action-btn bg-primary ms-2">
                                                    <a href="#" data-url="{{ URL::to('marketing-files/'.$marketing_file->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Marketing Files')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {!! $marketing_files->links() !!}
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

                var p_url = "{{route('marketing-files.view')}}";
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
