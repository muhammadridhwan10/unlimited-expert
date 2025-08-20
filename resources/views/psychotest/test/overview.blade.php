<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Overview - Psychotest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .test-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .test-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        .progress-container {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .progress {
            height: 20px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
        }
        .progress-bar {
            background: linear-gradient(45deg, #28a745, #20c997);
            border-radius: 10px;
        }
        .test-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .test-card:hover {
            transform: translateY(-5px);
        }
        .test-card.completed {
            background: linear-gradient(45deg, #d4edda, #c3e6cb);
            border-left: 5px solid #28a745;
        }
        .test-card.in-progress {
            background: linear-gradient(45deg, #fff3cd, #ffeaa7);
            border-left: 5px solid #ffc107;
        }
        .test-card.pending {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-left: 5px solid #6c757d;
        }
        .test-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 15px;
        }
        .test-icon.visual { background: linear-gradient(45deg, #ff6b6b, #ee5a24); }
        .test-icon.numeric { background: linear-gradient(45deg, #4834d4, #686de0); }
        .test-icon.verbal { background: linear-gradient(45deg, #00d2d3, #01a3a4); }
        .test-icon.kraeplin { background: linear-gradient(45deg, #ff9ff3, #f368e0); }
        .test-icon.standard { background: linear-gradient(45deg, #54a0ff, #2e86de); }
        
        .btn-start {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .btn-continue {
            background: linear-gradient(45deg, #ffc107, #ffb300);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-completed { background: #d4edda; color: #155724; }
        .status-in-progress { background: #fff3cd; color: #856404; }
        .status-pending { background: #f8f9fa; color: #6c757d; }
    </style>
</head>
<body>
    <div class="test-container">
        <!-- Header -->
        <div class="test-header">
            <h2 class="mb-3">Selamat Datang, {{ $schedule->candidates->name }}</h2>
            <p class="text-muted">Anda akan mengikuti serangkaian tes psikologi. Pastikan Anda dalam kondisi yang baik dan siap untuk memulai.</p>
            
            <div class="progress-container text-white">
                <h6>Progress Keseluruhan</h6>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $progress }}%"></div>
                </div>
                <small>{{ $progress }}% Selesai</small>
            </div>
        </div>

        <!-- Test Categories -->
        @foreach($categories as $category)
            @php
                $session = $sessions[$category->id] ?? null;
                $status = $session ? $session->status : 'pending';
                $isNext = $nextSession && $nextSession->category_id == $category->id;
            @endphp
            
            <div class="test-card {{ $status }} {{ $isNext ? 'border-primary' : '' }}">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <div class="test-icon {{ $category->type }}">
                            @if($category->type == 'visual')
                                <i class="fas fa-eye"></i>
                            @elseif($category->type == 'numeric')
                                <i class="fas fa-calculator"></i>
                            @elseif($category->type == 'verbal')
                                <i class="fas fa-comment"></i>
                            @elseif($category->type == 'kraeplin')
                                <i class="fas fa-plus"></i>
                            @else
                                <i class="fas fa-question"></i>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-2">{{ $category->name }}</h5>
                        <p class="text-muted mb-2">{{ $category->description }}</p>
                        <div class="d-flex align-items-center">
                            <small class="me-3">
                                <i class="fas fa-clock"></i> {{ $category->duration_minutes }} menit
                            </small>
                            <small class="me-3">
                                <i class="fas fa-question-circle"></i> {{ $category->total_questions }} soal
                            </small>
                            @if($category->type == 'kraeplin')
                                <small>
                                    <i class="fas fa-columns"></i> {{ $category->getSetting('kraeplin_columns', 10) }} kolom
                                </small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <span class="status-badge status-{{ str_replace('_', '-', $status) }}">
                            @if($status == 'completed')
                                <i class="fas fa-check"></i> Selesai
                            @elseif($status == 'in_progress')
                                <i class="fas fa-play"></i> Berlangsung
                            @else
                                <i class="fas fa-clock"></i> Menunggu
                            @endif
                        </span>
                    </div>
                    <div class="col-md-2 text-center">
                        @if($status == 'completed')
                            <button class="btn btn-success btn-sm" disabled>
                                <i class="fas fa-check"></i> Selesai
                            </button>
                        @elseif($status == 'in_progress')
                            <a href="{{ route('psychotest.test.category', $category->code) }}" class="btn btn-continue">
                                <i class="fas fa-play"></i> Lanjutkan
                            </a>
                        @elseif($isNext)
                            <a href="{{ route('psychotest.test.category', $category->code) }}" class="btn btn-start">
                                <i class="fas fa-play"></i> Mulai
                            </a>
                        @else
                            <button class="btn btn-secondary btn-sm" disabled>
                                <i class="fas fa-lock"></i> Terkunci
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Instructions -->
        <div class="test-card">
            <h5><i class="fas fa-info-circle text-info"></i> Petunjuk Umum</h5>
            <ul class="mb-0">
                <li>Setiap tes memiliki batas waktu yang berbeda</li>
                <li>Anda harus menyelesaikan tes secara berurutan</li>
                <li>Jika waktu habis, tes akan otomatis berlanjut ke tes berikutnya</li>
                <li>Pastikan koneksi internet stabil selama tes</li>
                <li>Jangan refresh atau tutup browser selama tes berlangsung</li>
            </ul>
        </div>

        <!-- Logout -->
        <div class="text-center">
            <a href="{{ route('psychotest.test.logout') }}" class="btn btn-outline-light">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>