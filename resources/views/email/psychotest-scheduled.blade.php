<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seleksi Cakapai HPN</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: left;
            margin-bottom: 30px;
        }
        .schedule-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .time-box {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 8px 15px;
            margin: 5px;
            border-radius: 5px;
            font-weight: bold;
        }
        .time-box.end {
            background-color: #28a745;
        }
        .credentials-box {
            background-color: #e9f7ef;
            border: 2px solid #28a745;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .credential-item {
            margin: 10px 0;
        }
        .credential-label {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            min-width: 80px;
            text-align: center;
            margin-right: 10px;
        }
        .credential-value {
            background-color: white;
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
        }
        .start-button {
            background-color: #28a745;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin: 15px 0;
            font-weight: bold;
        }
        .instructions {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .instructions ol {
            margin: 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 14px;
            color: #666;
        }
        .dotted-line {
            border-top: 2px dotted #666;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <p>Dear {{ $candidateName }},</p>
            <p>Anda kami undang untuk mengikuti <strong>Seleksi Calon Pegawai TGS AU+PARTNERS</strong>.<br>
            Sebelum mengikuti ujian, harap membaca poin penting ini:</p>
        </div>

        <div class="instructions">
            <ol>
                <li>Gunakan <strong>laptop/komputer</strong> dan pastikan memiliki koneksi <strong>internet</strong> stabil (minimal 256 Kbps).</li>
                <li>Gunakan browser terbaru <strong>Google Chrome</strong>.</li>
                <li>Waktu pelaksanaan tes menggunakan <strong>Waktu Indonesia Barat (WIB)</strong>. Bila Anda berada di luar wilayah WIB agar dapat menyesuaikan.</li>
                <li>Siapkan <strong>catatan</strong> dan <strong>alat tulis</strong> yang digunakan sebagai media bantu hitung. <strong>(DILARANG menggunakan kalkulator atau alat hitung lain)</strong>.</li>
                <li>Durasi tes kurang lebih selama {{ $duration }} menit, pastikan Anda mengerjakan dalam rentang waktu yang telah disediakan.</li>
                <li><strong>JANGAN membuka tab browser atau aplikasi lain selama ujian berlangsung</strong> karena sistem dapat mendeteksi dan mencatat aktivitas tersebut sebagai kecurangan.</li>
            </ol>
        </div>

        <p><strong>Berikut informasi mengenai tes Anda:</strong></p>

        <div class="schedule-info">
            <div class="time-box">Waktu Mulai</div>
            <strong>{{ $startTime }} WIB</strong><br><br>
            <div class="time-box end">Waktu Selesai</div>
            <strong>{{ $endTime }} WIB</strong><br><br>
            
            <p>Silahkan klik tombol ini untuk mulai ujian:</p>
            <a href="{{ $testUrl }}" class="start-button">Mulai Ujian</a>
            
            <p><strong>Alternatif Link Ujian:</strong></p>
            <p><a href="{{ $testUrl }}">{{ $testUrl }}</a></p>
        </div>

        <p><strong>Berikut ini informasi yang digunakan untuk login manual:</strong></p>

        <div class="credentials-box">
            <div class="credential-item">
                <span class="credential-label">Username</span>
                <span class="credential-value">{{ $username }}</span>
            </div>
            <div class="credential-item">
                <span class="credential-label">Password</span>
                <span class="credential-value">{{ $password }}</span>
            </div>
        </div>

        <p><strong>Silakan ikuti instruksi berikut ini:</strong></p>

        <div class="instructions">
            <p><strong>Saat Ujian:</strong></p>
            <ol>
                <li>Klik tombol "<strong>Mulai Ujian</strong>" untuk masuk ke halaman ujian.</li>
                <li>Apabila login gagal atau tidak bisa tersambung dalam waktu yang lama, silahkan klik "<strong>Login Manual</strong>" dan masukkan username serta <strong>password</strong> yang tertera di email ini. Pastikan mengisi username dan password dengan benar, tidak ada spasi yang tertinggal jika meng-copy password.</li>
                <li><strong>Bacalah instruksi</strong> setiap ujian dengan seksama.</li>
                <li>Kerjakan tes secara mandiri dengan penuh kejujuran dan integritas.</li>
                <li><strong>PENTING:</strong> Selama ujian berlangsung, <strong>jangan membuka tab browser lain, aplikasi lain, atau halaman baru</strong> karena sistem monitoring akan mendeteksi dan mencatat aktivitas tersebut.</li>
                <li>Sistem akan memonitor aktivitas seperti pergantian tab, pembukaan aplikasi lain, atau upaya copy-paste. <strong>Mohon junjung tinggi kejujuran & integritas</strong>.</li>
                <li>Setelah ujian selesai, pastikan untuk mengklik tombol submit/serahkan.</li>
                <li>Jika terjadi kendala, silahkan tutup browser Anda dan masuk kembali dengan informasi login yang sama sehingga dapat menyelesaikan sesi yang belum selesai. Link ujian dapat diakses kembali jika Anda belum menyelesaikan tes.</li>
                <li>Jika terdapat kendala yang sulit diatasi silahkan hubungi kami di kontak berikut: [kontak support]</li>
            </ol>
        </div>

        <div class="dotted-line"></div>

        <div style="text-align: center;">
            <div class="dotted-line"></div>
            <p><strong>Semoga sukses!</strong></p>
            <br>
            <p>Salam hangat,</p>
            <br><br>
            <p><strong>TGS AU+PARTNERS</strong><br>
            Follow Us on IG @aupartners</p>
            <div class="dotted-line"></div>
            <p style="font-size: 12px; color: #999;">
                Mohon tidak membalas email ini
            </p>
        </div>
    </div>
</body>
</html>