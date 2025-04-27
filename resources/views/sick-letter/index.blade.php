@extends('layouts.admin')

@section('page-title')
    {{__('Manage Sick Letter')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Manage Sick Letter')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create leave')
        <a href="#" data-size="lg" data-url="{{ route('sick-letter.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Sick Letter')}}" class="btn btn-sm btn-primary">
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
                        <table class="table">
                            <thead>
                                <tr>
                                    @if(\Auth::user()->type!='employee')
                                        <th>{{__('Employee')}}</th>
                                    @endif
                                    <th>{{__('Total Days')}}</th>
                                    <th>{{__('Sick Letter')}}</th>
                                    <th>{{__('Date Sick Letter')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($absence_sick as $sick)
                                    <tr>
                                        @if(\Auth::user()->type!='employee')
                                            <td>{{ !empty(\Auth::user()->getEmployee($sick->employee_id))?\Auth::user()->getEmployee($sick->employee_id)->name:'-' }}</td>
                                        @endif
                                        <td>{{ !empty($sick->total_sick_days)?$sick->total_sick_days:'-' }}</td>
                                        <td>
                                            <img alt="Image placeholder" src="{{ asset('assets/images/gallery.png')}}" class="avatar view-images rounded-circle avatar-sm" data-bs-toggle="tooltip" title="{{__('View Sick Letter')}}" data-original-title="{{__('View Sick Letter')}}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="{{$sick->id}}" id="track-images-{{$sick->id}}">
                                        </td>
                                         <td>{{ !empty($sick->date_sick_letter)?$sick->date_sick_letter:'-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-3">
        {{ $absence_sick->links() }}
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

                var p_url = "{{route('sick-letter.image.view')}}";
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
