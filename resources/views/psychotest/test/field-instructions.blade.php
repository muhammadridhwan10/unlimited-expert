{{-- resources/views/psychotest/test/field-instructions.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instruksi Tes Bidang - {{ $category->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .instruction-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 30px;
            margin: 20px;
            border-radius: 25px;
            text-align: center;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
        }
        
        .instructions-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            margin: 20px;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        
        .example-container {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #22c55e;
            border-radius: 20px;
            padding: 30px;
            margin: 25px 0;
        }
        
        .topic-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        
        .topic-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
            transition: transform 0.3s ease;
        }
        
        .topic-card:hover {
            transform: translateY(-5px);
        }
        
        .difficulty-indicator {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            justify-content: center;
        }
        
        .difficulty-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .difficulty-easy { background: #d1fae5; color: #065f46; }
        .difficulty-medium { background: #fef3c7; color: #92400e; }
        .difficulty-hard { background: #fee2e2; color: #991b1b; }
        
        .sample-question {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        
        .option-example {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .option-example:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .option-example.correct {
            border-color: #22c55e;
            background: #f0fdf4;
        }
        
        .start-section {
            text-align: center;
            margin-top: 50px;
            padding: 40px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 25px;
            border: 2px solid #0ea5e9;
        }
        
        .start-button {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border: none;
            color: white;
            padding: 20px 50px;
            border-radius: 50px;
            font-size: 1.3rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 15px 40px rgba(14, 165, 233, 0.4);
            text-decoration: none;
            display: inline-block;
        }
        
        .start-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 50px rgba(14, 165, 233, 0.6);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="instruction-header">
        <h2><i class="fas fa-briefcase me-3"></i>{{ $category->name }}</h2>
        <p class="lead mb-0">Tes khusus untuk menguji kemampuan teknis bidang auditor, perpajakan, dan akuntansi</p>
    </div>

    <!-- Instructions Container -->
    <div class="instructions-container">
        <!-- About Field Test -->
        <div class="instruction-section">
            <h3 class="mb-4"><i class="fas fa-info-circle text-primary me-2"></i>Tentang Tes Bidang</h3>
            <p class="lead">
                Tes ini dirancang khusus untuk mengukur kemampuan teknis dan pengetahuan professional dalam bidang 
                auditing, perpajakan, dan akuntansi sesuai dengan standar industri terkini.
            </p>
        </div>

        <!-- Test Coverage -->
        <div class="instruction-section">
            <h3 class="mb-4"><i class="fas fa-list-check text-success me-2"></i>Cakupan Materi Tes</h3>
            <div class="topic-grid">
                <div class="topic-card">
                    <h6><i class="fas fa-search-dollar text-primary"></i> Prosedur Audit</h6>
                    <small>Teknik audit, sampling, dokumentasi, dan pelaporan audit</small>
                </div>
                <div class="topic-card">
                    <h6><i class="fas fa-calculator text-info"></i> Perhitungan Pajak</h6>
                    <small>PPh, PPN, perhitungan kewajiban pajak, dan perencanaan pajak</small>
                </div>
                <div class="topic-card">
                    <h6><i class="fas fa-chart-line text-success"></i> Akuntansi Keuangan</h6>
                    <small>Penyusunan laporan keuangan, jurnal, dan analisis transaksi</small>
                </div>
                <div class="topic-card">
                    <h6><i class="fas fa-shield-alt text-warning"></i> Pengendalian Internal</h6>
                    <small>Sistem pengendalian, risiko, dan compliance</small>
                </div>
                <div class="topic-card">
                    <h6><i class="fas fa-magnifying-glass-chart text-danger"></i> Analisis Keuangan</h6>
                    <small>Rasio keuangan, interpretasi laporan, dan penilaian kinerja</small>
                </div>
                <div class="topic-card">
                    <h6><i class="fas fa-industry text-secondary"></i> Akuntansi Biaya</h6>
                    <small>Costing, budgeting, dan analisis varians</small>
                </div>
                <div class="topic-card">
                    <h6><i class="fas fa-gavel text-dark"></i> Hukum Pajak</h6>
                    <small>Regulasi perpajakan, kepatuhan, dan sanksi</small>
                </div>
                <div class="topic-card">
                    <h6><i class="fas fa-file-contract text-info"></i> Pelaporan Audit</h6>
                    <small>Format laporan, opini audit, dan komunikasi hasil</small>
                </div>
            </div>
        </div>

        <!-- Difficulty Levels -->
        <div class="instruction-section">
            <h3 class="mb-4"><i class="fas fa-layer-group text-warning me-2"></i>Tingkat Kesulitan</h3>
            <p class="mb-3">Soal tes terdiri dari 3 tingkat kesulitan:</p>
            <div class="difficulty-indicator">
                <div class="difficulty-badge difficulty-easy">
                    <i class="fas fa-star"></i> Easy (30%) - Konsep dasar
                </div>
                <div class="difficulty-badge difficulty-medium">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i> Medium (50%) - Aplikasi praktis
                </div>
                <div class="difficulty-badge difficulty-hard">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i> Hard (20%) - Analisis kompleks
                </div>
            </div>
        </div>

        <!-- Sample Questions -->
        {{-- @if($sampleQuestions && $sampleQuestions->count() > 0)
        <div class="example-container">
            <h4 class="text-center mb-4"><i class="fas fa-eye text-primary"></i> Contoh Soal Tes Bidang</h4>
            
            @foreach($sampleQuestions as $index => $question)
            <div class="sample-question">
                <div class="d-flex justify-content-between mb-2">
                    <h6>Contoh {{ $index + 1 }}: {{ $question->getFieldTopicName() }}</h6>
                    <span class="badge bg-{{ $question->getDifficultyColorClass() }}">
                        {{ ucfirst($question->difficulty_level ?? 'medium') }}
                    </span>
                </div>
                <p class="mb-3"><strong>{{ $question->title }}</strong></p>
                <p class="text-muted mb-3">{{ $question->question }}</p>
                
                @if($question->options)
                    @foreach($question->options as $optionIndex => $option)
                    <div class="option-example {{ $option == $question->correct_answer ? 'correct' : '' }}">
                        <strong>{{ chr(65 + $optionIndex) }}.</strong> {{ $option }}
                        @if($option == $question->correct_answer)
                            <i class="fas fa-check-circle text-success float-end"></i>
                        @endif
                    </div>
                    @endforeach
                    
                    @if($question->correct_answer)
                    <div class="mt-3">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Jawaban Benar:</strong> {{ $question->correct_answer }}
                        </div>
                    </div>
                    @endif
                @endif
            </div>
            @endforeach
        </div>
        @endif --}}

        <!-- Study Tips -->
        <div class="instruction-section">
            <h3 class="mb-4"><i class="fas fa-lightbulb text-warning me-2"></i>Tips Mengerjakan</h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle"></i> Strategi Sukses:</h6>
                        <ul class="mb-0">
                            <li>Baca soal dengan teliti dan lengkap</li>
                            <li>Identifikasi kata kunci dalam soal</li>
                            <li>Gunakan pengetahuan praktis, bukan hafalan</li>
                            <li>Kelola waktu dengan baik (45 detik per soal)</li>
                            <li>Kerjakan soal mudah terlebih dahulu</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-brain"></i> Fokus Pembelajaran:</h6>
                        <ul class="mb-0">
                            <li>Standar Akuntansi Keuangan (SAK)</li>
                            <li>Peraturan perpajakan terbaru</li>
                            <li>Standar Profesional Akuntan Publik (SPAP)</li>
                            <li>Teknik analisis laporan keuangan</li>
                            <li>Prinsip pengendalian internal</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Info -->
        <div class="row">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-question fa-2x text-primary mb-2"></i>
                        <h5>30 Soal</h5>
                        <small class="text-muted">Total pertanyaan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x text-info mb-2"></i>
                        <h5>15 Menit</h5>
                        <small class="text-muted">Waktu pengerjaan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-target fa-2x text-success mb-2"></i>
                        <h5>70%</h5>
                        <small class="text-muted">Passing score minimum</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-medal fa-2x text-warning mb-2"></i>
                        <h5>5 Poin</h5>
                        <small class="text-muted">Per soal benar</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Notes -->
        <div class="alert alert-warning mt-4">
            <h6><i class="fas fa-exclamation-triangle"></i> Catatan Penting:</h6>
            <ul class="mb-0">
                <li>Tes ini menggunakan standar dan regulasi yang berlaku di Indonesia</li>
                <li>Soal dapat berupa perhitungan, analisis kasus, atau konsep teoretis</li>
                <li>Nilai minimum 70% diperlukan untuk dinyatakan kompeten</li>
                <li>Setiap soal memiliki bobot nilai yang sama</li>
                <li>Tidak ada pengurangan nilai untuk jawaban salah</li>
            </ul>
        </div>

        <!-- Start Section -->
        <div class="start-section">
            <h3 class="mb-4" style="color: #0ea5e9; font-weight: 700;">
                <i class="fas fa-rocket me-3"></i> Siap Memulai Tes Bidang?
            </h3>
            <p class="mb-4" style="color: #0284c7; font-size: 1.1rem;">
                Pastikan Anda telah menguasai materi yang akan diujikan dan siap menunjukkan kompetensi profesional Anda.
                <br><strong class="text-danger">Timer akan dimulai setelah Anda menekan tombol di bawah ini.</strong>
            </p>
            <a href="{{ route('psychotest.test.category', ['categoryCode' => $category->code, 'start' => '1']) }}" 
               class="start-button">
                <i class="fas fa-play me-3"></i> Mulai Tes Bidang
            </a>
            <div class="mt-4">
                <small style="color: #64748b;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Catatan:</strong> Waktu tes akan dimulai setelah tombol diklik
                </small>
            </div>
        </div>
    </div>

    <script>
        // Add interactive examples
        document.querySelectorAll('.option-example').forEach(option => {
            option.addEventListener('click', function() {
                // Remove previous selections in same group
                const parent = this.closest('.sample-question');
                if (parent) {
                    parent.querySelectorAll('.option-example').forEach(opt => {
                        if (!opt.classList.contains('correct')) {
                            opt.style.borderColor = '#e2e8f0';
                            opt.style.backgroundColor = '#f8fafc';
                        }
                    });
                }
                
                // Highlight selected option (but don't override correct answer styling)
                if (!this.classList.contains('correct')) {
                    this.style.borderColor = '#667eea';
                    this.style.backgroundColor = '#f0f4ff';
                }
            });
        });

        // Add entrance animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.topic-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>