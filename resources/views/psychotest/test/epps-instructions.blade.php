{{-- resources/views/psychotest/test/epps-instructions.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instruksi EPPS Test - {{ $category->name }}</title>
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
            background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
            border: 2px solid #0277bd;
            border-radius: 20px;
            padding: 30px;
            margin: 25px 0;
        }
        
        .choice-example {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin: 15px 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .choice-pair {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        
        .choice-option {
            flex: 1;
            padding: 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .choice-option:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .choice-option.selected {
            border-color: #22c55e;
            background: #f0fdf4;
        }
        
        .dimension-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .dimension-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #667eea;
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
        <h2><i class="fas fa-user-tie me-3"></i>{{ $category->name }}</h2>
        <p class="lead mb-0">Tes kepribadian untuk mengukur kebutuhan dan preferensi personal dalam bekerja</p>
    </div>

    <!-- Instructions Container -->
    <div class="instructions-container">
        <!-- About EPPS -->
        <div class="instruction-section">
            <h3 class="mb-4"><i class="fas fa-info-circle text-primary me-2"></i>Tentang EPPS Test</h3>
            <p class="lead">
                Edward Personal Preference Schedule (EPPS) adalah tes kepribadian yang mengukur 15 dimensi kebutuhan psikologis 
                yang mempengaruhi perilaku seseorang dalam bekerja dan berinteraksi dengan orang lain.
            </p>
        </div>

        <!-- Test Format -->
        <div class="instruction-section">
            <h3 class="mb-4"><i class="fas fa-format-list-bulleted text-info me-2"></i>Format Tes</h3>
            <div class="alert alert-info">
                <h6><i class="fas fa-lightbulb"></i> Format Forced Choice</h6>
                <p class="mb-0">
                    Anda akan disajikan 100 pasang pernyataan. Untuk setiap pasang, pilih pernyataan yang 
                    <strong>PALING menggambarkan diri Anda</strong> atau yang <strong>PALING Anda sukai</strong>.
                </p>
            </div>
        </div>

        <!-- Example -->
        <div class="example-container">
            <h4 class="text-center mb-4"><i class="fas fa-eye text-primary"></i> Contoh Soal EPPS</h4>
            <div class="choice-example">
                <p class="text-center mb-3"><strong>Pilih pernyataan yang PALING menggambarkan diri Anda:</strong></p>
                <div class="choice-pair">
                    <div class="choice-option" onclick="selectExample(this)">
                        <i class="fas fa-trophy text-warning mb-2"></i>
                        <p class="mb-0"><strong>A.</strong> Saya senang mencapai target yang menantang</p>
                    </div>
                    <div class="choice-option" onclick="selectExample(this)">
                        <i class="fas fa-users text-success mb-2"></i>
                        <p class="mb-0"><strong>B.</strong> Saya senang bekerja dalam tim yang harmonis</p>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Pilih salah satu yang PALING sesuai dengan kepribadian Anda
                    </small>
                </div>
            </div>
        </div>

        <!-- 15 Dimensions -->
        <div class="instruction-section">
            <h3 class="mb-4"><i class="fas fa-brain text-success me-2"></i>15 Dimensi Kepribadian EPPS</h3>
            <p class="mb-3">
                Tes ini mengukur kekuatan relatif dari 15 kebutuhan psikologis berikut:
            </p>
            <div class="dimension-grid">
                <div class="dimension-card">
                    <h6><i class="fas fa-trophy text-warning"></i> Achievement</h6>
                    <small>Kebutuhan untuk mencapai prestasi</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-bow-arrow text-info"></i> Deference</h6>
                    <small>Kebutuhan untuk menghormati otoritas</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-sort-amount-up text-primary"></i> Order</h6>
                    <small>Kebutuhan akan keteraturan</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-star text-warning"></i> Exhibition</h6>
                    <small>Kebutuhan untuk menjadi pusat perhatian</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-running text-success"></i> Autonomy</h6>
                    <small>Kebutuhan akan kemandirian</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-heart text-danger"></i> Affiliation</h6>
                    <small>Kebutuhan untuk bersosialisasi</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-search text-info"></i> Intraception</h6>
                    <small>Kebutuhan untuk memahami orang lain</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-hands-helping text-primary"></i> Succorance</h6>
                    <small>Kebutuhan untuk mendapat dukungan</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-crown text-warning"></i> Dominance</h6>
                    <small>Kebutuhan untuk memimpin</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-hands text-secondary"></i> Abasement</h6>
                    <small>Kebutuhan untuk merasa bersalah</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-baby text-pink"></i> Nurturance</h6>
                    <small>Kebutuhan untuk merawat orang lain</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-exchange-alt text-info"></i> Change</h6>
                    <small>Kebutuhan akan perubahan</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-dumbbell text-dark"></i> Endurance</h6>
                    <small>Kebutuhan untuk bertahan</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-venus-mars text-danger"></i> Heterosexuality</h6>
                    <small>Kebutuhan untuk berinteraksi dengan lawan jenis</small>
                </div>
                <div class="dimension-card">
                    <h6><i class="fas fa-fist-raised text-danger"></i> Aggression</h6>
                    <small>Kebutuhan untuk bersikap agresif</small>
                </div>
            </div>
        </div>

        <!-- Important Instructions -->
        <div class="instruction-section">
            <h3 class="mb-4"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Petunjuk Penting</h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle"></i> Yang Harus Dilakukan:</h6>
                        <ul class="mb-0">
                            <li>Jawab dengan jujur sesuai kepribadian Anda</li>
                            <li>Pilih jawaban yang PERTAMA kali terlintas</li>
                            <li>Tidak ada jawaban benar atau salah</li>
                            <li>Setiap pasangan HARUS dipilih salah satu</li>
                            <li>Baca setiap pernyataan dengan teliti</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-times-circle"></i> Yang Harus Dihindari:</h6>
                        <ul class="mb-0">
                            <li>Jangan berpikir terlalu lama</li>
                            <li>Jangan memilih jawaban yang "ideal"</li>
                            <li>Jangan melewatkan soal (harus semua dijawab)</li>
                            <li>Jangan terpengaruh jawaban sebelumnya</li>
                            <li>Jangan memilih berdasarkan ekspektasi orang lain</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Info -->
        <div class="row">
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-question fa-2x text-primary mb-2"></i>
                        <h5>100 Pasangan</h5>
                        <small class="text-muted">Total soal yang harus dijawab</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x text-info mb-2"></i>
                        <h5>60 Menit</h5>
                        <small class="text-muted">Waktu yang tersedia</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-pie fa-2x text-success mb-2"></i>
                        <h5>15 Dimensi</h5>
                        <small class="text-muted">Aspek kepribadian yang diukur</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Start Section -->
        <div class="start-section">
            <h3 class="mb-4" style="color: #0ea5e9; font-weight: 700;">
                <i class="fas fa-rocket me-3"></i> Siap Memulai EPPS Test?
            </h3>
            <p class="mb-4" style="color: #0284c7; font-size: 1.1rem;">
                Ingat: Tidak ada jawaban benar atau salah. Jawablah sesuai dengan kepribadian Anda yang sesungguhnya.
                <br><strong class="text-danger">Timer akan dimulai setelah Anda menekan tombol di bawah ini.</strong>
            </p>
            <a href="{{ route('psychotest.test.category', ['categoryCode' => $category->code, 'start' => '1']) }}" 
               class="start-button">
                <i class="fas fa-play me-3"></i> Mulai EPPS Test
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
        function selectExample(element) {
            // Remove selection from siblings
            const parent = element.parentElement;
            const siblings = parent.querySelectorAll('.choice-option');
            siblings.forEach(sibling => sibling.classList.remove('selected'));
            
            // Add selection to clicked element
            element.classList.add('selected');
        }

        // Add animation effects
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.dimension-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
    </script>
</body>
</html>