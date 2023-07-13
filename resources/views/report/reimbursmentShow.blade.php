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
                <th>{{__('Client')}}</th>
                <th>{{__('Reimbursment Type')}}</th>
                <th>{{__('Amount')}}</th>
                <th>{{__('Description')}}</th>
                <th>{{__('Image')}}</th>
                <th>{{__('Status')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($employee_reimbursment as $employee_reimbursments)
                @php
                    $documentPath=asset(Storage::url($employee_reimbursments->reimbursment_image));
                @endphp
                <tr>
                    <td>{{!empty($employee_reimbursments->client->name) ? $employee_reimbursments->client->name:'-'}}</td>
                    <td>{{!empty($employee_reimbursments->reimbursment_type)?$employee_reimbursments->reimbursment_type:'-'}}</td>
                    <td>{{!empty(number_format($employee_reimbursments->amount))?number_format($employee_reimbursments->amount):'-'}}</td>
                    <td>{{!empty($employee_reimbursments->description)?$employee_reimbursments->description:'-'}}</td>
                    <td>
                                        @if(!empty($employee_reimbursments->reimbursment_image))
                                            <a href="{{$documentPath}}" target="_blank">
                                                {{$employee_reimbursments->reimbursment_image}}
                                            </a>
                                        @else
                                            <p>-</p>
                                        @endif
                                    </td>
                    <td>
                        @if($employee_reimbursments->status=="Pending")
                            <div class="status_badge badge bg-warning p-2 px-3 rounded">{{ $employee_reimbursments->status }}</div>
                        @elseif($employee_reimbursments->status=="Paid")
                            <div class="status_badge badge bg-success p-2 px-3 rounded">{{ $employee_reimbursments->status }}</div>
                        @else($employee_reimbursments->status =="Reject")
                            <div class="status_badge badge bg-danger p-2 px-3 rounded">{{ $employee_reimbursments->status }}</div>
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

                var p_url = "{{route('reimbursment-client.image.view')}}";
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
