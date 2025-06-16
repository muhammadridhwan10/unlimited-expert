@extends('layouts.admin')

@section('page-title')
    {{ $pageTitle }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ $pageTitle }}</li>
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
        background: linear-gradient(135deg, #6f42c1 0%, #8e24aa 100%);
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
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        padding: 1rem;
        border-radius: 8px 8px 0 0;
    }
    
    .view-toggle {
        background: #fff;
        border-radius: 8px;
        padding: 0.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .view-toggle .nav-pills .nav-link {
        border-radius: 6px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        color: #6c757d;
        transition: all 0.3s ease;
    }
    
    .view-toggle .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #6f42c1 0%, #8e24aa 100%);
        color: white;
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
        border-color: #6f42c1;
        box-shadow: 0 0 0 0.25rem rgba(111,66,193,.15);
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        display: block;
    }
    
    .btn-filter, .btn-reset {
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
    
    @media (max-width: 768px) {
        .filter-section {
            padding: 1.5rem;
        }
        
        .filter-row {
            flex-direction: column;
            gap: 1rem;
        }
        
        .filter-col, 
        .filter-col.filter-buttons {
            flex: none;
            width: 100%;
            min-width: auto;
        }
        
        .filter-col.filter-buttons {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .btn-filter, .btn-reset {
            width: 100%;
            min-width: auto;
        }
        
        .view-toggle .nav-pills .nav-link {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
    }
</style>
@endpush

@section('content')
    <div class="container-fluid mt-4">
        <!-- View Toggle -->
        <div class="view-toggle">
            <ul class="nav nav-pills justify-content-center">
                <li class="nav-item">
                    <a class="nav-link {{ $viewMode === 'evaluated' ? 'active' : '' }}" 
                       href="{{ route('evaluation.index') }}?view=evaluated">
                        <i class="fas fa-clipboard-list me-2"></i>Evaluasi yang Saya Nilai
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $viewMode === 'my_results' ? 'active' : '' }}" 
                       href="{{ route('evaluation.index') }}?view=my_results">
                        <i class="fas fa-user-check me-2"></i>Hasil Evaluasi Saya
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1 text-white">{{ $pageTitle }}</h4>
                        <small class="text-white-50">KAP AGUS UBAIDILLAH DAN REKAN</small>
                    </div>
                    <div class="text-white">
                        <i class="fas fa-chart-line me-2"></i>
                        Total Evaluasi: {{ $evaluations->count() }}
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                @if($showFilters)
                    <!-- Filter Section -->
                    <div class="filter-section">
                        <h6 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Evaluasi</h6>
                        <form method="GET" action="{{ route('evaluation.index') }}">
                            <input type="hidden" name="view" value="{{ $viewMode }}">
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
                                    <a href="{{ route('evaluation.index') }}?view={{ $viewMode }}" class="btn-reset">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Simple Filter for My Results -->
                    <div class="filter-section">
                        <h6 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Periode</h6>
                        <form method="GET" action="{{ route('evaluation.index') }}">
                            <input type="hidden" name="view" value="{{ $viewMode }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="cw" class="form-label">Pilih Caturwulan</label>
                                    <select name="cw" id="cw" onchange="this.form.submit()" class="form-select">
                                        <option value="">Semua Periode</option>
                                        <option value="CW 1" {{ request('cw') == 'CW 1' ? 'selected' : '' }}>CW 1 (Januari–April)</option>
                                        <option value="CW 2" {{ request('cw') == 'CW 2' ? 'selected' : '' }}>CW 2 (Mei–Agustus)</option>
                                        <option value="CW 3" {{ request('cw') == 'CW 3' ? 'selected' : '' }}>CW 3 (September–Desember)</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif

                @if($evaluations->count() > 0)
                    @if($viewMode === 'my_results')
                        {{-- View untuk hasil evaluasi penilai sendiri --}}
                        @foreach($evaluations as $evaluation)
                            <div class="evaluation-card">
                                <div class="evaluation-header">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Nama Karyawan:</strong><br>
                                            <i class="fas fa-user me-2"></i>{{ $evaluation->evaluatee->name }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Penilai:</strong><br>
                                            <i class="fas fa-user-tie me-2"></i>{{ $evaluation->evaluator->name }}
                                        </div>
                                        <div class="col-md-2">
                                            <strong>Periode:</strong><br>
                                            <i class="fas fa-calendar me-2"></i>{{ $evaluation->quarter }}
                                        </div>
                                        <div class="col-md-2">
                                            <strong>Tanggal:</strong><br>
                                            <i class="fas fa-clock me-2"></i>{{ $evaluation->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    {{-- Include detailed evaluation view --}}
                                    @include('evaluation.partials.evaluation-detail', ['evaluation' => $evaluation])
                                </div>
                            </div>
                        @endforeach
                    @else
                        {{-- View untuk evaluasi yang dinilai oleh penilai --}}
                        @foreach($evaluations as $evaluation)
                            <div class="evaluation-card">
                                <div class="evaluation-header">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Branch:</strong><br>
                                            <i class="fas fa-building me-2"></i>{{ $evaluation->evaluatee->employee->branch->name ?? 'Tidak Ada Branch' }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Karyawan:</strong><br>
                                            <i class="fas fa-user me-2"></i>{{ $evaluation->evaluatee->name }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Periode:</strong><br>
                                            <i class="fas fa-calendar me-2"></i>{{ $evaluation->quarter }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Tanggal:</strong><br>
                                            <i class="fas fa-clock me-2"></i>{{ $evaluation->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    {{-- Include detailed evaluation view --}}
                                    @include('evaluation.partials.evaluation-detail', ['evaluation' => $evaluation])
                                </div>
                            </div>
                        @endforeach
                    @endif
                    
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
                        @if($viewMode === 'my_results')
                            <p class="text-muted">Belum ada evaluasi untuk Anda yang sesuai dengan filter yang dipilih.</p>
                        @else
                            <p class="text-muted">Belum ada evaluasi yang Anda nilai sesuai dengan filter yang dipilih.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection