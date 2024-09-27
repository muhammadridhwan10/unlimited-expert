<div class="modal-body">
    <div class="row">

        <!-- Work Target Achievement (Form A1 & A2) -->
        <div class="col-6">
             <h6>{{ 'Work Target Achievement (Form A1 & A2)' }}</h6>
        </div>

        <div class="col-6 text-end mb-3">
            @php
                $starsA = floor($finalA);
                $halfStarA = $finalA - $starsA >= 0.5 ? true : false;
            @endphp
            <div class="star-rating">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $starsA)
                        <span class="fa fa-star checked"></span> 
                    @elseif($i == $starsA + 1 && $halfStarA)
                        <span class="fa fa-star-half-alt checked"></span>
                    @else
                        <span class="fa fa-star"></span> 
                    @endif
                @endfor
            </div>
        </div>

        <!-- Core Competencies (Form C) -->
        <div class="col-6">
             <h6>{{ 'Core Competencies (Form C)' }}</h6>
        </div>

        <div class="col-6 text-end mb-3">
            @php
                $starsC = floor($finalC);
                $halfStarC = $finalC - $starsC >= 0.5 ? true : false;
            @endphp
            <div class="star-rating">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $starsC)
                        <span class="fa fa-star checked"></span>
                    @elseif($i == $starsC + 1 && $halfStarC)
                        <span class="fa fa-star-half-alt checked"></span>
                    @else
                        <span class="fa fa-star"></span>
                    @endif
                @endfor
            </div>
        </div>

        <!-- Managerial Competence (Form D) -->
        <div class="col-6">
             <h6>{{ 'Managerial Competence (Form D)' }}</h6>
        </div>

        <div class="col-6 text-end mb-3">
            @php
                $starsD = floor($finalD);
                $halfStarD = $finalD - $starsD >= 0.5 ? true : false;
            @endphp
            <div class="star-rating">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $starsD)
                        <span class="fa fa-star checked"></span>
                    @elseif($i == $starsD + 1 && $halfStarD)
                        <span class="fa fa-star-half-alt checked"></span>
                    @else
                        <span class="fa fa-star"></span>
                    @endif
                @endfor
            </div>
        </div>

        <!-- Managerial Competence (Form D) -->
        <div class="col-6">
             <h6>{{ 'Total Final Score' }}</h6>
        </div>

        <div class="col-6 text-end mb-3">
            @php
                $starsFinal = floor($totalFinal);
                $halfStarFinal = $totalFinal - $starsFinal >= 0.5 ? true : false;
            @endphp
            <div class="star-rating">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $starsFinal)
                        <span class="fa fa-star checked"></span>
                    @elseif($i == $starsFinal + 1 && $halfStarFinal)
                        <span class="fa fa-star-half-alt checked"></span>
                    @else
                        <span class="fa fa-star"></span>
                    @endif
                @endfor
            </div>
        </div>
    </div>
</div>

<style>
    .star-rating .fa-star, .star-rating .fa-star-half-alt {
        font-size: 20px;
        color: #ccc;
    }
    .star-rating .fa-star.checked, .star-rating .fa-star-half-alt.checked {
        color: #ffcc00;
    }
</style>
