@extends('layouts.admin')

@section('page-title')
    {{ __('Buat Penilaian') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('evaluation.index') }}">{{ __('Manage Evaluation') }}</a></li>
    <li class="breadcrumb-item">{{ __('Buat Penilaian') }}</li>
@endsection

@push('css-page')
<style>
    /* General Card Styling */
    .card {
        border: 1px solid #e0e6ed;
        border-radius: 15px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
        margin-bottom: 2.5rem;
        transition: all 0.3s ease;
        background-color: #ffffff;
    }

    .card-header {
        border-bottom: 2px solid #e0e6ed;
        padding: 1.8rem 2rem;
        background: linear-gradient(145deg, #007bff 0%, #0056b3 100%);
        border-radius: 15px 15px 0 0;
        position: relative;
        overflow: hidden;
    }

    .card-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('data:image/svg+xml;utf8,<svg width="100%" height="100%" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><pattern id="pattern-zigzag" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 0 5 L 5 10 L 10 5 L 5 0 Z" fill="rgba(255,255,255,0.1)"/></pattern><rect x="0" y="0" width="100" height="100" fill="url(%23pattern-zigzag)"/></svg>') repeat;
        opacity: 0.2;
        mix-blend-mode: overlay;
    }

    .card-body {
        padding: 2.5rem 2rem;
    }

    /* Employee Card Styling */
    .employee-card {
        border: 2px solid #e9ecef;
        border-radius: 15px;
        margin-bottom: 3.5rem;
        background: #fff;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .employee-card:not(:last-child) {
        margin-bottom: 4rem;
    }

    .employee-card:not(:last-child)::after {
        content: '';
        position: absolute;
        bottom: -2rem;
        left: 50%;
        transform: translateX(-50%);
        width: 90%;
        height: 2px;
        background: linear-gradient(90deg, transparent 0%, #dbe0e6 20%, #dbe0e6 80%, transparent 100%);
    }

    .employee-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
    }

    .employee-header {
        background: linear-gradient(145deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 12px 12px 0 0;
        margin-bottom: 0;
        position: relative;
        overflow: hidden;
    }

    .employee-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('data:image/svg+xml;utf8,<svg width="100%" height="100%" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><pattern id="pattern-dots" x="0" y="0" width="4" height="4" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1" fill="rgba(255,255,255,0.1)"/></pattern><rect x="0" y="0" width="100" height="100" fill="url(%23pattern-dots)"/></svg>') repeat;
        opacity: 0.2;
        mix-blend-mode: overlay;
    }

    .employee-header h5 {
        position: relative;
        z-index: 1;
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    .employee-header h5 .fas {
        font-size: 1.3rem;
        margin-right: 10px;
    }

    .employee-header small {
        font-size: 0.9em;
        opacity: 0.8;
    }

    .employee-body {
        padding: 2.5rem 2rem;
    }

    /* Table Styling */


    .table tbody td {
        padding: 1rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.88rem;
    }

    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .category-cell {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        font-weight: 700;
        text-align: center;
        vertical-align: middle;
        writing-mode: vertical-rl;
        text-orientation: mixed;
        color: #1976d2;
        min-width: 90px;
        padding: 1.2rem 0.8rem;
        border-right: 2px solid #2196f3;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .score-cell {
        text-align: center;
        width: 65px;
        min-width: 65px;
        padding: 0.6rem;
    }

    .indicator-cell {
        max-width: 350px;
        line-height: 1.5;
        font-weight: 500;
        color: #343a40;
    }

    .comment-cell {
        min-width: 220px;
    }

    .score-radio {
        transform: scale(1.3);
        margin: 0;
        cursor: pointer;
        transition: all 0.2s ease;
        accent-color: #007bff; /* Sets color for checked state */
    }

    .score-radio:hover {
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
    }

    /* Form Control Styling */
    .form-control {
        border: 2px solid #ced4da;
        border-radius: 10px;
        padding: 0.6rem 0.9rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.2);
        background-color: #f8faff;
    }

    textarea.form-control {
        min-height: 60px;
        resize: vertical;
        overflow: hidden; /* Hide scrollbar initially */
    }

    /* Button Group Styling */
    .btn-group-custom {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        justify-content: center;
        padding-top: 1.5rem;
    }

    .btn-submit,
    .btn-cancel {
        font-weight: 600;
        padding: 0.9rem 2.8rem;
        border-radius: 12px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        overflow: hidden;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .btn-submit {
        background: linear-gradient(145deg, #007bff 0%, #0056b3 100%);
        color: #fff;
    }

    .btn-submit::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
        z-index: 1;
    }

    .btn-submit:hover::before {
        left: 100%;
    }

    .btn-submit:hover {
        background: linear-gradient(145deg, #0056b3 0%, #004085 100%);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 123, 255, 0.4);
        color: #fff;
    }

    .btn-cancel {
        background: #6c757d;
        color: #fff;
    }

    .btn-cancel:hover {
        background: #5a6268;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(108, 117, 125, 0.4);
    }

    /* Period Information */
    .period-info {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem 2rem;
        border-radius: 15px;
        border-left: 8px solid #007bff;
        margin-bottom: 3.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .period-info .icon {
        font-size: 2.2rem;
        color: #007bff;
    }

    .period-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.3rem;
        font-size: 1rem;
    }

    .period-value {
        color: #007bff;
        font-weight: 700;
        font-size: 1.3rem;
        letter-spacing: 0.5px;
    }

    .employee-count {
        background: #e8f5e8;
        color: #155724;
        padding: 0.6rem 1.2rem;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 8px rgba(0, 128, 0, 0.1);
    }

    .employee-count .fas {
        font-size: 0.8rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 1200px) {
        .card-body, .employee-body {
            padding: 2rem 1.5rem;
        }
        .table thead th, .table tbody td {
            padding: 0.8rem 0.7rem;
            font-size: 0.85rem;
        }
        .category-cell {
            min-width: 80px;
            padding: 1rem 0.6rem;
        }
        .score-cell {
            width: 55px;
            min-width: 55px;
            padding: 0.5rem;
        }
    }

    @media (max-width: 992px) {
        .card-header, .employee-header {
            padding: 1.5rem 1.5rem;
        }
        .card-body, .employee-body {
            padding: 1.5rem;
        }
        .period-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.2rem 1.5rem;
            margin-bottom: 2.5rem;
        }
        .btn-group-custom {
            flex-direction: column;
            width: 100%;
            gap: 1rem;
        }
        .btn-submit, .btn-cancel {
            width: 100%;
            padding: 0.8rem 1.5rem;
        }
        .employee-card:not(:last-child)::after {
            bottom: -1.8rem;
            width: 85%;
        }
    }

    @media (max-width: 768px) {
        .card {
            border-radius: 10px;
        }
        .card-header {
            border-radius: 10px 10px 0 0;
            padding: 1.2rem 1rem;
        }
        .card-body {
            padding: 1rem;
        }
        .employee-card {
            border-radius: 10px;
            margin-bottom: 3rem;
        }
        .employee-header {
            border-radius: 8px 8px 0 0;
            padding: 1rem 1rem;
        }
        .employee-body {
            padding: 1.2rem 1rem;
        }
        .category-cell {
            writing-mode: horizontal-tb; /* revert to horizontal for smaller screens */
            text-orientation: initial;
            min-width: auto;
            padding: 0.75rem;
            border-right: none;
            border-bottom: 2px solid #2196f3;
        }
        .table thead th,
        .table tbody td {
            padding: 0.6rem 0.5rem;
            font-size: 0.78rem;
        }
        .score-cell {
            min-width: 40px;
            width: 40px;
            padding: 0.25rem;
        }
        .indicator-cell {
            max-width: 180px;
        }
        .comment-cell {
            min-width: 150px;
        }
        .employee-card:not(:last-child) {
            margin-bottom: 2.5rem;
        }
        .employee-card:not(:last-child)::after {
            bottom: -1.2rem;
            width: 70%;
        }
    }
    
    /* Highlight for selected radio button */
    .score-cell.selected {
        background-color: rgba(40, 167, 69, 0.15); /* Light green tint */
        transition: background-color 0.2s ease;
    }

    /* Style for validation feedback */
    .is-invalid .form-control, .is-invalid .score-radio-group {
        border-color: #dc3545 !important;
    }
    .invalid-feedback {
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="card-title mb-1 text-white">Evaluasi Performa Karyawan</h4>
                        <p class="text-white-50 mb-0">KAP AGUS UBAIDILLAH DAN REKAN</p>
                    </div>
                    <div class="employee-count mt-2 mt-md-0">
                        <i class="fas fa-users"></i>
                        {{ count($selectedEmployees) }} Karyawan Dipilih
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="period-info">
                    <i class="fas fa-calendar-alt icon"></i>
                    <div>
                        <div class="period-label">Periode Penilaian:</div>
                        <div class="period-value">{{ $quarter }}</div>
                    </div>
                </div>

                <form action="{{ route('evaluation.store') }}" method="POST" id="evaluationForm">
                    @csrf
                    <input type="hidden" name="quarter" value="{{ $quarter }}">
                    
                    @php
                        // Ambil semua kategori dan indikator dari master
                        $masterCategories = \App\Models\Attribute::all()->groupBy('category');
                    @endphp

                    @foreach($selectedEmployees as $index => $employee)
                        <div class="employee-card" data-employee-index="{{ $index }}">
                            <div class="employee-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-circle"></i>
                                    {{ $employee->name }}
                                    <small class="ms-2">({{ $employee->email }})</small>
                                </h5>
                                <div class="mt-4">
                                    <span class="badge bg-light text-dark shadow-sm">
                                        Form Penilaian {{ $index + 1 }} dari {{ count($selectedEmployees) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="employee-body">
                                <input type="hidden" name="evaluations[{{ $index }}][evaluatee_id]" value="{{ $employee->id }}">
                                
                                <div class="table-container">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2">TATA NILAI</th>
                                                    <th rowspan="2">INDIKATOR</th>
                                                    <th colspan="5">SKOR PENILAIAN</th>
                                                    <th rowspan="2">KETERANGAN</th>
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
                                                @php $detailIndex = 0; @endphp
                                                @foreach($masterCategories as $category => $indicators)
                                                    @foreach($indicators as $i => $indicator)
                                                        <tr>
                                                            @if($i == 0)
                                                                <td rowspan="{{ $indicators->count() }}" class="category-cell">
                                                                    {{ strtoupper($category) }}
                                                                </td>
                                                            @endif
                                                            
                                                            <td class="indicator-cell">{{ $indicator->name }}</td>

                                                            <input type="hidden" name="evaluations[{{ $index }}][details][{{ $detailIndex }}][indicator_id]" value="{{ $indicator->id }}">
                                                            <input type="hidden" name="evaluations[{{ $index }}][details][{{ $detailIndex }}][indicator_category]" value="{{ $category }}">
                                                            <input type="hidden" name="evaluations[{{ $index }}][details][{{ $detailIndex }}][indicator_name]" value="{{ $indicator->name }}">

                                                            @for($s = 1; $s <= 5; $s++)
                                                                <td class="score-cell">
                                                                    <input type="radio" 
                                                                           name="evaluations[{{ $index }}][details][{{ $detailIndex }}][score]" 
                                                                           value="{{ $s }}" 
                                                                           class="form-check-input score-radio"
                                                                           id="employee_{{ $employee->id }}_indicator_{{ $indicator->id }}_score_{{ $s }}"
                                                                           data-employee-index="{{ $index }}"
                                                                           data-detail-index="{{ $detailIndex }}"
                                                                           required>
                                                                </td>
                                                            @endfor

                                                            <td class="comment-cell">
                                                                <textarea name="evaluations[{{ $index }}][details][{{ $detailIndex }}][comments]" 
                                                                          class="form-control" 
                                                                          rows="2" 
                                                                          placeholder="Tulis keterangan (opsional)..."
                                                                          id="employee_{{ $employee->id }}_indicator_{{ $indicator->id }}_comment"></textarea>
                                                            </td>
                                                        </tr>
                                                        @php $detailIndex++; @endphp
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-center mt-5 pt-4" style="border-top: 2px solid #f0f2f5;">
                        <div class="btn-group-custom">
                            <a href="{{ route('evaluation.index') }}" class="btn btn-cancel">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-save me-2"></i>Simpan Semua Penilaian
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('evaluationForm');
        
        // Function to check if all radio buttons are selected
        function validateScores() {
            let isValid = true;
            const employees = {{ count($selectedEmployees) }};
            const indicatorsPerEmployee = {{ $masterCategories->flatten()->count() }};
            
            for (let i = 0; i < employees; i++) {
                for (let j = 0; j < indicatorsPerEmployee; j++) {
                    const scoreInputs = document.querySelectorAll(`input[name="evaluations[${i}][details][${j}][score]"]`);
                    const isScoreSelected = Array.from(scoreInputs).some(input => input.checked);
                    
                    if (!isScoreSelected) {
                        isValid = false;
                        // Optional: Add visual feedback for missing selection
                        // This might be better handled by a dedicated validation library or custom element styling
                        const employeeCard = document.querySelector(`.employee-card[data-employee-index="${i}"]`);
                        if (employeeCard) {
                            employeeCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            employeeCard.style.border = '2px solid #dc3545'; // Highlight card with error
                        }
                        break;
                    } else {
                        // Reset border if previously highlighted
                        const employeeCard = document.querySelector(`.employee-card[data-employee-index="${i}"]`);
                        if (employeeCard && employeeCard.style.border === '2px solid rgb(220, 53, 69)') {
                            employeeCard.style.border = '2px solid #e9ecef';
                        }
                    }
                }
                if (!isValid) break;
            }
            return isValid;
        }

        // Form submission handler
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default submission
            
            if (!validateScores()) {
                alert('Harap lengkapi semua penilaian (skor) untuk setiap indikator pada setiap karyawan sebelum menyimpan.');
                return;
            }
            
            // Show confirmation dialog
            if (confirm('Apakah Anda yakin ingin menyimpan semua penilaian ini? Data yang sudah disimpan tidak dapat diubah.')) {
                this.submit(); // If confirmed, submit the form
            }
        });
        
        // Add visual feedback for radio button selection
        const radioButtons = document.querySelectorAll('.score-radio');
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function() {
                const name = this.name;
                const allRadiosInGroup = document.querySelectorAll(`input[name="${name}"]`);
                
                // Remove highlight from all cells in the same row group
                allRadiosInGroup.forEach(r => {
                    r.closest('td').classList.remove('selected');
                });
                
                // Add highlight to the currently selected cell
                if (this.checked) {
                    this.closest('td').classList.add('selected');
                }
            });

            // Set initial highlight if a radio is pre-checked (e.g., on edit form)
            if (radio.checked) {
                radio.closest('td').classList.add('selected');
            }
        });
        
        // Auto-resize textareas
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto'; // Reset height to recalculate
                this.style.height = (this.scrollHeight) + 'px';
            });
            // Also resize on initial load in case there's pre-filled content
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        });
    });
</script>
@endpush