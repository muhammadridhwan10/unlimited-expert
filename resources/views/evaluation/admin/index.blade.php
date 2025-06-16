@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Evaluation - Admin') }}
@endsection

@push('script-page')
<script>
    function exportToExcel() {
        // Ambil nilai filter saat ini
        const user_id = document.getElementById('user_id').value;
        const branch_id = document.getElementById('branch_id').value;
        const evaluator_id = document.getElementById('evaluator_id').value;
        const cw = document.getElementById('cw').value;
        
        // Buat URL dengan parameter filter
        let exportUrl = '{{ route("evaluation.export") }}?';
        const params = [];
        
        if (user_id) params.push('user_id=' + user_id);
        if (branch_id) params.push('branch_id=' + branch_id);
        if (evaluator_id) params.push('evaluator_id=' + evaluator_id);
        if (cw) params.push('cw=' + encodeURIComponent(cw));
        
        exportUrl += params.join('&');
        
        // Redirect ke URL export
        window.location.href = exportUrl;
    }
</script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Manage Evaluation') }}</li>
@endsection

@push('css-page')
<style>
    .card {
        border: 1px solid #dee2e6;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }
    
    .card-header {
        border-bottom: 2px solid #dee2e6;
        padding: 1.5rem;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-radius: 12px 12px 0 0;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    .filter-section {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: end;
    }
    
    .filter-col {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-col.filter-buttons {
        flex: 0 0 auto;
        min-width: auto;
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }
    
    .form-select {
        border: 2px solid #ced4da;
        border-radius: 8px;
        padding: 0.875rem 1rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        height: 50px;
        background-color: #fff;
    }
    
    .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,.15);
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        display: block;
    }
    
    .btn-filter, .btn-reset, .btn-export {
        height: 50px;
        padding: 0 1.5rem;
        font-weight: 600;
        border-radius: 8px;
        border: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 110px;
        font-size: 0.9rem;
    }
    
    .btn-filter {
        background: linear-gradient(135deg, #6f42c1 0%, #8e24aa 100%);
        color: #fff;
    }
    
    .btn-filter:hover {
        background: linear-gradient(135deg, #5a2d8c 0%, #7b1fa2 100%);
        color: #fff;
        transform: translateY(-1px);
    }
    
    .btn-reset {
        background: #6c757d;
        color: #fff;
    }
    
    .btn-reset:hover {
        background: #545b62;
        color: #fff;
        transform: translateY(-1px);
    }
    
    .btn-export {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #fff;
    }
    
    .btn-export:hover {
        background: linear-gradient(135deg, #218838 0%, #1ea97c 100%);
        color: #fff;
        transform: translateY(-1px);
    }
    
    @media (max-width: 1200px) {
        .filter-col {
            min-width: 180px;
        }
        
        .filter-col.filter-buttons {
            flex: 1 1 100%;
            justify-content: flex-start;
            margin-top: 0.5rem;
        }
    }
    
    .evaluation-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .evaluation-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .evaluation-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 1rem;
        border-radius: 8px 8px 0 0;
    }
    
    .score-cell {
        text-align: center;
        font-weight: 600;
        color: #28a745;
        width: 45px;
        min-width: 45px;
        padding: 0.5rem;
    }
    
    .score-total {
        text-align: center;
        font-weight: 700;
        color: #dc3545;
        background: #fff3cd;
        width: 80px;
        min-width: 80px;
        font-size: 0.9rem;
    }
    
    .category-cell {
        font-weight: bold;
        vertical-align: middle;
        background: #f8f9fa;
    }
    
    .indicator-cell {
        max-width: 300px;
        line-height: 1.4;
    }
    
    .comment-cell {
        max-width: 200px;
        word-wrap: break-word;
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .checkmark {
        color: #28a745;
        font-size: 1.1rem;
        font-weight: bold;
    }
    
    .star-filled {
        color: gold;
        font-size: 1.2em;
    }

    .star-empty {
        color: #ccc;
        font-size: 1.2em;
    }
    
    .star-half {
        position: relative;
        color: #ccc;
        font-size: 1.2em;
    }
    
    .star-half::before {
        content: "★";
        position: absolute;
        left: 0;
        width: 50%;
        overflow: hidden;
        color: gold;
    }
    
    .no-evaluation {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid mt-4">
        <!-- Main Card -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1 text-white">Evaluasi Performa Karyawan - Admin Panel</h4>
                        <small class="text-white-50">KAP AGUS UBAIDILLAH DAN REKAN</small>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-chart-line me-2"></i>
                        Total Evaluasi: {{ $evaluations->count() }}
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Filter Section -->
                <div class="filter-section">
                    <h6 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Evaluasi</h6>
                    <form method="GET" action="{{ route('evaluation.index') }}">
                        <div class="filter-row">
                            <div class="filter-col">
                                <label for="user_id" class="form-label">Pilih Karyawan</label>
                                <select name="user_id" id="user_id" class="form-select">
                                    <option value="">Semua Karyawan</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="filter-col">
                                <label for="branch_id" class="form-label">Pilih Branch</label>
                                <select name="branch_id" id="branch_id" class="form-select">
                                    <option value="">Semua Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="filter-col">
                                <label for="evaluator_id" class="form-label">Pilih Penilai</label>
                                <select name="evaluator_id" id="evaluator_id" class="form-select">
                                    <option value="">Semua Penilai</option>
                                    @foreach($evaluators as $evaluator)
                                        <option value="{{ $evaluator->id }}" {{ request('evaluator_id') == $evaluator->id ? 'selected' : '' }}>
                                            {{ $evaluator->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="filter-col">
                                <label for="cw" class="form-label">Pilih Caturwulan</label>
                                <select name="cw" id="cw" class="form-select">
                                    <option value="">Semua Periode</option>
                                    <option value="CW 1" {{ request('cw') == 'CW 1' ? 'selected' : '' }}>CW 1 (Jan–Apr)</option>
                                    <option value="CW 2" {{ request('cw') == 'CW 2' ? 'selected' : '' }}>CW 2 (Mei–Ags)</option>
                                    <option value="CW 3" {{ request('cw') == 'CW 3' ? 'selected' : '' }}>CW 3 (Sep–Des)</option>
                                </select>
                            </div>
                            
                            <div class="filter-col filter-buttons">
                                <button type="submit" class="btn-filter">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                                <a href="{{ route('evaluation.index') }}" class="btn-reset">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </a>
                                <button type="button" class="btn-export" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel me-2"></i>Export
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                @if($evaluations->count() > 0)
                    @foreach($evaluations as $evaluation)
                        <div class="evaluation-card">
                            <div class="evaluation-header">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Karyawan:</strong><br>
                                        <i class="fas fa-user me-2"></i>{{ $evaluation->evaluatee->name }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Penilai:</strong><br>
                                        <i class="fas fa-user-tie me-2"></i>{{ $evaluation->evaluator->name }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Periode:</strong><br>
                                        <i class="fas fa-calendar me-2"></i>{{ $evaluation->quarter }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Tanggal Evaluasi:</strong><br>
                                        <i class="fas fa-calendar me-2"></i>{{ $evaluation->created_at }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <!-- Evaluation Table -->
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
                                                                {{ $comment ?: 'Tidak ada keterangan' }}
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
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Rating Legend -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="mb-3">Keterangan Rating:</h6>
                            <p class="mb-2 text-muted"><small>Rating dibulatkan ke 0.5 terdekat</small></p>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul style="list-style-type: none; padding-left: 0;">
                                        <li class="mb-2"><span class="star-filled">★ ★ ★ ★ ★</span> (4.75-5.0): Excellent</li>
                                        <li class="mb-2"><span class="star-filled">★ ★ ★ ★</span><span class="star-half">★</span> (4.25-4.74): Excellent</li>
                                        <li class="mb-2"><span class="star-filled">★ ★ ★ ★</span><span class="star-empty">★</span> (3.75-4.24): Very Good</li>
                                        <li class="mb-2"><span class="star-filled">★ ★ ★</span><span class="star-half">★</span><span class="star-empty">★</span> (3.25-3.74): Very Good</li>
                                        <li class="mb-2"><span class="star-filled">★ ★ ★</span><span class="star-empty">★ ★</span> (2.75-3.24): Good</li>
                                        <li class="mb-2"><span class="star-filled">★ ★</span><span class="star-half">★</span><span class="star-empty">★ ★</span> (2.25-2.74): Good</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul style="list-style-type: none; padding-left: 0;">
                                        <li class="mb-2"><span class="star-filled">★ ★</span><span class="star-empty">★ ★ ★</span> (1.75-2.24): Fair</li>
                                        <li class="mb-2"><span class="star-filled">★</span><span class="star-half">★</span><span class="star-empty">★ ★ ★</span> (1.25-1.74): Fair</li>
                                        <li class="mb-2"><span class="star-filled">★</span><span class="star-empty">★ ★ ★ ★</span> (0.75-1.24): Poor</li>
                                        <li class="mb-2"><span class="star-half">★</span><span class="star-empty">★ ★ ★ ★</span> (0.25-0.74): Poor</li>
                                        <li class="mb-2"><span class="star-empty">★ ★ ★ ★ ★</span> (0.0-0.24): Poor</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="no-evaluation">
                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                        <h5>Tidak ada data evaluasi</h5>
                        <p class="text-muted">Belum ada evaluasi yang sesuai dengan filter yang dipilih.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection