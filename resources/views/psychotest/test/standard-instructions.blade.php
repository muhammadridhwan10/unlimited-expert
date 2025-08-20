<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Instruksi Tes - {{ $category->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            box-sizing: border-box;
        }

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
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .instruction-header h2 {
            color: #2d3748;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }

        .instruction-header .category-description {
            color: #718096;
            font-size: 1.2rem;
            font-weight: 400;
            margin-top: 10px;
        }

        .test-info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 20px;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .info-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            margin: 0 auto 15px;
        }

        .info-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .info-desc {
            color: #718096;
            line-height: 1.6;
        }

        .instructions-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            margin: 20px;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .instruction-section {
            margin-bottom: 40px;
        }

        .section-title {
            color: #2d3748;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .example-container {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #22c55e;
            border-radius: 20px;
            padding: 30px;
            margin: 25px 0;
            position: relative;
            overflow: hidden;
        }

        .example-container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #22c55e, #16a34a, #15803d, #22c55e);
            border-radius: 20px;
            z-index: -1;
            animation: borderGlow 3s linear infinite;
        }

        @keyframes borderGlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .example-title {
            color: #15803d;
            font-weight: 600;
            font-size: 1.3rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .question-example {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .question-text {
            font-size: 1.1rem;
            color: #2d3748;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .option-example {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .option-example:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .option-example.correct {
            border-color: #22c55e;
            background: #f0fdf4;
        }

        .option-letter {
            width: 30px;
            height: 30px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .option-example.correct .option-letter {
            background: #22c55e;
        }

        .sequence-example {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .sequence-item {
            width: 80px;
            height: 80px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #2d3748;
            background: white;
        }

        .sequence-item.question {
            border-color: #f59e0b;
            background: #fef3c7;
            color: #d97706;
        }

        .sequence-arrow {
            font-size: 1.5rem;
            color: #667eea;
        }

        .math-example {
            background: #f0f9ff;
            border: 2px solid #0ea5e9;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            font-size: 1.2rem;
            margin: 15px 0;
        }

        .visual-pattern {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            max-width: 300px;
            margin: 20px auto;
        }

        .pattern-cell {
            width: 80px;
            height: 80px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            font-size: 2rem;
        }

        .pattern-cell.missing {
            border-color: #f59e0b;
            background: #fef3c7;
            color: #d97706;
        }

        .rules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }

        .rule-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #667eea;
            transition: transform 0.3s ease;
        }

        .rule-card:hover {
            transform: translateY(-5px);
        }

        .rule-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .rule-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .rule-desc {
            color: #718096;
            line-height: 1.6;
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
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            display: inline-block;
        }

        .start-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 50px rgba(14, 165, 233, 0.6);
            color: white;
        }

        .warning-section {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 20px;
            padding: 25px;
            margin: 30px 0;
        }

        .warning-title {
            color: #d97706;
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .warning-list {
            color: #92400e;
            margin: 0;
            padding-left: 20px;
        }

        .warning-list li {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .instruction-header {
                margin: 10px;
                padding: 20px;
            }
            
            .instruction-header h2 {
                font-size: 2rem;
            }
            
            .instructions-container {
                margin: 10px;
                padding: 25px;
            }
            
            .test-info-cards {
                margin: 20px 10px;
                grid-template-columns: 1fr;
            }
            
            .rules-grid {
                grid-template-columns: 1fr;
            }
            
            .sequence-example {
                gap: 10px;
            }
            
            .sequence-item {
                width: 60px;
                height: 60px;
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="instruction-header">
        <h2><i class="fas fa-brain me-3"></i>{{ $category->name }}</h2>
        
        @if(stripos($category->name, 'deret gambar') !== false || stripos($category->name, 'visual') !== false)
            <p class="category-description">Tes kemampuan visual dan penalaran logika melalui pola gambar</p>
        @elseif(stripos($category->name, 'matematika') !== false || stripos($category->name, 'numeric') !== false)
            <p class="category-description">Tes kemampuan numerik dan perhitungan matematika dasar</p>
        @elseif(stripos($category->name, 'verbal') !== false || stripos($category->name, 'antonim') !== false || stripos($category->name, 'sinonim') !== false)
            <p class="category-description">Tes kemampuan bahasa, antonim, sinonim, dan penalaran verbal</p>
        @elseif(stripos($category->name, 'kraeplin') !== false)
            <p class="category-description">Tes konsentrasi dan kecepatan perhitungan mental</p>
        @else
            <p class="category-description">Tes psikologi untuk mengukur kemampuan kognitif</p>
        @endif
    </div>

    <!-- Test Info Cards -->
    <div class="test-info-cards">
        <div class="info-card">
            <div class="info-icon">
                <i class="fas fa-question"></i>
            </div>
            <div class="info-title">Total Soal</div>
            <div class="info-desc">{{ $questions->count() }} pertanyaan</div>
        </div>
        
        <div class="info-card">
            <div class="info-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="info-title">Waktu Tes</div>
            <div class="info-desc">{{ $category->duration_minutes }} menit</div>
        </div>
        
        <div class="info-card">
            <div class="info-icon">
                @if(stripos($category->name, 'deret gambar') !== false || stripos($category->name, 'visual') !== false)
                    <i class="fas fa-eye"></i>
                @elseif(stripos($category->name, 'matematika') !== false || stripos($category->name, 'numeric') !== false)
                    <i class="fas fa-calculator"></i>
                @elseif(stripos($category->name, 'verbal') !== false)
                    <i class="fas fa-comment"></i>
                @else
                    <i class="fas fa-list-alt"></i>
                @endif
            </div>
            <div class="info-title">Jenis Tes</div>
            <div class="info-desc">
                @if(stripos($category->name, 'deret gambar') !== false || stripos($category->name, 'visual') !== false)
                    Visual & Pola
                @elseif(stripos($category->name, 'matematika') !== false || stripos($category->name, 'numeric') !== false)
                    Numerik & Hitung
                @elseif(stripos($category->name, 'verbal') !== false)
                    Verbal & Bahasa
                @else
                    Pilihan Ganda
                @endif
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="info-title">Status</div>
            <div class="info-desc" style="color: #f59e0b; font-weight: 600;">Instruksi - Timer Belum Dimulai</div>
        </div>
        
        <div class="info-card">
            <div class="info-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="info-title">Total Poin</div>
            <div class="info-desc">{{ $questions->sum('points') }} poin</div>
        </div>
    </div>

    <!-- Instructions Container -->
    <div class="instructions-container">
        
        <!-- Category Specific Examples -->
        @if(stripos($category->name, 'deret gambar') !== false || stripos($category->name, 'visual') !== false)
        <div class="instruction-section">
            <h3 class="section-title">
                <div class="section-icon"><i class="fas fa-eye"></i></div>
                Petunjuk Tes Deret Gambar
            </h3>
            
            <div class="example-container">
                <h4 class="example-title">Contoh Soal Deret Gambar</h4>
                <div class="question-example">
                    <div class="question-text">
                        <strong>Pertanyaan:</strong> Perhatikan pola gambar berikut, gambar manakah yang seharusnya mengisi kotak kosong?
                    </div>
                    
                    <div class="visual-pattern">
                        <div class="pattern-cell">●</div>
                        <div class="pattern-cell">●●</div>
                        <div class="pattern-cell">●●●</div>
                        <div class="pattern-cell">○</div>
                        <div class="pattern-cell">○○</div>
                        <div class="pattern-cell pattern-missing">?</div>
                    </div>
                    
                    <div class="question-text">
                        <strong>Pilihan Jawaban:</strong>
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">A</div>
                        <div>○○○○</div>
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">B</div>
                        <div>○○○</div>
                    </div>
                    
                    <div class="option-example correct">
                        <div class="option-letter">C</div>
                        <div>○○○</div>
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">D</div>
                        <div>○○</div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Jawaban Benar:</strong> C. ○○○ - Mengikuti pola urutan jumlah (1,2,3 untuk ●) dan (1,2,3 untuk ○)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @elseif(stripos($category->name, 'matematika') !== false || stripos($category->name, 'numeric') !== false)
        <div class="instruction-section">
            <h3 class="section-title">
                <div class="section-icon"><i class="fas fa-calculator"></i></div>
                Petunjuk Tes Matematika Dasar
            </h3>
            
            <div class="example-container">
                <h4 class="example-title">Contoh Soal Matematika</h4>
                <div class="question-example">
                    <div class="question-text">
                        <strong>Pertanyaan:</strong> Jika 3x + 7 = 22, berapakah nilai x?
                    </div>
                    
                    <div class="math-example">
                        <strong>Langkah Penyelesaian:</strong><br>
                        3x + 7 = 22<br>
                        3x = 22 - 7<br>
                        3x = 15<br>
                        x = 15 ÷ 3<br>
                        x = 5
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">A</div>
                        <div>x = 3</div>
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">B</div>
                        <div>x = 4</div>
                    </div>
                    
                    <div class="option-example correct">
                        <div class="option-letter">C</div>
                        <div>x = 5</div>
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">D</div>
                        <div>x = 6</div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Jawaban Benar:</strong> C. x = 5 - Dengan menyelesaikan persamaan linear sederhana
                        </div>
                    </div>
                </div>
                
                <div class="question-example">
                    <div class="question-text">
                        <strong>Contoh Deret Angka:</strong> Tentukan angka berikutnya dalam deret: 2, 6, 18, 54, ...
                    </div>
                    
                    <div class="sequence-example">
                        <div class="sequence-item">2</div>
                        <div class="sequence-arrow">×3</div>
                        <div class="sequence-item">6</div>
                        <div class="sequence-arrow">×3</div>
                        <div class="sequence-item">18</div>
                        <div class="sequence-arrow">×3</div>
                        <div class="sequence-item">54</div>
                        <div class="sequence-arrow">×3</div>
                        <div class="sequence-item question">?</div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Pola:</strong> Setiap angka dikalikan 3, jadi jawaban adalah 54 × 3 = 162
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @elseif(stripos($category->name, 'verbal') !== false || stripos($category->name, 'antonim') !== false || stripos($category->name, 'sinonim') !== false)
        <div class="instruction-section">
            <h3 class="section-title">
                <div class="section-icon"><i class="fas fa-comment"></i></div>
                Petunjuk Tes Penalaran Verbal
            </h3>
            
            <div class="example-container">
                <h4 class="example-title">Contoh Soal Antonim (Lawan Kata)</h4>
                <div class="question-example">
                    <div class="question-text">
                        <strong>Pertanyaan:</strong> Manakah antonim (lawan kata) dari "OPTIMIS"?
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">A</div>
                        <div>Percaya diri</div>
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">B</div>
                        <div>Berharap</div>
                    </div>
                    
                    <div class="option-example correct">
                        <div class="option-letter">C</div>
                        <div>Pesimis</div>
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">D</div>
                        <div>Positif</div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Jawaban Benar:</strong> C. Pesimis - Antonim dari optimis adalah pesimis
                        </div>
                    </div>
                </div>
                
                <div class="question-example">
                    <div class="question-text">
                        <strong>Contoh Soal Sinonim (Persamaan Kata):</strong> Manakah sinonim dari "CERDAS"?
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">A</div>
                        <div>Bodoh</div>
                    </div>
                    
                    <div class="option-example correct">
                        <div class="option-letter">B</div>
                        <div>Pintar</div>
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">C</div>
                        <div>Lambat</div>
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">D</div>
                        <div>Malas</div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Jawaban Benar:</strong> B. Pintar - Sinonim dari cerdas adalah pintar
                        </div>
                    </div>
                </div>
                
                <div class="question-example">
                    <div class="question-text">
                        <strong>Contoh Analogi Kata:</strong> PANAS : DINGIN = TERANG : ...
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">A</div>
                        <div>Siang</div>
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">B</div>
                        <div>Benderang</div>
                    </div>
                    
                    <div class="option-example correct">
                        <div class="option-letter">C</div>
                        <div>Gelap</div>
                    </div>
                    
                    <div class="option-example">
                        <div class="option-letter">D</div>
                        <div>Cahaya</div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Jawaban Benar:</strong> C. Gelap - Hubungan antonim: panas↔dingin sama dengan terang↔gelap
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- General Rules -->
        <div class="instruction-section">
            <h3 class="section-title">
                <div class="section-icon"><i class="fas fa-clipboard-list"></i></div>
                Aturan Umum Tes
            </h3>
            
            <div class="rules-grid">
                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-mouse-pointer"></i>
                    </div>
                    <div class="rule-title">Navigasi Soal</div>
                    <div class="rule-desc">Anda dapat berpindah antar soal dengan bebas menggunakan tombol navigasi. Jawaban tersimpan otomatis.</div>
                </div>

                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-save"></i>
                    </div>
                    <div class="rule-title">Penyimpanan Otomatis</div>
                    <div class="rule-desc">Setiap jawaban yang Anda pilih akan tersimpan secara otomatis. Tidak perlu khawatir kehilangan progress.</div>
                </div>

                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="rule-title">Manajemen Waktu</div>
                    <div class="rule-desc">Timer akan berjalan terus. Gunakan waktu dengan bijak dan prioritaskan soal yang Anda kuasai.</div>
                </div>

                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="rule-title">Submit Final</div>
                    <div class="rule-desc">Pastikan semua soal sudah dijawab sebelum submit. Setelah submit, tidak bisa mengubah jawaban.</div>
                </div>

                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="rule-title">Integritas Tes</div>
                    <div class="rule-desc">Kerjakan dengan jujur tanpa bantuan orang lain atau tools eksternal. Ini untuk evaluasi kemampuan Anda yang sesungguhnya.</div>
                </div>

                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <div class="rule-title">Lingkungan Tes</div>
                    <div class="rule-desc">Pastikan koneksi internet stabil dan lingkungan tenang. Hindari gangguan selama tes berlangsung.</div>
                </div>
            </div>
        </div>

        <!-- Category-specific tips -->
        @if(stripos($category->name, 'deret gambar') !== false || stripos($category->name, 'visual') !== false)
        <div class="instruction-section">
            <h3 class="section-title">
                <div class="section-icon"><i class="fas fa-lightbulb"></i></div>
                Tips Khusus Tes Deret Gambar
            </h3>
            
            <div class="rules-grid">
                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="rule-title">Amati Pola</div>
                    <div class="rule-desc">Perhatikan perubahan bentuk, ukuran, posisi, dan jumlah elemen dalam setiap gambar secara berurutan.</div>
                </div>

                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-arrows-alt"></i>
                    </div>
                    <div class="rule-title">Rotasi & Transformasi</div>
                    <div class="rule-desc">Gambar bisa berputar, bergeser, atau berubah bentuk mengikuti pola tertentu dari kiri ke kanan.</div>
                </div>

                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="rule-title">Penambahan/Pengurangan</div>
                    <div class="rule-desc">Perhatikan apakah ada penambahan atau pengurangan elemen (garis, titik, bentuk) dalam setiap langkah.</div>
                </div>
            </div>
        </div>

        @elseif(stripos($category->name, 'matematika') !== false || stripos($category->name, 'numeric') !== false)
        <div class="instruction-section">
            <h3 class="section-title">
                <div class="section-icon"><i class="fas fa-lightbulb"></i></div>
                Tips Khusus Tes Matematika
            </h3>
            
            <div class="rules-grid">
                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="rule-title">Operasi Hitung</div>
                    <div class="rule-desc">Kuasai operasi dasar: penjumlahan, pengurangan, perkalian, pembagian, dan operasi dengan pecahan.</div>
                </div>

                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="rule-title">Pola Deret</div>
                    <div class="rule-desc">Cari pola dalam deret angka: aritmatika (+,-), geometri (×,÷), atau pola khusus lainnya.</div>
                </div>

                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div class="rule-title">Persamaan</div>
                    <div class="rule-desc">Untuk soal aljabar, isolasi variabel dengan memindahkan konstanta ke sisi lain persamaan.</div>
                </div>
            </div>
        </div>

        @elseif(stripos($category->name, 'verbal') !== false)
        <div class="instruction-section">
            <h3 class="section-title">
                <div class="section-icon"><i class="fas fa-lightbulb"></i></div>
                Tips Khusus Tes Verbal
            </h3>
            
            <div class="rules-grid">
                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="rule-title">Antonim</div>
                    <div class="rule-desc">Cari kata yang berlawanan makna. Contoh: besar↔kecil, tinggi↔rendah, cepat↔lambat.</div>
                </div>

                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-equals"></i>
                    </div>
                    <div class="rule-title">Sinonim</div>
                    <div class="rule-desc">Cari kata yang sama atau mirip makna. Contoh: besar=raksasa, cerdas=pintar, indah=cantik.</div>
                </div>

                <div class="rule-card">
                    <div class="rule-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="rule-title">Analogi</div>
                    <div class="rule-desc">Pahami hubungan kata pertama, lalu terapkan hubungan yang sama pada kata kedua.</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Warning Section -->
        <div class="warning-section">
            <h4 class="warning-title">
                <i class="fas fa-exclamation-triangle"></i>
                Peringatan Penting
            </h4>
            <ul class="warning-list">
                <li><strong>Jangan refresh halaman</strong> atau tekan tombol back/forward browser selama tes</li>
                <li><strong>Jangan tutup tab/window</strong> tes hingga selesai dan berhasil submit</li>
                <li><strong>Pastikan koneksi internet stabil</strong> untuk mencegah gangguan saat menyimpan jawaban</li>
                <li><strong>Setelah submit tidak bisa diubah</strong> - pastikan semua jawaban sudah sesuai</li>
                <li><strong>Gunakan browser modern</strong> (Chrome, Firefox, Edge, Safari) untuk pengalaman terbaik</li>
                <li><strong>Disable popup blocker</strong> untuk memastikan sistem tes berfungsi dengan baik</li>
            </ul>
        </div>

        <!-- Start Section -->
        <div class="start-section">
            <h3 class="mb-4" style="color: #0ea5e9; font-weight: 700;">
                <i class="fas fa-rocket me-3"></i>
                Siap Memulai Tes {{ $category->name }}?
            </h3>
            <p class="mb-4" style="color: #0284c7; font-size: 1.1rem;">
                Pastikan Anda telah memahami semua instruksi dan contoh soal di atas sebelum memulai tes.
                <br><strong class="text-danger">Timer akan dimulai setelah Anda menekan tombol di bawah ini.</strong>
            </p>
            
            <a href="{{ route('psychotest.test.category', ['categoryCode' => $category->code, 'start' => '1']) }}" class="start-button">
                <i class="fas fa-play me-3"></i>
                Mulai Tes Sekarang
            </a>
            
            <div class="mt-4">
                <small style="color: #64748b;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Catatan:</strong> Waktu tes akan dimulai setelah Anda menekan tombol "Mulai Tes Sekarang"
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // HAPUS semua prevent accidental page leave dan loading state
        // Karena ini adalah halaman instruksi, bukan halaman tes aktif

        // Add interactive examples
        document.querySelectorAll('.option-example').forEach(option => {
            option.addEventListener('click', function() {
                // Remove previous selections in same group
                const parent = this.closest('.question-example');
                if (parent) {
                    parent.querySelectorAll('.option-example').forEach(opt => {
                        opt.style.borderColor = '#e2e8f0';
                        opt.style.backgroundColor = '#f8fafc';
                    });
                }
                
                // Highlight selected option (but don't override correct answer styling)
                if (!this.classList.contains('correct')) {
                    this.style.borderColor = '#667eea';
                    this.style.backgroundColor = '#f0f4ff';
                }
            });
        });

        // Smooth scroll to sections
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Animation for sequence examples (math category)
        document.querySelectorAll('.sequence-item').forEach((item, index) => {
            setTimeout(() => {
                item.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    item.style.transform = 'scale(1)';
                }, 200);
            }, index * 500);
        });

        // Interactive pattern for visual tests
        document.querySelectorAll('.pattern-cell').forEach(cell => {
            cell.addEventListener('mouseenter', function() {
                if (!this.classList.contains('missing')) {
                    this.style.transform = 'scale(1.1)';
                    this.style.boxShadow = '0 5px 15px rgba(102, 126, 234, 0.3)';
                }
            });
            
            cell.addEventListener('mouseleave', function() {
                if (!this.classList.contains('missing')) {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = 'none';
                }
            });
        });

        // Simple click tracking for start button (tanpa loading state)
        document.querySelector('.start-button').addEventListener('click', function() {
            console.log('User clicked start test button - redirecting to actual test');
        });

        // Add entrance animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.info-card, .rule-card');
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