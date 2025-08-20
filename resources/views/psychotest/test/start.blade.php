{{-- resources/views/psychotest/test/start.blade.php - Full Screen Instructions --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Psychotest - Ready to Start</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .fullscreen-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 90%;
            margin: 20px;
            animation: slideInUp 0.8s ease-out;
        }
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }
        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        .header-content {
            position: relative;
            z-index: 2;
        }
        .content-section {
            padding: 50px;
        }
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .category-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        .category-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin: 0 auto 15px;
        }
        .category-icon.visual { background: linear-gradient(45deg, #ff6b6b, #ee5a24); }
        .category-icon.numeric { background: linear-gradient(45deg, #4834d4, #686de0); }
        .category-icon.verbal { background: linear-gradient(45deg, #00d2d3, #01a3a4); }
        .category-icon.kraeplin { background: linear-gradient(45deg, #ff9ff3, #f368e0); }
        .category-icon.standard { background: linear-gradient(45deg, #54a0ff, #2e86de); }
        .category-icon.field { background: linear-gradient(45deg, #54a0ff, #2e86de); }
        .category-icon.epps { background: linear-gradient(45deg, #54a0ff, #2e86de); }
        
        .instructions-section {
            background: #fff3cd;
            border: 2px solid #ffeaa7;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
        }
        .instructions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .instruction-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .instruction-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .start-button {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 20px 60px;
            border-radius: 50px;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .start-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(40, 167, 69, 0.4);
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        }
        .start-button:active {
            transform: translateY(-1px);
        }
        .warning-section {
            background: #f8d7da;
            border: 2px solid #f5c2c7;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
        }
        .stats-row {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
            flex-wrap: wrap;
            gap: 20px;
        }
        .stat-item {
            text-align: center;
            flex: 1;
            min-width: 150px;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            display: block;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .logout-link {
            position: absolute;
            top: 20px;
            right: 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .logout-link:hover {
            color: white;
            background: rgba(255,255,255,0.2);
        }
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (max-width: 768px) {
            .content-section {
                padding: 30px 20px;
            }
            .header-section {
                padding: 30px 20px;
            }
            .start-button {
                padding: 15px 40px;
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="fullscreen-container">
        <!-- Logout Link -->
        <a href="{{ route('psychotest.test.logout') }}" class="logout-link">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>

        <div class="welcome-card">
            <!-- Header Section -->
            <div class="header-section">
                <div class="header-content">
                    <h1 class="mb-3">
                        <i class="fas fa-brain me-3"></i>
                        Selamat Datang di Psychotest
                    </h1>
                    <h3 class="mb-4">{{ $schedule->candidates->name }}</h3>
                    <p class="lead mb-0">
                        Anda akan mengikuti serangkaian tes psikologi untuk mengevaluasi kemampuan kognitif dan kepribadian Anda.
                    </p>
                </div>
            </div>

            <!-- Content Section -->
            <div class="content-section">
                <!-- Test Statistics -->
                <div class="stats-row">
                    <div class="stat-item">
                        <span class="stat-number">{{ $categories->count() }}</span>
                        <span class="stat-label">Test Categories</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">{{ $categories->sum('total_questions') }}</span>
                        <span class="stat-label">Total Questions</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">{{ $categories->sum('duration_minutes') }}</span>
                        <span class="stat-label">Total Minutes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">{{ $schedule->end_time->format('H:i') }}</span>
                        <span class="stat-label">Must Finish By</span>
                    </div>
                </div>

                <!-- Test Categories Overview -->
                <h4 class="text-center mb-4">Test Categories</h4>
                <div class="category-grid">
                    @foreach($categories as $index => $category)
                    <div class="category-card">
                        <div class="category-icon {{ $category->type }}">
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
                        <h5 class="mb-2">Test {{ $index + 1 }}: {{ $category->name }}</h5>
                        <p class="text-muted mb-2">{{ $category->description }}</p>
                        <div class="d-flex justify-content-between text-sm">
                            <span><i class="fas fa-clock text-primary"></i> {{ $category->duration_minutes }} min</span>
                            <span><i class="fas fa-question-circle text-info"></i> {{ $category->total_questions }} soal</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Instructions -->
                <div class="instructions-section">
                    <h4 class="text-center mb-4">
                        <i class="fas fa-info-circle text-warning"></i>
                        Petunjuk Penting
                    </h4>
                    <div class="instructions-grid">
                        <div class="instruction-item">
                            <div class="instruction-icon">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Perangkat & Koneksi</h6>
                                <small>Gunakan laptop/komputer dengan koneksi internet stabil. Pastikan kamera dan speaker aktif.</small>
                            </div>
                        </div>
                        
                        <div class="instruction-item">
                            <div class="instruction-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Waktu Terbatas</h6>
                                <small>Setiap tes memiliki batas waktu. Jika waktu habis, tes akan otomatis lanjut ke kategori berikutnya.</small>
                            </div>
                        </div>

                        <div class="instruction-item">
                            <div class="instruction-icon">
                                <i class="fas fa-mouse-pointer"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Navigasi Bebas</h6>
                                <small>Anda dapat berpindah-pindah soal dalam satu kategori test. Jawaban tersimpan otomatis.</small>
                            </div>
                        </div>

                        <div class="instruction-item">
                            <div class="instruction-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Identitas</h6>
                                <small>Siapkan KTP/SIM/Kartu Pelajar untuk verifikasi identitas jika diminta.</small>
                            </div>
                        </div>

                        <div class="instruction-item">
                            <div class="instruction-icon">
                                <i class="fas fa-ban"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Larangan</h6>
                                <small>Dilarang menggunakan kalkulator, mencari jawaban online, atau meminta bantuan orang lain.</small>
                            </div>
                        </div>

                        <div class="instruction-item">
                            <div class="instruction-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Konsentrasi</h6>
                                <small>Pastikan berada di ruangan yang tenang tanpa gangguan selama mengerjakan tes.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning Section -->
                <div class="warning-section">
                    <h5 class="text-danger mb-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        Peringatan Penting
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="text-start list-unstyled">
                                <li class="mb-2"><i class="fas fa-times-circle text-danger me-2"></i>Jangan refresh atau tutup browser selama tes</li>
                                <li class="mb-2"><i class="fas fa-times-circle text-danger me-2"></i>Jangan gunakan tombol back/forward browser</li>
                                <li class="mb-2"><i class="fas fa-times-circle text-danger me-2"></i>Setelah submit, jawaban tidak dapat diubah</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="text-start list-unstyled">
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Jawaban tersimpan otomatis</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Dapat berpindah antar soal dalam kategori</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Progress test dapat dilihat real-time</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Start Button -->
                <div class="text-center">
                    <form action="{{ route('psychotest.test.start-test') }}" method="POST" style="display: inline-block;">
                        @csrf
                        <button type="submit" class="start-button">
                            <i class="fas fa-play me-3"></i>
                            Mulai Tes Sekarang
                        </button>
                    </form>
                    
                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Pastikan Anda sudah siap sebelum menekan tombol "Mulai Tes"
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add loading state to start button
        document.getElementById('startBtn').addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-3"></i>Starting Test...';
            this.disabled = true;
        });

        // Prevent accidental page refresh
        window.addEventListener('beforeunload', function(e) {
            // Only show warning if user hasn't started the test yet
            if (!document.getElementById('startBtn').disabled) {
                e.preventDefault();
                e.returnValue = 'Are you sure you want to leave? Your test session may be lost.';
            }
        });

        // Add some interactive effects
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>