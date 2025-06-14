
<div class="modal-header pb-2 pt-2">
    <h5 class="modal-title" id="exampleModalLongTitle">{{ $reimbursment->reimbursment_type}} <small>({{date('d-M-Y',strtotime($reimbursment->date))}} )</small></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

        <span aria-hidden="true">&times;</span>
    </button>
  </div>
<div class="modal-body p-1">
    <div class="row ">
    <div class="col-lg-12 product-left mb-5 mb-lg-0">
        @if( $images->count() > 0)
            <div class="swiper-container product-slider mb-2 pb-2" style="border-bottom:solid 2px #f2f3f5">
                <div class="swiper-wrapper">
                    @foreach ($images as $image)
                        <div class="swiper-slide" id="slide-{{ $image->id }}">
                            <img src="{{ Storage::disk('minio')->url($image->reimbursment_image) }}" alt="..." class="img-fluid">
                            <div class="text-center mt-2">
                                <a href="{{ Storage::disk('minio')->temporaryUrl($image->reimbursment_image, now()->addMinutes(5), ['ResponseContentDisposition' => 'attachment']) }}" class="btn btn-primary mt-2">
                                    Download Image
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
        <div class="no-image">
            <h5 class="text-muted">Images Not Available .</h5>
        </div>
        @endif
    </div>
    </div>
</div>
