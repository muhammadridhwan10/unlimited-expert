<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Instruksi Tes Kraeplin - {{ $category->name }}</title>
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

        /* ===== STYLES UNTUK HALAMAN INSTRUKSI ===== */
        .instruction-page {
            display: block;
        }

        .test-page {
            display: none;
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

        .example-title {
            color: #15803d;
            font-weight: 600;
            font-size: 1.3rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .kraeplin-example {
            background: #f5f3e8;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .example-columns {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 20px 0;
        }

        .example-column {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .example-column-header {
            background: #333;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            margin: 0 auto 15px;
        }

        .example-vertical-items {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .example-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .example-number {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            line-height: 1.2;
        }

        .example-input {
            width: 25px;
            height: 20px;
            text-align: center;
            border: 1px solid #333;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            background: white;
            margin: 2px 0;
        }

        .example-input.filled {
            background: #e8f4f8;
            border-color: #17a2b8;
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
        }

        .start-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 50px rgba(14, 165, 233, 0.6);
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

        /* ===== STYLES UNTUK HALAMAN TES ===== */
        .test-header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            color: #333;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .kraeplin-main-container {
            background: #f5f3e8;
            margin: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 30px;
            position: relative;
            height: auto;
            padding-bottom: 80px;
        }

        .column-header-center {
            text-align: center;
            margin-bottom: 20px;
        }

        .current-column-display {
            background: #333;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            margin: 0 auto 15px;
        }

        .current-column-display.active {
            background: #667eea;
        }

        .column-timer-display {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .column-timer-display.warning {
            color: #dc3545;
            animation: pulse 1s infinite;
        }

        /* Grid layout seperti gambar - multiple kolom */
        .kraeplin-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 15px;
            padding: 10px;
            width: 100%;
            height: auto;
        }

        .kraeplin-column {
            background: transparent;
            border: none;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: auto; 
        }

        .kraeplin-column.active {
            background: rgba(102,126,234,0.1);
            border-radius: 8px;
            padding: 8px;
        }

        .column-number {
            background: #333;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .column-number.active {
            background: #667eea;
        }

        .column-items {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
            height: auto; 
        }

        .kraeplin-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .number-display {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            text-align: center;
            line-height: 1.1;
        }

        .answer-input {
            width: 22px;
            height: 18px;
            text-align: center;
            border: 1px solid #333;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            background: white;
            padding: 0;
        }

        .answer-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 3px rgba(102,126,234,0.5);
        }

        .answer-input.filled {
            background: #e8f4f8;
            border-color: #17a2b8;
        }

        .submit-section {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(40,167,69,0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40,167,69,0.4);
        }

        /* Loading spinner for auto submit */
        .auto-submit-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            flex-direction: column;
            color: white;
        }

        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid #fff;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
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

            .kraeplin-main-container {
                margin: 10px;
                padding: 20px;
                padding-bottom: 80px;
            }
            
            .kraeplin-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 10px;
            }
            
            .number-display {
                font-size: 11px;
            }
            
            .answer-input {
                width: 18px;
                height: 16px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- HALAMAN INSTRUKSI -->
    <div class="instruction-page" id="instruction-page">
        <!-- Header -->
        <div class="instruction-header">
            <h2><i class="fas fa-plus me-3"></i>Tes Kraeplin</h2>
            <p class="category-description">Tes konsentrasi dan kecepatan perhitungan mental</p>
        </div>

        <!-- Test Info Cards -->
        <div class="test-info-cards">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-columns"></i>
                </div>
                <div class="info-title">Total Kolom</div>
                <div class="info-desc">10 kolom</div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="info-title">Waktu Per Kolom</div>
                <div class="info-desc">30 detik</div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="info-title">Soal Per Kolom</div>
                <div class="info-desc">30 soal vertikal</div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="info-title">Jenis Tes</div>
                <div class="info-desc">Penjumlahan Cepat</div>
            </div>
        </div>

        <!-- Instructions Container -->
        <div class="instructions-container">
            
            <!-- Instruksi Khusus Kraeplin -->
            <div class="instruction-section">
                <h3 class="section-title">
                    <div class="section-icon"><i class="fas fa-plus"></i></div>
                    Petunjuk Tes Kraeplin
                </h3>
                
                <div class="example-container">
                    <h4 class="example-title">Layout Tes Kraeplin - Pengerjaan dari Atas ke Bawah per Kolom</h4>
                    <div class="kraeplin-example">
                        <div class="text-center mb-3">
                            <strong style="color: #dc3545;">PENTING: Hanya isi digit terakhir dari hasil penjumlahan!</strong>
                        </div>
                        
                        <div class="example-columns">
                            <div class="example-column">
                                <div class="example-column-header">1</div>
                                <div class="example-vertical-items">
                                    <div class="example-item">
                                        <div class="example-number">8</div>
                                        <input type="text" class="example-input filled" value="5" readonly>
                                        <div class="example-number">7</div>
                                    </div>
                                    <div class="example-item">
                                        <div class="example-number">3</div>
                                        <input type="text" class="example-input filled" value="8" readonly>
                                        <div class="example-number">5</div>
                                    </div>
                                    <div class="example-item">
                                        <div class="example-number">5</div>
                                        <input type="text" class="example-input filled" value="0" readonly>
                                        <div class="example-number">5</div>
                                    </div>
                                    <div style="color: #666; font-size: 10px; margin-top: 5px;">
                                        ↓ dan seterusnya
                                    </div>
                                </div>
                            </div>
                            
                            <div class="example-column">
                                <div class="example-column-header">2</div>
                                <div class="example-vertical-items">
                                    <div class="example-item">
                                        <div class="example-number">6</div>
                                        <input type="text" class="example-input" value="" readonly>
                                        <div class="example-number">7</div>
                                    </div>
                                    <div class="example-item">
                                        <div class="example-number">5</div>
                                        <input type="text" class="example-input" value="" readonly>
                                        <div class="example-number">3</div>
                                    </div>
                                    <div class="example-item">
                                        <div class="example-number">7</div>
                                        <input type="text" class="example-input" value="" readonly>
                                        <div class="example-number">6</div>
                                    </div>
                                    <div style="color: #666; font-size: 10px; margin-top: 5px;">
                                        ↓ belum dikerjakan
                                    </div>
                                </div>
                            </div>
                            
                            <div class="example-column">
                                <div class="example-column-header">3</div>
                                <div class="example-vertical-items">
                                    <div class="example-item">
                                        <div class="example-number">9</div>
                                        <input type="text" class="example-input" value="" readonly>
                                        <div class="example-number">1</div>
                                    </div>
                                    <div class="example-item">
                                        <div class="example-number">7</div>
                                        <input type="text" class="example-input" value="" readonly>
                                        <div class="example-number">7</div>
                                    </div>
                                    <div class="example-item">
                                        <div class="example-number">3</div>
                                        <input type="text" class="example-input" value="" readonly>
                                        <div class="example-number">9</div>
                                    </div>
                                    <div style="color: #666; font-size: 10px; margin-top: 5px;">
                                        ↓ belum dikerjakan
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p><strong>Contoh:</strong> 8+7=15 → isi <strong>5</strong> | 3+5=8 → isi <strong>8</strong> | 5+5=10 → isi <strong>0</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aturan Umum -->
            <div class="instruction-section">
                <h3 class="section-title">
                    <div class="section-icon"><i class="fas fa-clipboard-list"></i></div>
                    Aturan Tes Kraeplin
                </h3>
                
                <div class="rules-grid">
                    <div class="rule-card">
                        <div class="rule-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="rule-title">Aturan Penjumlahan</div>
                        <div class="rule-desc"><strong>Hanya isi digit terakhir</strong> dari hasil penjumlahan. Contoh: 8+7=15, maka isi 5. Contoh: 4+3=7, maka isi 7.</div>
                    </div>

                    <div class="rule-card">
                        <div class="rule-icon">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="rule-title">Arah Pengerjaan</div>
                        <div class="rule-desc">Kerjakan dari atas ke bawah dalam setiap kolom. Tekan Tab atau Enter untuk pindah ke baris berikutnya.</div>
                    </div>

                    <div class="rule-card">
                        <div class="rule-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="rule-title">Waktu Per Kolom</div>
                        <div class="rule-desc">Setiap kolom memiliki waktu <strong>30 detik</strong>. Saat waktu habis, akan otomatis pindah ke kolom berikutnya.</div>
                    </div>

                    <div class="rule-card">
                        <div class="rule-icon">
                            <i class="fas fa-forward"></i>
                        </div>
                        <div class="rule-title">Perpindahan Kolom</div>
                        <div class="rule-desc">Kolom akan berganti otomatis setelah 30 detik. Tidak bisa kembali ke kolom sebelumnya.</div>
                    </div>

                    <div class="rule-card">
                        <div class="rule-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div class="rule-title">Kecepatan & Akurasi</div>
                        <div class="rule-desc">Kerjakan secepat mungkin tanpa mengorbankan keakuratan. Semakin banyak jawaban benar, semakin baik nilai Anda.</div>
                    </div>

                    <div class="rule-card">
                        <div class="rule-icon">
                            <i class="fas fa-focus"></i>
                        </div>
                        <div class="rule-title">Konsentrasi</div>
                        <div class="rule-desc">Tetap fokus dan jangan terburu-buru. Kesalahan akan mengurangi nilai akhir Anda.</div>
                    </div>
                </div>
            </div>

            <!-- Warning Section -->
            <div class="warning-section">
                <h4 class="warning-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Peringatan Penting
                </h4>
                <ul class="warning-list">
                    <li><strong>Semua kolom ditampilkan bersamaan</strong> seperti tes Kraeplin asli</li>
                    <li><strong>30 detik per kolom</strong> dengan perpindahan otomatis</li>
                    <li><strong>Pengerjaan dari atas ke bawah</strong> per kolom</li>
                    <li><strong>Tidak ada indikator benar/salah</strong> selama tes berlangsung</li>
                    <li><strong>Grafik penilaian</strong> berdasarkan kecepatan dan akurasi</li>
                    <li><strong>Tes akan otomatis selesai</strong> setelah kolom terakhir berakhir</li>
                </ul>
            </div>

            <!-- Start Section -->
            <div class="start-section">
                <h3 class="mb-4" style="color: #0ea5e9; font-weight: 700;">
                    <i class="fas fa-rocket me-3"></i>
                    Siap Memulai Tes Kraeplin?
                </h3>
                <p class="mb-4" style="color: #0284c7; font-size: 1.1rem;">
                    Anda akan melihat <strong>10 kolom</strong> dengan <strong>30 soal per kolom</strong> yang dikerjakan dari atas ke bawah
                    <br><strong class="text-danger">Setiap kolom memiliki waktu 30 detik dengan perpindahan otomatis</strong>
                </p>
                
                <button onclick="startTest()" class="start-button">
                    <i class="fas fa-play me-3"></i>
                    Mulai Tes Sekarang
                </button>
                
                <div class="mt-4">
                    <small style="color: #64748b;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Catatan:</strong> Timer akan dimulai setelah Anda menekan tombol "Mulai Tes Sekarang"
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- HALAMAN TES -->
    <div class="test-page" id="test-page">
        <!-- Header -->
        <div class="test-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <h5 class="mb-0">Tes Kraeplin</h5>
                        <small class="text-muted">{{ $session->schedule->candidates->name ?? 'Test User' }}</small>
                    </div>
                    <div class="col-md-6 text-center">
                        <div class="d-flex justify-content-center align-items-center gap-4">
                            <div>
                                <strong>Kolom: <span id="current-column">1</span>/10</strong>
                            </div>
                            <div>
                                <strong>Waktu: <span id="column-time-display">30s</span></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <small class="text-muted">Jawaban Terisi: <span id="filled-count">0</span></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Container -->
        <div class="kraeplin-main-container">
            <!-- Current Column Display -->
            <div class="column-header-center">
                <div class="current-column-display" id="current-column-display">1</div>
                <div class="column-timer-display" id="column-timer">30 detik</div>
            </div>

            <!-- Kraeplin Grid - Multiple Columns Like Image -->
            <div class="kraeplin-grid" id="kraeplin-grid">
                <!-- 10 kolom akan di-generate oleh JavaScript -->
            </div>
        </div>

        <!-- Submit button -->
        <div class="submit-section">
            <button class="submit-btn" id="submit-btn" onclick="submitTest()" style="display: none;">
                <i class="fas fa-check me-2"></i>Selesai
            </button>
        </div>
    </div>

    <!-- Auto Submit Overlay -->
    <div class="auto-submit-overlay" id="auto-submit-overlay" style="display: none;">
        <div class="spinner"></div>
        <h4>Tes Kraeplin Selesai</h4>
        <p>Sedang memproses hasil tes dan melanjutkan ke tes berikutnya...</p>
    </div>

    <!-- Submit Modal -->
    <div class="modal fade" id="submitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Selesai Tes Kraeplin</h5>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menyelesaikan tes Kraeplin?</p>
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Ringkasan Tes</h6>
                        <p class="mb-1">Total Kolom: <strong>10</strong></p>
                        <p class="mb-1">Kolom Diselesaikan: <strong id="final-columns">0</strong></p>
                        <p class="mb-0">Total Jawaban: <strong id="final-answers">0</strong></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="confirmSubmit()">Ya, Selesai</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Konfigurasi tes
        const TEST_CONFIG = {
            totalColumns: 10, // 10 kolom
            timePerColumn: 30, // 30 detik per kolom
            itemsPerColumn: 30, // 30 soal per kolom
            sessionId: {{ $session->id }}
        };

        // State management
        let currentColumn = 0;
        let columnTimer = TEST_CONFIG.timePerColumn;
        let kraeplinData = [];
        let answers = [];
        let timerInterval = null;
        let currentRow = 0;
        let isTestCompleted = false;

        // Generate data untuk semua kolom
        function generateKraeplinData() {
            for (let col = 0; col < TEST_CONFIG.totalColumns; col++) {
                const columnData = [];
                for (let item = 0; item < TEST_CONFIG.itemsPerColumn; item++) {
                    const num1 = Math.floor(Math.random() * 9) + 1;
                    const num2 = Math.floor(Math.random() * 9) + 1;
                    columnData.push({ num1, num2, sum: num1 + num2 });
                }
                kraeplinData.push(columnData);
                answers.push(new Array(TEST_CONFIG.itemsPerColumn).fill(''));
            }
        }

        // Mulai tes dari halaman instruksi
        function startTest() {
            document.getElementById('instruction-page').style.display = 'none';
            document.getElementById('test-page').style.display = 'block';
            
            generateKraeplinData();
            createKraeplinGrid();
            setupInputHandlers();
            startTimer();
            updateUI();
        }

        // Create grid layout seperti gambar - semua kolom ditampilkan
        function createKraeplinGrid() {
            const grid = document.getElementById('kraeplin-grid');
            
            for (let col = 0; col < TEST_CONFIG.totalColumns; col++) {
                const columnDiv = document.createElement('div');
                columnDiv.className = 'kraeplin-column';
                columnDiv.id = `column-${col}`;
                
                // Nomor kolom
                const columnNumber = document.createElement('div');
                columnNumber.className = 'column-number';
                columnNumber.textContent = col + 1;
                columnNumber.id = `number-${col}`;
                columnDiv.appendChild(columnNumber);

                // Container untuk items dalam kolom
                const itemsContainer = document.createElement('div');
                itemsContainer.className = 'column-items';

                // Items dalam kolom (30 item vertikal)
                for (let item = 0; item < TEST_CONFIG.itemsPerColumn; item++) {
                    const data = kraeplinData[col][item];
                    
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'kraeplin-item';
                    
                    // Angka atas
                    const topNumber = document.createElement('div');
                    topNumber.className = 'number-display';
                    topNumber.textContent = data.num1;
                    
                    // Input field
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'answer-input';
                    input.maxLength = 1;
                    input.id = `input-${col}-${item}`;
                    input.dataset.col = col;
                    input.dataset.item = item;
                    input.dataset.correctDigit = (data.sum % 10).toString();
                    input.inputMode = 'numeric';
                    input.pattern = '[0-9]*';
                    input.disabled = col !== 0; // Hanya kolom pertama yang aktif
                    
                    // Angka bawah
                    const bottomNumber = document.createElement('div');
                    bottomNumber.className = 'number-display';
                    bottomNumber.textContent = data.num2;
                    
                    itemDiv.appendChild(topNumber);
                    itemDiv.appendChild(input);
                    itemDiv.appendChild(bottomNumber);
                    
                    itemsContainer.appendChild(itemDiv);
                }
                
                columnDiv.appendChild(itemsContainer);
                grid.appendChild(columnDiv);
            }
            
            // Set kolom pertama aktif
            document.getElementById('column-0').classList.add('active');
            document.getElementById('number-0').classList.add('active');
            focusFirstInput();
        }

        // Timer functions
        function startTimer() {
            timerInterval = setInterval(() => {
                columnTimer--;
                updateTimer();
                
                if (columnTimer <= 0) {
                    nextColumn();
                }
            }, 1000);
        }

        function updateTimer() {
            document.getElementById('column-timer').textContent = columnTimer + ' detik';
            document.getElementById('column-time-display').textContent = columnTimer + 's';
            
            const timerDisplay = document.getElementById('column-timer');
            const currentColumnDisplay = document.getElementById('current-column-display');
            
            if (columnTimer <= 10) {
                timerDisplay.classList.add('warning');
                currentColumnDisplay.style.background = '#dc3545';
            } else {
                timerDisplay.classList.remove('warning');
                currentColumnDisplay.style.background = '#667eea';
            }
        }

        // Pindah ke kolom berikutnya - MODIFIKASI UTAMA DI SINI
        function nextColumn() {
            if (currentColumn < TEST_CONFIG.totalColumns - 1) {
                // Deactivate current column
                document.getElementById(`column-${currentColumn}`).classList.remove('active');
                document.getElementById(`number-${currentColumn}`).classList.remove('active');
                disableColumnInputs(currentColumn);
                
                currentColumn++;
                currentRow = 0;
                columnTimer = TEST_CONFIG.timePerColumn;
                
                // Activate next column
                document.getElementById(`column-${currentColumn}`).classList.add('active');
                document.getElementById(`number-${currentColumn}`).classList.add('active');
                enableColumnInputs(currentColumn);
                focusFirstInput();
                updateUI();
            } else {
                // MODIFIKASI: Tes selesai - auto submit tanpa menunggu tombol
                autoCompleteTest();
            }
        }

        // FUNGSI BARU: Auto complete test
        function autoCompleteTest() {
            if (isTestCompleted) return; // Prevent double execution
            
            isTestCompleted = true;
            clearInterval(timerInterval);
            
            // Show loading overlay
            document.getElementById('auto-submit-overlay').style.display = 'flex';
            
            // Auto submit after 2 seconds
            setTimeout(() => {
                autoSubmitTest();
            }, 2000);
        }

        // FUNGSI BARU: Auto submit test
        function autoSubmitTest() {
            // Hitung statistik akhir
            let totalAnswers = 0;
            let correctAnswers = 0;
            let columnScores = [];
            
            for (let col = 0; col < TEST_CONFIG.totalColumns; col++) {
                let columnTotal = 0;
                let columnCorrect = 0;
                
                for (let item = 0; item < TEST_CONFIG.itemsPerColumn; item++) {
                    const userAnswer = answers[col][item];
                    const correctDigit = (kraeplinData[col][item].sum % 10).toString();
                    
                    if (userAnswer !== '') {
                        totalAnswers++;
                        columnTotal++;
                        if (userAnswer === correctDigit) {
                            correctAnswers++;
                            columnCorrect++;
                        }
                    }
                }
                
                columnScores.push({
                    column: col + 1,
                    total: columnTotal,
                    correct: columnCorrect,
                    percentage: columnTotal > 0 ? (columnCorrect / columnTotal * 100) : 0
                });
            }

            // Save data
            fetch('{{ route("psychotest.test.save-kraeplin-answer") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    session_id: TEST_CONFIG.sessionId,
                    kraeplin_data: answers,
                    statistics: {
                        total_answers: totalAnswers,
                        correct_answers: correctAnswers,
                        completed_columns: TEST_CONFIG.totalColumns, // Always 10 for auto complete
                        column_scores: columnScores
                    }
                })
            }).then(response => {
                if (response.ok) {
                    // Submit form untuk pindah ke test berikutnya
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("psychotest.test.submit") }}';
                    
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrf);
                    
                    const sessionInput = document.createElement('input');
                    sessionInput.type = 'hidden';
                    sessionInput.name = 'session_id';
                    sessionInput.value = TEST_CONFIG.sessionId;
                    form.appendChild(sessionInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                } else {
                    // Hide loading and show error
                    document.getElementById('auto-submit-overlay').style.display = 'none';
                    alert('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
                    document.getElementById('submit-btn').style.display = 'block';
                }
            }).catch(error => {
                console.error('Error:', error);
                // Hide loading and show error
                document.getElementById('auto-submit-overlay').style.display = 'none';
                alert('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
                document.getElementById('submit-btn').style.display = 'block';
            });
        }

        function enableColumnInputs(col) {
            for (let item = 0; item < TEST_CONFIG.itemsPerColumn; item++) {
                document.getElementById(`input-${col}-${item}`).disabled = false;
            }
        }

        function disableColumnInputs(col) {
            for (let item = 0; item < TEST_CONFIG.itemsPerColumn; item++) {
                document.getElementById(`input-${col}-${item}`).disabled = true;
            }
        }

        function focusFirstInput() {
            const firstInput = document.getElementById(`input-${currentColumn}-0`);
            if (firstInput) {
                firstInput.focus();
                currentRow = 0;
            }
        }

        // Input handling
        function setupInputHandlers() {
            document.addEventListener('input', handleInput);
            document.addEventListener('keydown', handleKeydown);
        }

        function handleInput(e) {
            if (!e.target.classList.contains('answer-input')) return;
            
            const col = parseInt(e.target.dataset.col);
            const item = parseInt(e.target.dataset.item);
            let userInput = e.target.value;
            
            // Validasi hanya angka 0-9
            userInput = userInput.replace(/[^0-9]/g, '');
            e.target.value = userInput;
            
            const userAnswer = userInput.trim();
            answers[col][item] = userAnswer;
            
            if (userAnswer !== '') {
                e.target.classList.add('filled');
                
                // Auto-advance ke input berikutnya dalam kolom yang sama
                setTimeout(() => {
                    const nextInput = document.getElementById(`input-${col}-${item + 1}`);
                    if (nextInput && !nextInput.disabled) {
                        nextInput.focus();
                        currentRow = item + 1;
                    }
                }, 100);
            } else {
                e.target.classList.remove('filled');
            }
            
            updateFilledCount();
        }

        function handleKeydown(e) {
            if (!e.target.classList.contains('answer-input')) return;
            
            // Allow: backspace, delete, tab, escape, enter
            if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }
            
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
            
            // Handle Tab/Enter navigation
            if ((e.key === 'Tab' || e.key === 'Enter')) {
                e.preventDefault();
                const col = parseInt(e.target.dataset.col);
                const item = parseInt(e.target.dataset.item);
                const nextInput = document.getElementById(`input-${col}-${item + 1}`);
                if (nextInput && !nextInput.disabled) {
                    nextInput.focus();
                    currentRow = item + 1;
                }
            }
        }

        function updateFilledCount() {
            let totalFilled = 0;
            
            // Count total filled
            for (let col = 0; col <= currentColumn; col++) {
                for (let item = 0; item < TEST_CONFIG.itemsPerColumn; item++) {
                    if (answers[col][item] !== '') {
                        totalFilled++;
                    }
                }
            }
            
            document.getElementById('filled-count').textContent = totalFilled;
        }

        function updateUI() {
            document.getElementById('current-column').textContent = currentColumn + 1;
            document.getElementById('current-column-display').textContent = currentColumn + 1;
            updateFilledCount();
        }

        // FUNGSI MANUAL SUBMIT (untuk tombol Selesai jika diperlukan)
        function submitTest() {
            updateFinalStatistics();
            new bootstrap.Modal(document.getElementById('submitModal')).show();
        }

        function updateFinalStatistics() {
            document.getElementById('final-columns').textContent = currentColumn + 1;
            
            let totalAnswers = 0;
            for (let col = 0; col <= currentColumn; col++) {
                for (let item = 0; item < TEST_CONFIG.itemsPerColumn; item++) {
                    if (answers[col][item] !== '') {
                        totalAnswers++;
                    }
                }
            }
            document.getElementById('final-answers').textContent = totalAnswers;
        }

        function confirmSubmit() {
            if (isTestCompleted) return; // Prevent double execution
            autoSubmitTest();
        }

        // Prevent page refresh
        window.addEventListener('beforeunload', (e) => {
            if (timerInterval && !isTestCompleted) {
                e.preventDefault();
                e.returnValue = 'Tes Anda akan hilang jika meninggalkan halaman ini.';
            }
        });
    </script>
</body>
</html>