<div class="modal-header pb-2 pt-2">
    <h5 class="modal-title" id="exampleModalLongTitle">{{ $file->name . ' File' }}</h5>
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
                            @if(in_array(pathinfo($image->file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']))
                                <div class="swiper-slide" id="slide-{{ $image->id }}">
                                    <img src="{{ Storage::disk('s3')->url($image->file) }}" alt="..." class="img-fluid">
                                    <div class="text-center mt-2">
                                        <a href="{{ Storage::disk('s3')->temporaryUrl($image->file, now()->addMinutes(5), ['ResponseContentDisposition' => 'attachment']) }}" class="btn btn-primary mt-2">
                                            Download Image
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="swiper-slide" id="slide-{{ $image->id }}">
                                    <iframe src="{{ Storage::disk('s3')->url($image->file) }}" width="100%" height="500px" style="border: none;"></iframe>
                                </div>
                            @endif
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
