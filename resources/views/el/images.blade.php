<div class="modal-header pb-2 pt-2">
    <h5 class="modal-title" id="exampleModalLongTitle">{{ 'El ' . $file->el_number }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body p-1">
    <div class="row">
        <div class="col-lg-12 product-left mb-5 mb-lg-0">
            @foreach ($images as $image)
                @if($image->file != NULL)
                    <div class="swiper-container product-slider mb-2 pb-2" style="border-bottom:solid 2px #f2f3f5">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide" id="slide-{{ $image->id }}">
                                <iframe src="{{ Storage::disk('s3')->url($image->file) }}" width="100%" height="500px" style="border: none;"></iframe>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="no-image">
                        <h5 class="text-muted text-center">File Not Available.</h5>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
