<div class="modal-body text-center">
    <h4>Total Final Score</h4>
    <div class="star-rating-container">

        <div class="star-rating">
            <span class="fa fa-star checked"></span>
        </div>

        <div class="score-label">
            <strong>{{ number_format($totalFinal, 2) }}</strong> 
        </div>
    </div>
</div>

<style>

    .star-rating-container {
        display: flex;
        justify-content: center;
        align-items: center;
    }


    .star-rating .fa-star {
        font-size: 70px; 
        color: #ffcc00; 
        margin-right: 15px; 


    .score-label {
        font-size: 50px;
        color: #333; 
    }
</style>
