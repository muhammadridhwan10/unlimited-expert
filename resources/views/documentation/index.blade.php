<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unlimited Expert Guide Book</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Basic Reset and Font */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }
        body {
            font-family: 'Roboto', sans-serif;
            color: #333;
            display: flex;
            background-color: #f8f9fc;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #0056b3;
            color: #ffffff;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 2;
        }
        .sidebar h2 {
            font-size: 1.6rem;
            text-align: center;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        .sidebar ul {
            list-style-type: none;
        }
        .sidebar li {
            margin: 1rem 0;
        }
        .sidebar a {
            color: #ffffff;
            text-decoration: none;
            font-size: 1rem;
            padding: 10px;
            display: block;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #003a7f;
        }
        .toggle-btn {
            display: none;
            background-color: #0056b3;
            color: #ffffff;
            padding: 10px 15px;
            font-size: 1.2rem;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 3;
            cursor: pointer;
            border: none;
            border-radius: 4px;
        }

        /* Content */
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 40px;
            max-width: 800px;
        }
        .content h1 {
            font-size: 2rem;
            color: #0056b3;
            text-align: center;
            font-weight: 700;
            margin-bottom: 2rem;
        }
        .content h2, .content h3 {
            color: #333;
            margin-top: 2rem;
            font-weight: 700;
        }
        .content h3 {
            font-size: 1.3rem;
        }
        .content h4 {
            font-size: 1.1rem;
            margin-top: 1rem;
            font-weight: 500;
        }
        p, ul {
            margin: 1rem 0;
            line-height: 1.6;
        }
        ul {
            list-style-type: disc;
            padding-left: 20px;
        }
        /* Media Queries */
        @media (max-width: 768px) {
            .sidebar {
                position: absolute;
                width: 100%;
                height: auto;
                display: none;
                padding: 20px 10px;
            }
            .sidebar.show {
                display: block;
            }
            .toggle-btn {
                display: block;
            }
            .content {
                margin-left: 0;
                padding: 20px;
            }
            .sidebar h2 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>

    <!-- Toggle Button for Mobile -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰ Guide Menu</button>

    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="sidebar">
        <h2>Guide Contents</h2>
        <ul>
            <li><a href="#login">1.0 Login</a></li>
            <li><a href="#home-features">2.0 Akses Fitur Home</a></li>
            <li><a href="#hrm-module">3.0 Akses Modul HRM</a>
                <ul>
                    <li><a href="#overtime-data">3.1 Input Overtime Data</a></li>
                    <li><a href="#absence-request">3.2 Absence and Sick Leave Requests</a></li>
                    <li><a href="#personnel-assessment">3.3 Input Data Personnel Assessment</a></li>
                    <li><a href="#report-hrm">3.4 Akses Report Modul HRM</a></li>
                </ul>
            </li>
            <li><a href="#finance-module">4.0 Akses Modul Finansial</a>
                <ul>
                    <li><a href="#medical-allowance">4.1 Input Data Medical Allowance</a></li>
                    <li><a href="#reimbursement-personal">4.2 Mengajukan Data Reimbursement Personal</a></li>
                    <li><a href="#reimbursement-client">4.3 Mengajukan Data Reimbursement Client</a></li>
                </ul>
            </li>
            <li><a href="#project-management">5.0 Akses Modul Project Management</a>
                <ul>
                    <li><a href="#input-project-data">5.1 Menginputkan Data Project</a></li>
                    <li><a href="#input-task-data">5.2 Menginputkan Data Task</a></li>
                    <li><a href="#input-time-budget">5.3 Menginputkan Data Time Budget</a></li>
                    <li><a href="#input-subtask-data">5.4 Menginputkan Data Subtask</a></li>
                    <li><a href="#view-tracker">5.5 Melihat Data Tracker Per Project</a></li>
                    <li><a href="#invite-members">5.6 Mengundang Anggota ke Project</a></li>
                    <li><a href="#view-timesheet">5.7 Melihat Data Timesheet Per Project</a></li>
                    <li><a href="#checklist-completed">5.8 Checklist Completed Project</a></li>
                    <li><a href="#project-progress-report">5.9 Melihat Data Project Progress Report</a></li>
                    <li><a href="#timesheet-report">5.10 Melihat Data Timesheet Reports</a></li>
                    <li><a href="#project-tracker">5.11 Melihat Data Project Tracker</a></li>
                </ul>
            </li>
            <li><a href="#document-request">6.0 Akses Fitur Document Request</a></li>
            <li><a href="#support ticket">7.0 Akses Fitur Support Ticket</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="content">
        <h1>Unlimited Expert Guide Book</h1>

        <!-- Section 1.0: Login -->
        <section id="login">
            <h2>1.0 Login</h2>
            <ul>
                <li>Jika Belum Memiliki Akun: Hubungi admin untuk dibuatkan akun. Admin akan mengatur dan memberikan akses kepada Anda.</li>
                <li>Jika Sudah Memiliki Akun :
                    <ul>
                        <li>Masukkan email dan password yang diberikan oleh admin</li>
                        <li>Centang captcha sebagai langkah verifikasi keamanan.</li>
                        <li>Klik tombol Login untuk mengakses aplikasi.</li>
                    </ul>
                </li>
                <p>Jika ada kesulitan, silakan hubungi admin untuk bantuan lebih lanjut.</p>
            </ul>
        </section>

        <!-- Section 2.0: Access Home Features -->
        <section id="home-features">
            <h2>2.0 Akses Modul Home</h2>
            <p>Setelah berhasil login, Anda akan diarahkan ke halaman Home. Di halaman ini, Anda dapat melihat berbagai data report terkait kinerja dan kehadiran Anda, antara lain : </p>
            <ul>
                <li>Total Attendance - Total jumlah kehadiran Anda</li>
                <li>Total Overtime Hours – Jumlah jam lembur yang sudah Anda lakukan</li>
                <li>Total Annual Leave Taken – Total cuti tahunan yang telah digunakan</li>
                <li>Remaining Annual Leave Days – Sisa hari cuti tahunan Anda</li>
                <li>Total Sick Days – Jumlah hari sakit yang sudah diambil.</li>
                <li>Total Reimbursements – Jumlah klaim reimbursement yang sudah disetujui.</li>
                <li>Daily Timesheet Hours – Rekap jam kerja harian</li>
                <li>Attendance Statistics – Statistik kehadiran Anda secara keseluruhan.</li>
                <li>Total Projects – Jumlah total proyek yang sedang berjalan</li>
                <li>Project Status Distribution – Distribusi status dari proyek-proyek Anda</li>
            </ul>
            <p>Selain itu, Anda juga dapat melakukan absensi langsung dari halaman ini.</p>
        </section>

        <!-- Section 3.0: Access HRM Module -->
        <section id="hrm-module">
            <h2>3.0 Akses Modul HRM</h2>
            <p>Setelah berada di halaman Home, Anda dapat mengakses modul HRM untuk melakukan berbagai aktivitas terkait manajemen sumber daya manusia. Di modul ini, Anda dapat :</p>
            <ul>
                <li>Menginputkan Data Overtime – Merekam jam lembur yang sudah Anda lakukan dan mengajukan persetujuan lembur.</li>
                <li>Absence Request – Mengajukan permohonan izin atau cuti (seperti sakit, cuti tahunan, dan izin lainnya).</li>
                <li>Personnel Assessment – Mengisi atau melihat hasil penilaian kinerja dan pengembangan diri.</li>
                <li>Report – Melihat atau mengunduh laporan terkait kinerja, absensi, dan data HR lainnya.</li>
            </ul>
            <p>Gunakan fitur ini untuk memastikan semua data HRM Anda tercatat dengan baik. Jika ada kendala, silakan hubungi tim HR atau admin.</p>
            
            <!-- Subsection 3.1: Input Overtime Data -->
            <section id="overtime-data">
                <h3>3.1 Input Overtime Data</h3>
                <p>Untuk menginput data lemburan Anda, ikuti langkah-langkah berikut : </p>
                <ul>
                    <li>Akses modul HRM dan klik fitur Overtime.</li>
                    <li>Di halaman overtime, Anda akan melihat daftar lembur yang sudah dibuat sebelumnya.</li>
                    <li>Untuk menambah data lemburan baru, klik tombol + di kanan atas.</li>
                    <li>Sistem akan menampilkan popup form untuk input data lemburan.</li>
                    <li>Isi semua field di form tersebut, termasuk:
                        <ul>
                            <li>Approval By: Masukkan nama orang yang memberikan persetujuan lembur.</li>
                            <li>Project: Pilih proyek terkait lemburan.</li>
                            <li>Start Date: Tentukan tanggal mulai lembur.</li>
                            <li>Start Time: Tentukan waktu mulai lembur.</li>
                            <li>End Time: Tentukan waktu selesai lembur.</li>
                            <li>Note: Tambahkan catatan jika diperlukan</li>
                        </ul>
                    </li>
                    <li>Setelah semua field diisi, klik Create untuk menyimpan data lembur</li>
                </ul>
                <p>Data lemburan Anda akan tersimpan di sistem dan dapat dilihat di daftar overtime.</p>
            </section>

            <!-- Subsection 3.2: Absence and Sick Leave Requests -->
            <section id="absence-request">
                <h3>3.2 Absence and Sick Leave Requests</h3>
                <p>Pada langkah ini, user dengan tipe senior, junior, staff, dan manager bisa mengajukan cuti dan ketidakhadiran karena sakit, dengan memenuhi kebijakan perusahaan. User dengan tipe intern hanya bisa mengajukan ketidakhadiran karena sakit</p>
                <h4>Langkah Mengajukan Cuti: </h4>
                <ul>
                    <li>Klik fitur Absence Request pada modul HRM.</li>
                    <li>Anda akan melihat daftar pengajuan cuti yang sudah dibuat sebelumnya.</li>
                    <li>Untuk mengajukan cuti baru, klik tombol + di kanan atas.</li>
                    <li>Sistem akan menampilkan popup form.</li>
                    <li>Isi semua field dalam form, seperti:
                        <ul>
                            <li>Employee: Nama karyawan.</li>
                            <li>Select Type: Pilih Leave untuk pengajuan cuti.</li>
                            <li>Approval By: Nama orang yang memberikan persetujuan.</li>
                            <li>Leave Type: Pilih jenis cuti (misalnya, cuti tahunan).</li>
                            <li>Start Date dan End Date: Tanggal mulai dan berakhirnya cuti.</li>
                            <li>Leave Reason: Alasan pengajuan cuti.</li>
                        </ul>
                    </li>
                    <li>Setelah semua field terisi, klik Create.</li>
                </ul>
                <h4>Langkah Mengajukan Ketidakhadiran karena Sakit: </h4>
                <ul>
                    <li>Pada bagian Leave Type, pilih Sick.</li>
                    <li>Isi field berikut:
                        <ul>
                            <li>Image: Unggah surat dokter sebagai bukti.</li>
                            <li>Date Sick Letter: Tanggal surat sakit.</li>
                            <li>Total Sick Days: Jumlah hari ketidakhadiran karena sakit.</li>
                        </ul>
                    </li>
                    <li>Setelah semua field terisi, klik tombol Create.</li>
                </ul>
                <p>Data pengajuan akan tersimpan di sistem, dan status pengajuan akan menunggu persetujuan sesuai kebijakan perusahaan.</p>
            </section>

            <!-- Subsection 3.3: Input Data Personnel Assessment -->
            <section id="personnel-assessment">
                <h3>3.3 Input Data Personnel Assessment</h3>
            </section>

            <!-- Subsection 3.3: Input Data Personnel Assessment -->
            <section id="report-hrm">
                <h3>3.4 Akses Report HRM Module</h3>
                <p>Pada modul HRM, Anda bisa mengakses berbagai data report terkait kehadiran, lembur, dan ketidakhadiran. Berikut adalah panduan untuk mengaksesnya :</p>
                <ul>
                    <li>Report Attendance:
                        <ul>
                            <li>Klik fitur Attendance Report.</li>
                            <li>Di dalamnya, Anda akan melihat Attendance Statistics, yang memberikan gambaran mengenai statistik kehadiran Anda, seperti total hari hadir, terlambat, dan absensi.</li>
                        </ul>
                    </li>
                    <li>Report Overtime:
                        <ul>
                            <li>Klik fitur Overtime Report.</li>
                            <li>Anda akan melihat Overtime Statistics, yang menampilkan jumlah jam lembur, frekuensi lembur, dan statistik lainnya terkait lembur.</li>
                        </ul>
                    </li>
                    <li>Report Absence Request:
                        <ul>
                            <li>Klik fitur Absence Request Report.</li>
                            <li>Terdapat dua jenis report di dalamnya:
                                <ul>
                                    <li>Report Leave: Menampilkan laporan terkait pengajuan cuti.</li>
                                    <li>Report Sick: Menampilkan laporan ketidakhadiran karena sakit, beserta bukti yang diunggah.</li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <p>Dengan fitur report ini, Anda dapat memantau dan mengevaluasi berbagai aspek dari aktivitas kehadiran, lembur, dan ketidakhadiran Anda di perusahaan.</p>
                </ul>
            </section>
        </section>

        <!-- Section 3.0: Access HRM Module -->
        <section id="finance-module">
            <h2>4.0 Akses Modul Finansial</h2>
            <p>Pada modul Finance, Anda dapat mengelola berbagai pengeluaran dan klaim yang terkait dengan tunjangan medis dan reimbursement, di modul ini, anda dapat : </p>
            <ul>
                <li>Menginputkan Medical Allowance – Mengajukan klaim untuk mendapatkan penggantian biaya pengobatan, pembelian obat atau perawatan kesehatan lainnya.</li>
                <li>Reimbursement Personal – Klaim penggantian biaya yang dikeluarkan karyawan untuk keperluan pribadi terkait pekerjaan, misalnya biaya transportasi, makan saat dinas, atau biaya operasional yang dikeluarkan selama bekerja.</li>
                <li>Reimbursement Client – Klaim penggantian biaya yang dikeluarkan untuk keperluan proyek atau klien tertentu, seperti biaya perjalanan, akomodasi, atau pembelian material yang dibutuhkan dalam proyek.</li>
            </ul>
            <p>Gunakan fitur ini untuk memastikan semua data Finance Anda tercatat dengan baik. Jika ada kendala, silakan hubungi tim IT atau admin</p>
            
            <!-- Subsection 3.1: Input Overtime Data -->
            <section id="medical-allowance">
                <h3>4.1 Mengajukan Data Medical Allowance</h3>
                <p>User dengan tipe selain intern dan sesuai kebijakan partner dapat mengajukan tunjangan medis. Berikut langkah-langkahnya : </p>
                <ul>
                    <li>Akses modul Finance dan klik fitur Medical Allowance.</li>
                    <li>Anda akan melihat daftar tunjangan medis yang sudah diajukan sebelumnya.</li>
                    <li>Untuk mengajukan tunjangan medis baru, klik tombol + di kanan atas.</li>
                    <li>Sistem akan menampilkan popup form</li>
                    <li>Isi semua field yang diperlukan
                        <ul>
                            <li>Employee: Nama karyawan yang mengajukan.</li>
                            <li>Branch: Cabang tempat karyawan bekerja.</li>
                            <li>Approval By: Nama pemberi persetujuan.</li>
                            <li>Reimbursement Type: Pilih Medical Allowance.</li>
                            <li>Date: Tanggal pengajuan.</li>
                            <li>Amount: Jumlah tunjangan medis yang diajukan</li>
                            <li>Image: Unggah bukti pengeluaran medis (misalnya, kuitansi).</li>
                            <li>Description: Tambahkan deskripsi singkat tentang pengeluaran.</li>
                        </ul>
                    </li>
                    <li>Setelah semua field terisi, klik Create untuk menyimpan pengajuan</li>
                </ul>
                <p>Tunjangan medis akan diajukan untuk persetujuan, sesuai dengan kebijakan yang berlaku.</p>
            </section>

            <!-- Subsection 4.2: Reimbursement Personal -->
            <section id="reimbursement-personal">
                <h3>4.2 Mengajukan Data Reimbursement Personal</h3>
                <p>Semua user dapat mengajukan reimbursement personal. Berikut langkah-langkahnya: </p>
                <ul>
                    <li>Akses modul Finance dan klik fitur Reimbursement Personal.</li>
                    <li>Anda akan melihat daftar reimbursement personal yang sudah diajukan sebelumnya.</li>
                    <li>Untuk mengajukan reimbursement baru, klik tombol + di kanan atas.</li>
                    <li>Sistem akan menampilkan popup form.</li>
                    <li>Isi semua field yang ada di form tersebut:
                        <ul>
                            <li>Branch: Cabang tempat karyawan bekerja.</li>
                            <li>Approval By: Nama pemberi persetujuan.</li>
                            <li>Date: Tanggal pengajuan</li>
                            <li>Amount: Jumlah pengeluaran yang ingin direimburs.</li>
                            <li>Image: Unggah bukti pengeluaran (misalnya, kuitansi).</li>
                            <li>Description: Tambahkan deskripsi singkat mengenai pengeluaran</li>
                        </ul>
                    </li>
                    <li>Setelah semua field terisi, klik Create untuk menyimpan pengajuan.</li>
                </ul>
                <p>Pengajuan reimbursement personal akan diproses sesuai kebijakan perusahaan dan dipantau melalui daftar yang sudah diajukan.</p>
            </section>

            <section id="reimbursement-client">
                <h3>4.3 Mengajukan Data Reimbursement Client</h3>
                <p>User dapat mengajukan reimbursement client dengan langkah-langkah berikut: </p>
                <ul>
                    <li>Akses modul Finance dan klik fitur Reimbursement Client.</li>
                    <li>Anda akan melihat daftar reimbursement client yang sudah diajukan sebelumnya.</li>
                    <li>Untuk mengajukan reimbursement client baru, klik tombol + di kanan atas.</li>
                    <li>Sistem akan menampilkan popup form.</li>
                    <li>Isi semua field yang ada di form tersebut:
                        <ul>
                            <li>Client: Nama klien terkait.</li>
                            <li>Approval By: Nama pemberi persetujuan.</li>
                            <li>Date: Tanggal pengajuan reimbursementn</li>
                            <li>Amount: Jumlah yang diajukan untuk reimbursement.</li>
                            <li>Image: Unggah bukti pengeluaran (misalnya, kuitansi atau faktur).</li>
                            <li>Description: Tambahkan deskripsi singkat mengenai pengeluaran untuk klien</li>
                        </ul>
                    </li>
                    <li>Setelah semua field terisi, klik Create untuk menyimpan pengajuan</li>
                </ul>
                <p>Catatan: Penginputan reimbursement client harus dilakukan maksimal 1 minggu setelah transaksi keluar. Pastikan semua informasi akurat dan tepat waktu untuk memastikan proses penggantian berjalan lancar.</p>
            </section>
        </section>

        <section id="project-management">
            <h2>5.0 Akses Modul Project Management</h2>
            <p>Anda dapat mengakses modul Project Management untuk melakukan berbagai aktivitas terkait manajemen proyek. Di modul ini, Anda dapat:</p>
            <ul>
                <li>Menginputkan Data Project – Membuat dan mencatat detail proyek baru, termasuk judul, deskripsi, dan timeline proyek.</li>
                <li>Menginputkan Data Task – Mendefinisikan dan mengatur tugas-tugas yang perlu diselesaikan dalam setiap proyek.</li>
                <li>Menginputkan Data Time Budget – Mengalokasikan anggaran waktu yang diperlukan untuk setiap tugas atau sub-tugas.</li>
                <li>Menginputkan Data Sub Task – Membagi tugas besar menjadi beberapa sub-tugas yang lebih terperinci.</li>
                <li>Melihat Data Tracker per Project – Memantau perkembangan dan status dari setiap proyek yang sedang berjalan.</li>
                <li>Mengundang Anggota ke Project – Menambahkan anggota tim untuk berkolaborasi dalam proyek tertentu.</li>
                <li>Melihat Data Timesheet per Project – Memantau jam kerja yang sudah dihabiskan oleh setiap anggota tim dalam proyek tersebut.</li>
                <li>Checklist Completed Project – Menandai proyek yang sudah selesai berdasarkan kriteria yang telah ditentukan.</li>
                <li>Melihat Data Project Progress Report – Melihat laporan perkembangan proyek secara keseluruhan, termasuk milestone yang sudah dicapai.</li>
                <li>Melihat Timesheet Report – Mengakses laporan detail jam kerja untuk setiap proyek yang sedang berlangsung.</li>
                <li>Melihat Project Tracker – Mengawasi jalannya proyek melalui tracker yang menampilkan aktivitas dan perkembangan tugas secara visual.</li>
            </ul>
            <section id="input-project-data">
                <h3>5.1 Menginputkan Data Project</h3>
                <p>User dapat menginputkan data project baru dengan langkah-langkah berikut:</p>
                <ul>
                    <li>Akses modul Project Management dan klik fitur Projects.</li>
                    <li>Anda akan melihat daftar project yang sudah dibuat oleh admin.</li>
                    <li>Untuk membuat project baru, klik tombol + di kanan atas.</li>
                    <li>Sistem akan menampilkan popup form.</li>
                    <li>Isi semua field yang tersedia dalam form tersebut:</li>
                    <ul>
                        <li><strong>Project Name:</strong> Nama project.</li>
                        <li><strong>Start Date:</strong> Tanggal mulai project.</li>
                        <li><strong>Total Days Working:</strong> Jumlah hari kerja yang dialokasikan untuk project.</li>
                        <li><strong>Project Image (optional):</strong> Tambahkan gambar project jika diperlukan.</li>
                        <li><strong>Public Accountant:</strong> Nama akuntan publik yang terlibat.</li>
                        <li><strong>Client:</strong> Nama klien yang terkait dengan project.</li>
                        <li><strong>Budget:</strong> Anggaran yang dialokasikan untuk project.</li>
                        <li><strong>Team Leader:</strong> Pemimpin tim yang bertanggung jawab atas project.</li>
                        <li><strong>Task Template (optional):</strong> Template tugas yang digunakan, jika ada.</li>
                        <li><strong>Label:</strong> Label untuk mengelompokkan project.</li>
                        <li><strong>Description:</strong> Deskripsi singkat mengenai project.</li>
                        <li><strong>Tag:</strong> Tambahkan tag untuk mempermudah pencarian.</li>
                        <li><strong>Book Year:</strong> Tahun pembukuan project.</li>
                        <li><strong>Status:</strong> Status project (misalnya, ongoing atau completed).</li>
                    </ul>
                    <li>Setelah semua field terisi, klik <strong>Create</strong> untuk menyimpan project.</li>
                </ul>
                <p>Pastikan data yang diinputkan akurat agar project dapat dikelola dengan baik.</p>
            </section>
        
            <!-- Section 5.2: Menginputkan Data Task -->
            <section id="input-task-data">
                <h3>5.2 Menginputkan Data Task</h3>
                <p>Untuk menginputkan data task baru ke dalam proyek, ikuti langkah-langkah berikut:</p>
                <ul>
                    <li>Akses modul Project Management dan klik fitur Projects.</li>
                    <li>Pilih project yang ingin Anda tambahkan task dengan cara mengklik nama project tersebut.</li>
                    <li>Setelah memilih project, sistem akan menampilkan dashboard project yang berisi detail project tersebut.</li>
                    <li>Untuk menginputkan task, klik tombol Task di kanan atas.</li>
                    <li>Sistem akan menampilkan list task yang ada, termasuk task yang menggunakan template. Anda bisa menyesuaikan task tersebut (edit, tambahkan, atau hapus sesuai kebutuhan).</li>
                    <li>Untuk menambahkan task baru, klik tombol + di kanan atas.</li>
                    <li>Sistem akan menampilkan popup form untuk menginputkan data task.</li>
                    <li>Isi semua field yang ada di form tersebut:</li>
                    <ul>
                        <li><strong>Task Name:</strong> Nama task yang akan dikerjakan.</li>
                        <li><strong>Group Name (jika project audit):</strong> Nama grup yang terkait dengan task.</li>
                        <li><strong>Priority:</strong> Tentukan prioritas task (misalnya, high, medium, low).</li>
                        <li><strong>Members:</strong> Pilih anggota tim yang akan mengerjakan task tersebut.</li>
                    </ul>
                    <li>Setelah semua field terisi, klik <strong>Create</strong> untuk menyimpan task.</li>
                </ul>
                <p>Dengan langkah ini, Anda dapat mengelola task dalam project sesuai dengan kebutuhan dan memastikan setiap anggota tim bekerja pada tugas yang sesuai.</p>
            </section>

            <section id="input-time-budget">
                <h3>5.3 Menginputkan Data Time Budget</h3>
                <p>Untuk menginputkan data time budget dalam sebuah project, ikuti langkah-langkah berikut:</p>
                <ul>
                    <li>Akses modul Project Management dan klik fitur Projects.</li>
                    <li>Pilih project yang ingin Anda tambahkan time budget dengan cara mengklik ikon jam yang terdapat pada project tersebut.</li>
                    <li>Sistem akan menampilkan popup form untuk pengisian data time budget.</li>
                    <li>Isi semua field yang tersedia di form tersebut:
                        <ul>
                            <li>Project Hour Partner: Jumlah jam yang dialokasikan untuk Partner.</li>
                            <li>Rate Partner: Tarif per jam untuk Partner.</li>
                            <li>Project Hour Manager: Jumlah jam yang dialokasikan untuk Manager.</li>
                            <li>Rate Manager: Tarif per jam untuk Manager.</li>
                            <li>Project Hour Senior Associate: Jumlah jam yang dialokasikan untuk Senior Associate.</li>
                            <li>Rate Senior Associate: Tarif per jam untuk Senior Associate.</li>
                            <li>Project Hour Associate: Jumlah jam yang dialokasikan untuk Associate.</li>
                            <li>Rate Associate: Tarif per jam untuk Associate.</li>
                            <li>Project Hour Assistant: Jumlah jam yang dialokasikan untuk Assistant.</li>
                            <li>Rate Assistant: Tarif per jam untuk Assistant.</li>
                        </ul>
                    </li>
                    <li>Setelah semua field diisi, sistem akan secara otomatis menghitung dan mengisi Total Project Hour dan Total Charge Out.</li>
                    <li>Jika ingin menggunakan template rate yang sudah tersedia, klik tombol Template Rate.</li>
                    <li>Klik Create untuk menyimpan time budget.</li>
                </ul>
                <p>Dengan fitur ini, Anda bisa mengelola alokasi waktu dan anggaran setiap level dalam tim secara efektif.</p>
            </section>
        
            <!-- Section 5.4: Menginputkan Data Subtask -->
            <section id="input-subtask-data">
                <h3>5.4 Menginputkan Data Subtask</h3>
                <p>Untuk menginputkan data sub task dalam sebuah project, ikuti langkah-langkah berikut:</p>
                <ul>
                    <li>Akses modul Project Management dan klik fitur Projects.</li>
                    <li>Pilih project yang ingin Anda tambahkan sub task dengan cara mengklik nama project.</li>
                    <li>Sistem akan menampilkan dashboard project yang Anda pilih.</li>
                    <li>Klik tombol Task di kanan atas untuk melihat daftar task dalam project tersebut.</li>
                    <li>Pilih task yang ingin Anda tambahkan sub task dengan cara mengklik nama task tersebut.</li>
                    <li>Sistem akan menampilkan form sub task di dalam detail task yang dipilih.</li>
                    <li>Klik tombol + pada bagian Sub Task untuk menambahkan sub task baru.</li>
                    <li>Isi semua field dalam form tersebut:
                        <ul>
                            <li>Sub Task Name: Nama sub task yang ingin Anda buat.</li>
                            <li>Description (optional): Deskripsi singkat tentang sub task.</li>
                            <li>Link (optional): Tautan terkait sub task (jika diperlukan).</li>
                        </ul>
                    </li>
                    <li>Klik tombol Checklist untuk menyimpan sub task.</li>
                </ul>
                <p>Dengan langkah ini, Anda dapat mengelola sub task secara detail dan memastikan semua sub task di dalam project terorganisir dengan baik.</p>
            </section>
        
            <!-- Section 5.5: Melihat Data Tracker Per Project -->
            <section id="view-tracker">
                <h3>5.5 Melihat Data Tracker Per Project</h3>
                <p>Untuk melihat data tracker dari user yang tergabung dalam sebuah project, ikuti langkah-langkah berikut:</p>
                <ul>
                    <li>Akses modul Project Management dan klik fitur Projects.</li>
                    <li>Pilih project yang ingin Anda lihat data trackernya dengan cara mengklik nama project.</li>
                    <li>Sistem akan menampilkan dashboard project yang Anda pilih.</li>
                    <li>Klik tombol Tracker di kanan atas.</li>
                    <li>Sistem akan menampilkan data tracker dari user yang tergabung di project tersebut.</li>
                    <li>Gunakan filter yang telah disediakan untuk menyaring informasi.</li>
                </ul>
                <p>Dengan fitur ini, Anda dapat memantau progres pekerjaan setiap anggota tim secara detail.</p>
            </section>
        
            <!-- Section 5.6: Mengundang Anggota ke Project -->
            <section id="invite-members">
                <h3>5.6 Mengundang Anggota ke Project</h3>
                <p>Untuk mengundang anggota lain ke dalam project, ikuti langkah-langkah berikut:</p>
                <ul>
                    <li>Akses modul Project Management dan klik fitur Projects.</li>
                    <li>Pilih project yang ingin Anda tambahkan anggota dengan cara mengklik nama project.</li>
                    <li>Sistem akan menampilkan dashboard project yang Anda pilih.</li>
                    <li>Gulir ke bawah hingga menemukan section Members.</li>
                    <li>Klik tombol + di bagian Members.</li>
                    <li>Sistem akan menampilkan form data user.</li>
                    <li>Pilih user yang ingin Anda undang ke project dengan mengklik tombol + di samping nama user tersebut.</li>
                </ul>
                <p>Dengan langkah ini, user akan diundang dan bergabung dalam project.</p>
            </section>
        
            <!-- Section 5.7: Melihat Data Timesheet Per Project -->
            <section id="view-timesheet">
                <h3>5.7 Melihat Data Timesheet Per Project</h3>
                <p>Untuk melihat data timesheet dari user yang tergabung dalam sebuah project, ikuti langkah-langkah berikut:</p>
                <ul>
                    <li>Akses modul Project Management dan klik fitur Projects.</li>
                    <li>Pilih project yang ingin Anda lihat data timesheetnya dengan cara mengklik nama project.</li>
                    <li>Sistem akan menampilkan dashboard project yang Anda pilih.</li>
                    <li>Klik tombol Timesheet di kanan atas.</li>
                    <li>Sistem akan menampilkan data timesheet dari user yang tergabung di project tersebut.</li>
                    <li>Gunakan filter yang telah disediakan untuk menyaring informasi.</li>
                </ul>
                <p>Dengan fitur ini, Anda dapat memantau progres pekerjaan setiap anggota tim secara detail.</p>
            </section>
        
            <!-- Section 5.8: Checklist Completed Project -->
            <section id="checklist-completed">
                <h3>5.8 Checklist Completed Project</h3>
                <p>Untuk mengupdate status project menjadi completed, ikuti langkah-langkah berikut:</p>
                <ul>
                    <li>Akses modul Project Management dan klik fitur Projects.</li>
                    <li>Pilih project yang ingin Anda checklist dengan cara mengklik nama project.</li>
                    <li>Sistem akan menampilkan dashboard project.</li>
                    <li>Pastikan Anda mengubah status task dari "IN PROGRESS" menjadi "DONE".</li>
                    <li>Setelah semua task diubah menjadi "DONE", ubah status project menjadi "COMPLETE".</li>
                    <li>Klik tombol pensil untuk mengedit project dan ubah statusnya.</li>
                </ul>
                <p>Dengan langkah ini, project yang selesai dapat diupdate dengan benar.</p>
            </section>
        
            <!-- Section 5.9: Melihat Data Project Progress Report -->
            <section id="project-progress-report">
                <h3>5.9 Melihat Data Project Progress Report</h3>
                <p>Untuk melihat data project progress report, ikuti langkah-langkah berikut:</p>
                <ul>
                    <li>Akses modul Project Management dan klik fitur Reports.</li>
                    <li>Pilih Project Progress Report.</li>
                    <li>Gunakan filter untuk mencari data berdasarkan kriteria tertentu.</li>
                    <li>Klik nama project untuk melihat detail report.</li>
                </ul>
                <p>Informasi ini membantu memantau dan menganalisis progres project secara menyeluruh.</p>
            </section>
        
            <!-- Section 5.10: Melihat Data Timesheet Reports -->
            <section id="timesheet-report">
                <h3>5.10 Melihat Data Timesheet Reports</h3>
                <p>Untuk melihat data timesheet report, ikuti langkah-langkah berikut:</p>
                <ul>
                    <li>Akses modul Project Management dan klik fitur Reports.</li>
                    <li>Pilih Timesheet Report.</li>
                    <li>Gunakan filter untuk mencari data timesheet berdasarkan kriteria tertentu.</li>
                </ul>
                <p>Dengan langkah ini, Anda dapat memantau waktu kerja yang dicatat oleh anggota tim.</p>
            </section>

            <section id="project-tracker">
                <h3>5.11 Melihat Data Project Tracker</h3>
                <p>Untuk melihat data project tracker report, ikuti langkah-langkah berikut:</p>
                <ul>
                    <li>Akses modul Project Management dan klik fitur Reports.</li>
                    <li>Pilih Project Tracker.</li>
                    <li>Gunakan filter untuk mencari data tracker berdasarkan kriteria tertentu.</li>
                </ul>
                <p>Dengan langkah ini, Anda dapat memantau dan menganalisis data tracker untuk setiap project, memastikan semua aktivitas terlacak dengan baik. Informasi ini membantu dalam evaluasi kinerja dan pengelolaan sumber daya, serta memberikan transparansi kepada semua anggota tim dan stakeholder terkait progres project.</p>
            </section>
        </section>
        <section id="document-request">
            <h2>6.0 Akses Modul Document Request</h2>
            <p>Pada langkah ini, user dapat melakukan permintaan dokumen yang diperlukan dengan fitur Document Request. Berikut langkah-langkahnya : </p>
            <ul>
                <li>Masuk ke modul Document Request.</li>
                <li>Sistem akan menampilkan daftar permintaan dokumen yang sudah ada</li>
                <li>Untuk membuat permintaan dokumen baru, klik tombol +.</li>
                <li>Isi semua field yang tersedia dalam form popup, yaitu</li>
                <ul>
                    <li>Approval By: Pilih siapa yang akan menyetujui permintaan dokumen</li>
                    <li>Document Type: Pilih jenis dokumen yang diperlukan</li>
                    <li>Detail Document Type: Rincian tipe dokumen yang dipilih</li>
                    <li>Note (opsional): Catatan tambahan terkait permintaan dokumen</li>
                </ul>
                <li>Klik Create untuk mengajukan permintaan dokumen.</li>
            </ul>
            <p>User dapat mengunduh file yang sudah disiapkan dari kolom File (khusus untuk dokumen bertipe other letters dan contract employee), sehingga memastikan semua kebutuhan dokumen terpenuhi secara terorganisir dan efisien.</p>
        </section>
        <section id="support-ticket">
            <h2>7.0 Akses Modul Support Ticket</h2>
            <p>Pada langkah ini, user dapat melaporkan bug atau memberikan saran perbaikan untuk aplikasi melalui Support Ticket. Berikut adalah cara melakukannya : </p>
            <ul>
                <li>Klik modul Support Ticket</li>
                <li>Sistem akan menampilkan daftar ticket pengaduan yang sudah dibuat sebelumnya</li>
                <li>Untuk membuat pengaduan baru, klik tombol +.</li>
                <li>Isi semua field pada form popup, termasuk:</li>
                <ul>
                    <li>Subject: Topik atau judul pengaduan</li>
                    <li>Support for User: Pilih user yang akan diberikan dukungan</li>
                    <li>Priority: Tentukan prioritas pengaduan.</li>
                    <li>Bukti bug/error: Unggah bukti terkait bug atau error yang ditemukan.</li>
                    <li>Description: Berikan deskripsi detail mengenai pengaduan atau saran.</li>
                </ul>
                <li>Setelah semua field terisi, klik Create</li>
            </ul>
            <p>Setelah membuat ticket, sistem secara otomatis akan mengarahkan user ke WhatsApp untuk mengirimkan pesan kepada tim support. User dapat menunggu tim support menangani ticket hingga masalah terselesaikan.</p>
        </section>
    </div>

    <script>
        // Toggle Sidebar for Mobile
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("show");
        }
    </script>
</body>
</html>
