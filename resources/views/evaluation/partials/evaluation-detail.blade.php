<div class="table-container mb-4">
    @php
        // Ambil semua kategori dan indikator dari master
        $masterCategories = \App\Models\Attribute::all()->groupBy('category');
        $details = $evaluation->details->keyBy('indicator_id');
    @endphp

    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th style="min-width: 100px;">TATA NILAI</th>
                    <th style="min-width: 250px;">INDIKATOR</th>
                    <th colspan="5" class="text-center">SKOR PENILAIAN</th>
                    <th style="min-width: 80px;">TOTAL<br>SKOR</th>
                    <th style="min-width: 150px;">KETERANGAN</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th class="score-cell">1</th>
                    <th class="score-cell">2</th>
                    <th class="score-cell">3</th>
                    <th class="score-cell">4</th>
                    <th class="score-cell">5</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($masterCategories as $category => $indicators)
                    @foreach($indicators as $i => $indicator)
                        <tr>
                            @if($i == 0)
                                <td rowspan="{{ $indicators->count() }}" class="category-cell">
                                    {{ strtoupper($category) }}
                                </td>
                            @endif
                            
                            <td class="indicator-cell">{{ $indicator->name }}</td>

                            @php
                                $score = $details[$indicator->id]->score ?? null;
                                $comment = $details[$indicator->id]->comments ?? '';
                            @endphp

                            <!-- Score Columns -->
                            @for($s = 1; $s <= 5; $s++)
                                <td class="score-cell">
                                    @if($score == $s)
                                        <span class="checkmark">✓</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endfor

                            <td class="score-total">
                                {{ $score ?? '—' }}
                            </td>
                            <td class="comment-cell">
                                {{ $comment ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                <tr>
                    <td colspan="2" class="category-cell text-center fw-bold">TOTAL KESELURUHAN</td>
                    <td colspan="5"></td>
                    <td class="score-total fw-bold">{{ $evaluation->getOverallScoreAttribute() }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Summary Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th class="category-cell">KATEGORI</th>
                    <th class="score-total">TOTAL SKOR</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalWeightedScore = 0;
                @endphp
                @foreach($masterCategories as $category => $indicators)
                    @php
                        $weightedScore = 0;
                        foreach ($indicators as $indicator) {
                            $score = $details[$indicator->id]->score ?? 0;
                            $weight = $indicator->weight ?? 0;
                            $weightedScore += $score * ($weight / 100);
                        }
                        $totalWeightedScore += $weightedScore;
                    @endphp
                    <tr>
                        <td class="category-cell">{{ strtoupper($category) }}</td>
                        <td class="score-total">{{ number_format($weightedScore, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="category-cell text-center fw-bold">TOTAL Final Score</td>
                    <td class="score-total fw-bold">{{ number_format($totalWeightedScore, 2) }}</td>
                </tr>
                <tr>
                    <td class="category-cell text-center fw-bold">Rating</td>
                    <td class="score-total">
                        @php
                            // Logika rating yang lebih akurat dengan setengah bintang
                            // Gunakan pembulatan ke 0.5 terdekat
                            $rating = round($totalWeightedScore * 2) / 2; // Membulatkan ke 0.5 terdekat
                            $fullStars = floor($rating);
                            $hasHalfStar = ($rating - $fullStars) >= 0.5;
                            $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                        @endphp
                        
                        {{-- Tampilkan bintang penuh --}}
                        @for($i = 1; $i <= $fullStars; $i++)
                            <span class="star-filled">★</span>
                        @endfor
                        
                        {{-- Tampilkan setengah bintang jika ada --}}
                        @if($hasHalfStar)
                            <span class="star-half">★</span>
                        @endif
                        
                        {{-- Tampilkan bintang kosong --}}
                        @for($i = 1; $i <= $emptyStars; $i++)
                            <span class="star-empty">★</span>
                        @endfor
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>