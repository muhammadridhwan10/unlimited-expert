@extends('layouts.admin')

@push('css-page')
    <link rel="stylesheet" href="{{url('css/swiper.min.css')}}">

    <link rel="stylesheet" href="{{url('css/swiper.min.css')}}">


    <style>
        .product-thumbs .swiper-slide img {
        border:2px solid transparent;
        object-fit: cover;
        cursor: pointer;
        }
        .product-thumbs .swiper-slide-active img {
        border-color: #bc4f38;
        }

        .product-slider .swiper-button-next:after,
        .product-slider .swiper-button-prev:after {
            font-size: 20px;
            color: #000;
            font-weight: bold;
        }
        .modal-dialog.modal-md {
            background-color: #fff !important;
        }

        .no-image{
            min-height: 300px;
            align-items: center;
            display: flex;
            justify-content: center;
        }
    </style>
@endpush


@section('content')

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-start">
                    <h5>{{ __('Invoice') }}</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="invoice-table">
                        <thead>
                        <tr>
                            <th>{{ __('Invoice Number') }}</th>
                            <th>{{ __('Issue Date') }}</th>
                            <th>{{ __('Due Date') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoices as $invoice)
                                <tr>
                                    <td class="Id">
                                    @can('manage invoice')
                                        <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->invoice->id)) }}"
                                            class="btn btn-outline-primary">{{ $invoice->invoice->invoice_id }}
                                        </a>
                                    @else
                                        <a
                                            class="btn btn-outline-primary">{{ $invoice->invoice->invoice_id }}
                                        </a>
                                    @endcan
                                    </td>
                                    <td>{{ $invoice->invoice->issue_date }}</td>
                                    <td>
                                        @if ($invoice->invoice->due_date < date('Y-m-d'))
                                            <span class="text-danger"> {{$invoice->invoice->due_date }}</span>
                                        @else
                                            {{ $invoice->invoice->due_date }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($invoice->invoice->status == 0)
                                            <span
                                                class="badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->invoice->status]) }}</span>
                                        @elseif($invoice->invoice->status == 1)
                                            <span
                                                class="badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->invoice->status]) }}</span>
                                        @elseif($invoice->invoice->status == 2)
                                            <span
                                                class="badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->invoice->status]) }}</span>
                                        @elseif($invoice->invoice->status == 3)
                                            <span
                                                class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->invoice->status]) }}</span>
                                        @elseif($invoice->invoice->status == 4)
                                            <span
                                                class="badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->invoice->status]) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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


    <script type="text/javascript">

        function init_slider(){
                if($(".product-left").length){
                        var productSlider = new Swiper('.product-slider', {
                            spaceBetween: 0,
                            centeredSlides: false,
                            loop:false,
                            direction: 'horizontal',
                            loopedSlides: 5,
                            navigation: {
                                nextEl: ".swiper-button-next",
                                prevEl: ".swiper-button-prev",
                            },
                            resizeObserver:true,
                        });
                    var productThumbs = new Swiper('.product-thumbs', {
                        spaceBetween: 0,
                        centeredSlides: true,
                        loop: false,
                        slideToClickedSlide: true,
                        direction: 'horizontal',
                        slidesPerView: 7,
                        loopedSlides: 5,
                    });
                    productSlider.controller.control = productThumbs;
                    productThumbs.controller.control = productSlider;
                }
            }


        $(document).on('click', '.view-images', function () {

                var p_url = "{{route('tracker.image.view')}}";
                var data = {
                    'id': $(this).attr('data-id')
                };
                postAjax(p_url, data, function (res) {
                    $('.image_sider_div').html(res);
                    $('#exampleModalCenter').modal('show');
                    setTimeout(function(){
                        var total = $('.product-left').find('.product-slider').length
                        if(total > 0){
                            init_slider();
                        }

                    },200);

                });
                });


                // ============================ Remove Track Image ===============================//
                $(document).on("click", '.track-image-remove', function () {
                var rid = $(this).attr('data-pid');
                $('.confirm_yes').addClass('image_remove');
                $('.confirm_yes').attr('image_id', rid);
                $('#cModal').modal('show');
                var total = $('.product-left').find('.swiper-slide').length
                });

                function removeImage(id){
                    var p_url = "{{route('tracker.image.remove')}}";
                    var data = {id: id};
                    deleteAjax(p_url, data, function (res) {

                        if(res.flag){
                            $('#slide-thum-'+id).remove();
                            $('#slide-'+id).remove();
                            setTimeout(function(){
                                var total = $('.product-left').find('.swiper-slide').length
                                if(total > 0){
                                    init_slider();
                                }else{
                                    $('.product-left').html('<div class="no-image"><h5 class="text-muted">Images Not Available .</h5></div>');
                                }
                            },200);
                        }

                        $('#cModal').modal('hide');
                        show_toastr('error',res.msg,'error');
                    });
                }

                // $(document).on("click", '.remove-track', function () {
                // var rid = $(this).attr('data-id');
                // $('.confirm_yes').addClass('t_remove');
                // $('.confirm_yes').attr('uid', rid);
                // $('#cModal').modal('show');
            // });


    </script>
    <script>
        $(document).ready(function () {
            loadTimeTracker();

            function loadTimeTracker() {
                var month = $("#month").val();

                $.ajax({
                    url: '{{ route('time.tracker.search_json') }}',
                    type: 'POST',
                    data: {
                        month: month,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function (data) {
                        var tr = '';

                        if (data.length > 0) {
                            $.each(data, function (index, item) {
                            var startDate = new Date(item.start_time);
                            var formattedStartDate = startDate.toLocaleString('en-US', {
                                weekday: 'long',
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric',
                            });
                            var totalSeconds = item.total_time;
                            var hours = Math.floor(totalSeconds / 3600);
                            var minutes = Math.floor((totalSeconds % 3600) / 60);
                            var seconds = totalSeconds % 60;

                            var totalTimeFormatted = hours + ':' + (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;

                                tr += '<tr>' +
                        '<td>' + formattedStartDate + '</td>' +
                        '<td>' + (item.user ? item.user.name : '-') + '</td>' +
                        '<td>' + item.project_name + '</td>' +
                        '<td>' + item.start_time + '</td>' +
                        '<td>' + item.end_time + '</td>' +
                        '<td>' + totalTimeFormatted + '</td>' +
                        '<td>' +
                            '<img alt="Image placeholder" src="{{ asset('assets/images/gallery.png')}}" class="avatar view-images rounded-circle avatar-sm" data-bs-toggle="tooltip" title="{{__('View Screenshot images')}}" data-original-title="{{__('View Screenshot images')}}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="' + item.id + '" id="track-images-' + item.id + '">' +
                            '@if (Auth::user()->type == "admin" || Auth::user()->type == "company")' +
                                '<div class="action-btn bg-danger ms-2">' +
                                    '<form method="POST" action="{{ route('tracker.destroy', ':id') }}" id="delete-form-' + item.id + '">' +
                                        '@csrf' +
                                        '@method("DELETE")' +
                                        '<a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__("Delete")}}" data-original-title="{{__("Delete")}}" data-confirm="{{__("Are You Sure?")." | ".__("This action can not be undone. Do you want to continue?")}}" data-confirm-yes="document.getElementById(\'delete-form-' + item.id + '\').submit();">' +
                                            '<i class="ti ti-trash text-white"></i>' +
                                        '</a>' +
                                    '</form>' +
                                '</div>' +
                            '@endif' +
                        '</td>' +
                        '</tr>';
                            });
                        } else {
                            tr = '<tr><td colspan="7">{{ __('No records found') }}</td></tr>';
                        }

                        $('#time-tracker-table tbody').html(tr);
                        var table = document.querySelector("#time-tracker-table");
                        var datatable = new simpleDatatables.DataTable(table, {
                            perPage: 25,
                            searchable: true, 
                        });
                    },
                    error: function (data) {
                        console.log('Error:', data);
                    }
                });
            }

            
        });
    </script>
@endpush
