@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Evaluation') }}
@endsection

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
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        border: 1px solid #e9ecef;
    }
    
    .info-section {
        background: #fff;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .info-item {
        margin-bottom: 0.75rem;
    }
    
    .info-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
        display: block;
    }
    
    .info-value {
        color: #6c757d;
        font-size: 0.9rem;
        margin: 0;
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
    
    .no-cell {
        text-align: center;
        font-weight: 700;
        background: #f8f9fa;
        color: #495057;
        width: 60px;
        min-width: 60px;
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
    
    .btn-create {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: #fff;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(40,167,69,0.3);
    }
    
    .btn-create:hover {
        background: linear-gradient(135deg, #218838 0%, #1ea97c 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(40,167,69,0.4);
        color: #fff;
    }
    
    .form-select {
        border: 2px solid #ced4da;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,.15);
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .checkmark {
        color: #28a745;
        font-size: 1.1rem;
        font-weight: bold;
    }
    
    @media (max-width: 992px) {
        .card-header .d-flex {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .btn-create {
            width: 100%;
        }
        
        .filter-section,
        .info-section {
            padding: 1rem;
        }
        
        .card-body {
            padding: 1rem;
        }
    }
    
    @media (max-width: 768px) {
        .category-cell {
            writing-mode: horizontal-tb;
            text-orientation: initial;
            min-width: auto;
            padding: 0.75rem;
        }
        
        .table thead th,
        .table tbody td {
            padding: 0.5rem 0.25rem;
            font-size: 0.75rem;
        }
        
        .score-cell,
        .score-total,
        .no-cell {
            min-width: 35px;
            width: 35px;
            padding: 0.25rem;
        }
        
        .indicator-cell {
            max-width: 150px;
        }
        
        .comment-cell {
            max-width: 100px;
            font-size: 0.7rem;
        }
    }
</style>
<style>
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

    .score-total {
        text-align: center;
        vertical-align: middle;
    }

    .category-cell {
        font-weight: bold;
        vertical-align: middle;
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
                        <h4 class="card-title mb-1 text-white">Evaluasi Performa Karyawan</h4>
                        <small class="text-white-50">KAP AGUS UBAIDILLAH DAN REKAN</small>
                    </div>
                    <button type="button" class="btn btn-create" data-bs-toggle="modal" data-bs-target="#createEvaluationModal">
                        <i class="fas fa-plus me-2"></i>Buat Penilaian
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Filter and Info Section -->
                <div class="row mb-4">
                    <!-- Filter Column -->
                    <div class="col-lg-4 mb-3">
                        <div class="filter-section">
                            <h6 class="mb-3">Filter Periode</h6>
                            <form method="GET" action="{{ route('evaluation.index') }}">
                                <label for="cw" class="form-label">Pilih Caturwulan:</label>
                                <select name="cw" id="cw" onchange="this.form.submit()" class="form-select">
                                    <option value="">Semua Periode</option>
                                    <option value="CW 1" {{ request('cw') == 'CW 1' ? 'selected' : '' }}>CW 1 (Januari–April)</option>
                                    <option value="CW 2" {{ request('cw') == 'CW 2' ? 'selected' : '' }}>CW 2 (Mei–Agustus)</option>
                                    <option value="CW 3" {{ request('cw') == 'CW 3' ? 'selected' : '' }}>CW 3 (September–Desember)</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Info Columns -->
                    <div class="col-lg-8 mb-3">
                        <div class="info-section">
                            <h6 class="mb-3">Informasi Penilaian</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-item">
                                        <label class="info-label">Nama Karyawan:</label>
                                        <p class="info-value">{{ $evaluations->first()->evaluatee->name ?? 'Tidak tersedia' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item">
                                        <label class="info-label">Penilai:</label>
                                        <p class="info-value">{{ $evaluations->first()->evaluator->name ?? 'Tidak tersedia' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item">
                                        <label class="info-label">Periode:</label>
                                        <p class="info-value">{{ $evaluations->first()->quarter ?? 'Tidak tersedia' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item">
                                        <label class="info-label">Waktu:</label>
                                        <p class="info-value">{{ $evaluations->first()->created_at ?? 'Tidak tersedia' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Evaluation Table -->
                <div class="table-container mb-5">
                    @php
                        // Ambil semua kategori dan indikator dari master
                        $masterCategories = \App\Models\Attribute::all()->groupBy('category');
                        $no = 1;

                        // Ambil penilaian pertama jika ada
                        $evaluation = $evaluations->first();
                        $details = $evaluation ? $evaluation->details->keyBy('indicator_id') : collect();
                    @endphp

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="min-width: 100px;">TATA NILAI</th>
                                    <th rowspan="2" style="min-width: 250px;">INDIKATOR</th>
                                    <th colspan="5">SKOR PENILAIAN</th>
                                    <th rowspan="2" style="min-width: 80px;">TOTAL<br>SKOR</th>
                                    <th rowspan="2" style="min-width: 150px;">KETERANGAN</th>
                                </tr>
                                <tr>
                                    <th class="score-cell">1</th>
                                    <th class="score-cell">2</th>
                                    <th class="score-cell">3</th>
                                    <th class="score-cell">4</th>
                                    <th class="score-cell">5</th>
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
                                    <td colspan="5"></td> <!-- Kolom skor penilaian kosong -->
                                    <td class="score-total fw-bold">{{ $evaluation ? $evaluation->getOverallScoreAttribute() : '—' }}</td>
                                    <td></td> <!-- Kolom keterangan kosong -->
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Tabel Hasil Akhir Per Kategori -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="category-cell">KATEGORI</th>
                                    <th class="score-total">TOTAL SKOR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalWeightedScore = 0; // Inisialisasi total skor keseluruhan
                                @endphp
                                @foreach($masterCategories as $category => $indicators)
                                    @php
                                        $weightedScore = 0;
                                        foreach ($indicators as $indicator) {
                                            $score = $details[$indicator->id]->score ?? 0;
                                            $weight = $indicator->weight ?? 0;
                                            $weightedScore += $score * ($weight / 100); // Hitung skor berbobot
                                        }
                                        $totalWeightedScore += $weightedScore; // Tambahkan ke total skor keseluruhan
                                    @endphp
                                    <tr>
                                        <td class="category-cell">{{ strtoupper($category) }}</td>
                                        <td class="score-total">{{ number_format($weightedScore, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="1" class="category-cell text-center fw-bold">TOTAL Final Score</td>
                                    <td class="score-total fw-bold">{{ number_format($totalWeightedScore, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="1" class="category-cell text-center fw-bold">Rating</td>
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
                                <!-- Baris Keterangan Rating -->
                                <tr>
                                    <td colspan="2" class="text-start">
                                        <div class="mt-3">
                                            <p class="mb-1"><strong>Keterangan Rating:</strong></p>
                                            <p class="mb-2 text-muted"><small>Rating dibulatkan ke 0.5 terdekat</small></p>
                                            <ul style="list-style-type: none; padding-left: 0;">
                                                <li><span class="star-filled">★ ★ ★ ★ ★</span> (4.75-5.0): Excellent</li>
                                                <li><span class="star-filled">★ ★ ★ ★</span><span class="star-half">★</span> (4.25-4.74): Excellent</li>
                                                <li><span class="star-filled">★ ★ ★ ★</span><span class="star-empty">★</span> (3.75-4.24): Very Good</li>
                                                <li><span class="star-filled">★ ★ ★</span><span class="star-half">★</span><span class="star-empty">★</span> (3.25-3.74): Very Good</li>
                                                <li><span class="star-filled">★ ★ ★</span><span class="star-empty">★ ★</span> (2.75-3.24): Good</li>
                                                <li><span class="star-filled">★ ★</span><span class="star-half">★</span><span class="star-empty">★ ★</span> (2.25-2.74): Good</li>
                                                <li><span class="star-filled">★ ★</span><span class="star-empty">★ ★ ★</span> (1.75-2.24): Fair</li>
                                                <li><span class="star-filled">★</span><span class="star-half">★</span><span class="star-empty">★ ★ ★</span> (1.25-1.74): Fair</li>
                                                <li><span class="star-filled">★</span><span class="star-empty">★ ★ ★ ★</span> (0.75-1.24): Poor</li>
                                                <li><span class="star-half">★</span><span class="star-empty">★ ★ ★ ★</span> (0.25-0.74): Poor</li>
                                                <li><span class="star-empty">★ ★ ★ ★ ★</span> (0.0-0.24): Poor</li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Evaluation Modal -->
    <div class="modal fade" id="createEvaluationModal" tabindex="-1" aria-labelledby="createEvaluationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createEvaluationModalLabel">Buat Penilaian Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="evaluationForm" action="{{ route('evaluation.create') }}" method="GET">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quarter" class="form-label">Periode Penilaian</label>
                                <select name="quarter" id="quarter" class="form-select" required>
                                    <option value="">Pilih Caturwulan</option>
                                    <option value="CW 1">CW 1 (Januari - April)</option>
                                    <option value="CW 2">CW 2 (Mei - Agustus)</option>
                                    <option value="CW 3">CW 3 (September - Desember)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah Karyawan Dipilih</label>
                                <div class="form-control bg-light" id="selectedCount">0 karyawan terpilih</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Pilih Karyawan yang Akan Dinilai</label>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                <div class="row" id="employeeList">
                                    @php
                                        $employees = \App\Models\User::where('id', '!=', auth()->user()->id)->where('type', '!=', 'client')->where('is_active', 1)->get();
                                    @endphp
                                    @foreach($employees as $employee)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input employee-checkbox" type="checkbox" 
                                                       name="evaluatees[]" value="{{ $employee->id }}" 
                                                       id="employee{{ $employee->id }}">
                                                <label class="form-check-label" for="employee{{ $employee->id }}">
                                                    {{ $employee->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-create" onclick="submitEvaluationForm()">Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script-page')
<script>
    // Update selected count
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.employee-checkbox');
        const selectedCount = document.getElementById('selectedCount');
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll('.employee-checkbox:checked').length;
                selectedCount.textContent = checkedCount + ' karyawan terpilih';
            });
        });
    });

    function submitEvaluationForm() {
        const quarter = document.getElementById('quarter').value;
        const checkedEmployees = document.querySelectorAll('.employee-checkbox:checked');
        
        if (!quarter) {
            alert('Silakan pilih periode penilaian terlebih dahulu.');
            return;
        }
        
        if (checkedEmployees.length === 0) {
            alert('Silakan pilih minimal satu karyawan untuk dinilai.');
            return;
        }
        
        document.getElementById('evaluationForm').submit();
    }
</script>
@endpush