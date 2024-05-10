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
<div class="modal-body">
    <div class="row mt-2">
        <table class="table datatable">
            <thead>
            <tr>
                <th>{{__('Date')}}</th>
                <th>{{__('Sick Letter')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($employee_sick as $employee_sicks)
                @php
                    $documentPath = Storage::disk('s3')->url($employee_sicks->sick_letter);
                @endphp
                <tr>
                    <td>{{ \Auth::user()->dateFormat($employee_sicks->applied_on)}}</td>
                    <td>
                        @if(!empty($employee_sicks->sick_letter))
                            <a href="{{ $documentPath }}" target="_blank">
                                {{ 'click here' }}
                            </a>
                        @else
                            <p>-</p>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg ss_modale " role="document">
        <div class="modal-content image_sider_div">

        </div>
    </div>
</div>
@push('script-page')
    <script src="{{url('js/swiper.min.js')}}"></script>
    <script type="text/javascript">



        $(document).on('click', '.view-images', function () {

                var p_url = "{{route('sick.letter.image.view')}}";
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
