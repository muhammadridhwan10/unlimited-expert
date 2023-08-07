@extends('layouts.admin')
@section('page-title')
    {{ucwords($task->name)}}
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
@endpush
@push('script-page')
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
    <script>
        document.getElementById('prosedur_pmpj').addEventListener('change', function() {
            var selectedValue = this.value;
            var viewYes = document.getElementById('viewYesPPJ');
            var viewNo = document.getElementById('viewNoPPJ');
            var viewYesPMPJ = document.getElementById('viewYesPMPJ');

            console.log(selectedValue);

            if (selectedValue === 'yes') {
                viewYes.style.display = 'block';
                viewNo.style.display = 'none';
                viewYesPMPJ.style.display = 'none';
            } else if (selectedValue === 'no') {
                viewYes.style.display = 'none';
                viewNo.style.display = 'block';
                viewYesPMPJ.style.display = 'none';
            } else {
                viewYes.style.display = 'none';
                viewNo.style.display = 'none';
                viewYesPMPJ.style.display = 'none';
            }
        });
    </script>
    
    <script>
    function toggleView() {
        var viewYes = document.getElementById('viewYesPMPJ');
        var button = document.getElementById('pmpj_sederhana');
        var hiddenInput = document.getElementById('pmpj_sederhana_input');

        if (viewYes.style.display === 'none') {
            viewYes.style.display = 'block';
            button.textContent = 'Yes';
            hiddenInput.value = 'yes'; // Set the value of the hidden field to 'yes'
        } else {
            viewYes.style.display = 'none';
            button.textContent = 'No';
            hiddenInput.value = 'no'; // Set the value of the hidden field to 'no'
        }
    }
</script>

    <script>
        document.getElementById('ruang_lingkup_jasa').addEventListener('change', function() {
            var selectedValue = this.value;
            var viewYes = document.getElementById('viewYes');
            var viewNo = document.getElementById('viewNo');

            console.log(selectedValue);

            if (selectedValue === 'yes') {
                viewYes.style.display = 'block';
                viewNo.style.display = 'none';
            } else if (selectedValue === 'no') {
                viewYes.style.display = 'none';
                viewNo.style.display = 'block';
            } else {
                viewYes.style.display = 'none';
                viewNo.style.display = 'none';
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form1 = document.getElementById("form1");
            const form2 = document.getElementById("form2");

            function showForm(formToShow) {
                formToShow.style.display = "block";
                formToShow.querySelectorAll("select, input, textarea").forEach(input => {
                    input.setAttribute("required", "required");
                });
            }

            function hideForm(formToHide) {
                formToHide.style.display = "none";
                formToHide.querySelectorAll("select, input, textarea").forEach(input => {
                    input.removeAttribute("required");
                });
            }

            function updateFormVisibility() {
                const actingForWhom = document.getElementById("pengguna_jasa_bertindak_untuk").value;

                if (actingForWhom === "bo") {
                    showForm(form1);
                    showForm(form2);
                } else if (actingForWhom === "sendiri") {
                    showForm(form1);
                    hideForm(form2);
                } else {
                    hideForm(form1);
                    hideForm(form2);
                }
            }

            document.getElementById("pengguna_jasa_bertindak_untuk").addEventListener("change", updateFormVisibility);

            updateFormVisibility();
        });
    </script>
    <script>
        const profilPenggunaJasaDropdown = document.getElementById('profil_pengguna_jasa');
        const risikoPpjInput = document.getElementById('risiko_ppj');
        const profilBisnisPenggunaJasaDropdown = document.getElementById('profil_bisnis_pengguna_jasa');
        const risikoPbpjInput = document.getElementById('risiko_pbpj');
        const DomisiliDropdown = document.getElementById('domisili_pengguna_jasa');
        const risikoDomisiliInput = document.getElementById('risiko_domisili');
        const ExposePersonDropdown = document.getElementById('politically_exposed_person');
        const risikoExposePersonInput = document.getElementById('risiko_exposeperson');
        const FATFDropdown = document.getElementById('transaksi_negara_risiko_tinggi');
        const risikoFATFInput = document.getElementById('risiko_fatf');

        profilPenggunaJasaDropdown.addEventListener('change', function () {
            const selectedValuePPJ = profilPenggunaJasaDropdown.value;

            const riskLevelsPPJ = {
                'a': 'Tinggi',
                'b': 'Tinggi',
                'c': 'Tinggi',
                'd': 'Sedang',
                'e': 'Sedang',
                'f': 'Sedang',
                'g': 'Sedang',
                'h': 'Sedang',
                'i': 'Sedang',
                'j': 'Sedang',
                'k': 'Rendah',
                'l': 'Rendah',
                'm': 'Rendah',
                'n': 'Rendah',
                'o': 'Rendah',
                'p': 'Rendah',
                'q': 'Rendah',
            };

            risikoPpjInput.value = riskLevelsPPJ[selectedValuePPJ] || '';
        });

        profilBisnisPenggunaJasaDropdown.addEventListener('change', function () {
            const selectedValuePBPJ = profilBisnisPenggunaJasaDropdown.value;

            const riskLevelsPBPJ = {
                'a': 'Tinggi',
                'b': 'Tinggi',
                'c': 'Tinggi',
                'd': 'Tinggi',
                'e': 'Tinggi',
                'f': 'Sedang',
                'g': 'Sedang',
                'h': 'Sedang',
                'i': 'Sedang',
                'j': 'Rendah',
                'k': 'Rendah',
                'l': 'Rendah',
                'm': 'Rendah',
                'n': 'Rendah',
            };

            risikoPbpjInput.value = riskLevelsPBPJ[selectedValuePBPJ] || '';
        });

        DomisiliDropdown.addEventListener('change', function () {
            const selectedValueDom = DomisiliDropdown.value;

            const riskLevelsDom = {
                'a': 'Tinggi',
                'b': 'Tinggi',
                'c': 'Tinggi',
                'd': 'Sedang',
                'e': 'Sedang',
                'f': 'Sedang',
                'g': 'Sedang',
                'h': 'Rendah',
                'i': 'Tinggi',
                'j': 'Tinggi',
                'k': 'Sedang',
                'l': 'Rendah',
            };

            risikoDomisiliInput.value = riskLevelsDom[selectedValueDom] || '';
        });

        ExposePersonDropdown.addEventListener('change', function () {
            const selectedValueEP = ExposePersonDropdown.value;

            const riskLevelsEP = {
                'a': 'Tinggi',
                'b': 'Tinggi',
                'c': 'Tinggi',
                'd': 'Tinggi',
                'e': 'Tinggi',
                'f': 'Rendah',
            };

            risikoExposePersonInput.value = riskLevelsEP[selectedValueEP] || '';
        });

        FATFDropdown.addEventListener('change', function () {
            const selectedValueFATF = FATFDropdown.value;

            const riskLevelsFATF = {
                'yes': 'Tinggi',
                'no': 'Rendah',
            };

            risikoFATFInput.value = riskLevelsFATF[selectedValueFATF] || '';
        });

        function getConclusion(risiko_ppj, risiko_pbpj, risiko_domisili, risiko_exposeperson, risiko_fatf) {
            let tinggiExposePersonFatf = 0;
            if (risiko_exposeperson === 'Tinggi') {
                tinggiExposePersonFatf++;
            }
            if (risiko_fatf === 'Tinggi') {
                tinggiExposePersonFatf++;
            }

            if (tinggiExposePersonFatf > 0) {
                return 'TINGGI';
            }

            let tinggiCount = 0;
            if (risiko_ppj === 'Tinggi') {
                tinggiCount++;
            }
            if (risiko_pbpj === 'Tinggi') {
                tinggiCount++;
            }
            if (risiko_domisili === 'Tinggi') {
                tinggiCount++;
            }

            if (tinggiCount === 1) {
                return 'RENDAH';
            } else if (tinggiCount === 2 || tinggiCount === 3) {
                return 'SEDANG';
            } else {
                return '';
            }
        }

        function fetchConclusionFromDatabase() {
            fetch("{{route('get.kesimpulan',[$project->id, $task->id])}}")
            .then(response => response.json())
            .then(data => {
                const kesimpulanElement = document.getElementById('kesimpulan');
                if (kesimpulanElement) {
                    kesimpulanElement.textContent = `Kesimpulan dari analisis risiko di atas adalah pengguna jasa / BO beresiko ${data.kesimpulan}`;
                }

                // Additionally, you can update the other view elements based on the fetched conclusion
                const viewTinggiElement = document.getElementById('viewTinggiProsedur');
                const viewRendahElement = document.getElementById('viewRendahProsedur');

                if (viewTinggiElement && viewRendahElement) {
                    if (data.kesimpulan === 'TINGGI') {
                        viewTinggiElement.style.display = 'block';
                        viewRendahElement.style.display = 'none';
                    } else if (data.kesimpulan === 'RENDAH') {
                        viewTinggiElement.style.display = 'none';
                        viewRendahElement.style.display = 'block';
                    } else if (data.kesimpulan === 'SEDANG') {
                        viewTinggiElement.style.display = 'none';
                        viewRendahElement.style.display = 'block';
                    } else {
                        viewTinggiElement.style.display = 'none';
                        viewRendahElement.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching kesimpulan:', error);
            });

        }

        function updateConclusion() {
            // Ambil value dari variabel risiko_exposeperson dan risiko_fatf
            const risikoExposePerson = risikoExposePersonInput.value;
            const risikoFATF = risikoFATFInput.value;

            // Ambil value dari variabel risiko_ppj, risiko_pbpj, dan risiko_domisili
            const risikoPPJ = risikoPpjInput.value;
            const risikoPBPJ = risikoPbpjInput.value;
            const risikoDomisili = risikoDomisiliInput.value;

            const conclusion = getConclusion(risikoPPJ, risikoPBPJ, risikoDomisili, risikoExposePerson, risikoFATF);
            const conclusionInput = document.getElementById('conclusionInput');
            conclusionInput.value = conclusion;

            const kesimpulanElement = document.getElementById('kesimpulan');
            const viewTinggiElement = document.getElementById('viewTinggiProsedur');
            const viewRendahElement = document.getElementById('viewRendahProsedur');

            if (kesimpulanElement && viewTinggiElement && viewRendahElement) {
                kesimpulanElement.textContent = `Kesimpulan dari analisis risiko di atas adalah pengguna jasa / BO beresiko ${conclusion}`;

                // Tampilkan atau sembunyikan view berdasarkan kesimpulan
                if (conclusion === 'TINGGI') {
                    viewTinggiElement.style.display = 'block';
                    viewRendahElement.style.display = 'none';
                } 
                else if (conclusion === 'RENDAH') {
                    viewTinggiElement.style.display = 'none';
                    viewRendahElement.style.display = 'block';
                }
                else if (conclusion === 'SEDANG') {
                    viewTinggiElement.style.display = 'none';
                    viewRendahElement.style.display = 'block';
                }
                else {
                    viewTinggiElement.style.display = 'none';
                    viewRendahElement.style.display = 'none';
                }
            }
        }

        // Panggil fungsi updateConclusion setiap kali ada perubahan pada input
        profilPenggunaJasaDropdown.addEventListener('change', updateConclusion);
        profilBisnisPenggunaJasaDropdown.addEventListener('change', updateConclusion);
        DomisiliDropdown.addEventListener('change', updateConclusion);
        ExposePersonDropdown.addEventListener('change', updateConclusion);
        FATFDropdown.addEventListener('change', updateConclusion);

        // Call the fetchConclusionFromDatabase function when the page loads
        window.onload = fetchConclusionFromDatabase;

        function showNotification() {
            alert("Maaf, Sedang Dalam Tahap Pengembangan.");
        }

    </script>





@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.index',$project->id)}}">{{__('Task')}}</a></li>
    <li class="breadcrumb-item">{{__($task->name)}}</li>
@endsection

@section('content')
{{ Form::open(['route' => ['save-pmpj',[$project->id, $task->id]], 'method' => 'post']) }}
    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
            <div class="card-header"><h6 class="mb-0">{{__('I. PEMETAAN RUANG LINGKUP JASA')}}</h6></div>
                <div class="col-12">
                    <div class="card-body table-border-style">
                        <p>Apakah ruang lingkup pemberian jasa meliputi hal berikut : 
                            <br/> 1.pembelian dan penjualan properti;
                            <br/> 2.pengelolaan terhadap uang, efek, dan/ atau produk jasa keuangan lainnya;
                            <br/> 3.pengelolaan rekening giro, rekening tabungan, rekening deposito, dan/ atau rekening efek;
                            <br/> 4.pengoperasian dan pengelolaan perusahaan; atau
                            <br/> 5.pendirian, pembelian, dan penjualan badan hukum.
                        </p>
                        <td>
                            <select class="form-control select" name="ruang_lingkup_jasa" id="ruang_lingkup_jasa" style="width: 150px;">
                                @if(is_object($value_pmpj))
                                    <option value="0" {{ ($value_pmpj->ruang_lingkup_jasa == '0') ? 'selected' : '' }}>Yes or No</option>
                                    <option value="yes" {{ ($value_pmpj->ruang_lingkup_jasa == 'yes') ? 'selected' : '' }}>Yes</option>
                                    <option value="no" {{ ($value_pmpj->ruang_lingkup_jasa == 'no') ? 'selected' : '' }}>No</option>
                                @else
                                    <option value="0">Yes or No</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                @endif
                            </select>
                        </td>
                        <br>
                            <div id="viewNo" style="display: {{ (is_object($value_pmpj) && $value_pmpj->ruang_lingkup_jasa == 'no') ? 'block' : 'none' }}">
                                <p style="color:red"><em>*Jasa yang diberikan merupakan kategori PMPJ berisiko rendah, sehingga dapat dilaksanakan Prosedur PMPJ Sederhana dan dapat tidak diterapkan analisis risiko.</em></p>
                            </div>
                            <div id="viewYes" style="display: {{ (is_object($value_pmpj) && $value_pmpj->ruang_lingkup_jasa == 'yes') ? 'block' : 'none' }}">
                                <p style="color:red"><em>*PMPJ Mendalam sedang dalam tahap pengembangan aplikasi, sementara dapat didokumentasikan secara manual di excel.</em></p>
                            </div>
                    </div>
                </div>
                <div class="card-header"><h6 class="mb-0">{{__('Ringkasan Analisis Risiko Pengguna Jasa / BO')}}</h6></div>
                <div class="col-12">
                    <div class="card-body table-border-style">
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    <p class="form-control">Profil Pengguna Jasa / BO</p>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-sm-4 col-md-6">
                                                <div class="form-group">
                                                    <select class="form-control select" name="profil_pengguna_jasa" id="profil_pengguna_jasa" style="width: 400px;">
                                                            @foreach(\App\Models\ProjectTask::$profilPenggunaJasa as $key => $val)
                                                                <option value="{{ $key }}" {{ (is_object($value_pmpj) && $key == $value_pmpj->profil_pengguna_jasa) ? 'selected' : '' }}>{{ __($val) }}</option>
                                                            @endforeach
                                                    </select>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-sm-12 col-md-12">
                                                <div class="form-group">
                                                        <input type="text" name="risiko_ppj" id="risiko_ppj" class="form-control ppj" readonly="true" style="width: 150px;" value="{{ is_object($value_pmpj) ? $value_pmpj->risiko_ppj : '' }}" />
                                                </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    <p class="form-control">Profil Bisnis Pengguna Jasa / BO</p>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-sm-4 col-md-6">
                                                <div class="form-group">
                                                    <select class="form-control select" name="profil_bisnis_pengguna_jasa" id="profil_bisnis_pengguna_jasa" style="width: 400px;">
                                                        @foreach(\App\Models\ProjectTask::$profilBisnisPenggunaJasa as $key => $val)
                                                            <option value="{{ $key }}" {{ (is_object($value_pmpj) && $key == $value_pmpj->profil_bisnis_pengguna_jasa) ? 'selected' : '' }}>{{ __($val) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <input type="text" name="risiko_pbpj" id="risiko_pbpj" class="form-control risiko_pbpj" readonly="true" style="width: 150px;" value="{{ is_object($value_pmpj) ? $value_pmpj->risiko_pbpj : '' }}" />
                                                </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    <p class="form-control">Domisili Pengguna Jasa / BO</p>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-sm-4 col-md-6">
                                                <div class="form-group">
                                                    <select class="form-control select" name="domisili_pengguna_jasa" id="domisili_pengguna_jasa" style="width: 400px;">
                                                        @foreach(\App\Models\ProjectTask::$Domisili as $key => $val)
                                                            <option value="{{ $key }}" {{ (is_object($value_pmpj) && $key == $value_pmpj->domisili_pengguna_jasa) ? 'selected' : '' }}>{{ __($val) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <input type="text" name="risiko_domisili" id="risiko_domisili" class="form-control risiko_domisili" readonly="true" style="width: 150px;" value="{{ is_object($value_pmpj) ? $value_pmpj->risiko_domisili : '' }}"/>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <h6 class="mb-auto">{{__('Keterlibatan Pengguna Jasa / BO')}}</h6>
                        <br>
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    <p>Apakah Pengguna jasa / BO merupakan orang yang populer secara politis (Politically Exposed Person) ?</p>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-sm-4 col-md-6">
                                               <div class="form-group">
                                                    <select class="form-control select" name="politically_exposed_person" id="politically_exposed_person" style="width: 400px;">
                                                        @foreach(\App\Models\ProjectTask::$expose_person as $key => $val)
                                                            <option value="{{ $key }}" {{ (is_object($value_pmpj) && $key == $value_pmpj->politically_exposed_person) ? 'selected' : '' }}>{{ __($val) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-sm-12 col-md-12">
                                                <div class="form-group">
                                                     <input type="text" name="risiko_exposeperson" id="risiko_exposeperson" class="form-control risiko_exposeperson" readonly="true" style="width: 150px;" value="{{ is_object($value_pmpj) ? $value_pmpj->risiko_exposeperson : '' }}" />
                                                </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    <p>Apakah Pengguna Jasa / BO melakukan transaksi dengan pihak dari negara berisiko tinggi sesuai daftar rekomendasi Financial Action Task Force (FATF) ?</p>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-sm-4 col-md-6">
                                                <div class="form-group">
                                                    <select class="form-control select" name="transaksi_negara_risiko_tinggi" id="transaksi_negara_risiko_tinggi" style="width: 400px;">
                                                        @foreach(\App\Models\ProjectTask::$fatf as $key => $val)
                                                            <option value="{{ $key }}" {{ (is_object($value_pmpj) && $key == $value_pmpj->transaksi_negara_risiko_tinggi) ? 'selected' : '' }} >{{ __($val) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-sm-12 col-md-12">
                                                <div class="form-group">
                                                      <input type="text" name="risiko_fatf" id="risiko_fatf" class="form-control risiko_fatf" readonly="true" style="width: 150px;" value="{{ is_object($value_pmpj) ? $value_pmpj->risiko_fatf : '' }}" />
                                                </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        
                        <p name="kesimpulan" id="kesimpulan"></p>
                        <input type="hidden" name="kesimpulan" id="conclusionInput" value="">
                        <div id="viewTinggiProsedur" style="display: none;">
                            <td>
                                <a class="btn btn-simpan btn-primary" href="javascript:void(0);" onclick="showNotification()">Lakukan PMPJ lanjutan</a>
                            </td>
                        </div>
                    </div>
                </div>
                
            </div>


            <div id="viewRendahProsedur" style="display: none;">
                <div class="card">
                <div class="card-header"><h6 class="mb-0">{{__('II. KOMUNIKASI DENGAN PENGGUNA JASA')}}</h6></div>
                    <div class="col-12">
                        <div class="card-body table-border-style">
                            <p>Apakah klien setuju dengan prosedur PMPJ? 
                            </p>
                            <td>
                                <select class="form-control select" name="prosedur_pmpj" id="prosedur_pmpj" style="width: 150px;">
                                    @if(is_object($value_pmpj))
                                        <option value="0" {{ ($value_pmpj->prosedur_pmpj == '0') ? 'selected' : '' }}>Yes or No</option>
                                        <option value="yes" {{ ($value_pmpj->prosedur_pmpj == 'yes') ? 'selected' : '' }}>Yes</option>
                                        <option value="no" {{ ($value_pmpj->prosedur_pmpj == 'no') ? 'selected' : '' }}>No</option>
                                    @else
                                        <option value="0">Yes or No</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    @endif
                                </select>
                            </td>
                            <br>
                            <div id="viewNoPPJ" style="display: {{ (is_object($value_pmpj) && $value_pmpj->prosedur_pmpj == 'no') ? 'block' : 'none' }}">
                                <p>Apabila pengguna jasa tidak setuju dengan prosedur PMPJ maka wajib lapor PPATK
                                </p>
                                <td>
                                    <a class="btn btn-simpan btn-primary" href="https://goaml.ppatk.go.id/Home" target="_blank">Lapor PPATK</a>
                                </td>
                            </div>
                            <div id="viewYesPPJ" style="display: {{ (is_object($value_pmpj) && $value_pmpj->prosedur_pmpj == 'yes') ? 'block' : 'none' }}">
                                <p>Surat Pernyataan Persetujuan PMPJ (Insert Link GDrive)
                                </p>
                                <div class="form-group">
                                    @if (is_object($value_pmpj) && !empty($value_pmpj->link_surat_pernyataan))
                                        <a href="{{ $value_pmpj->link_surat_pernyataan }}" target="_blank" style="color: blue;">
                                            <input type="url" name="link_surat_pernyataan" class="form-control" value="{{ $value_pmpj->link_surat_pernyataan }}">
                                        </a>
                                    @else
                                        <input type="url" name="link_surat_pernyataan" class="form-control" value="">
                                    @endif
                                </div>
                                <p>Lakukan PMPJ Sederhana?
                                </p>
                                <td>
                                   <a class="btn btn-primary" name="pmpj_sederhana" id="pmpj_sederhana" onclick="toggleView()" value="no">Yes</a>
                                <input type="hidden" name="pmpj_sederhana" id="pmpj_sederhana_input" value="no">
                                </td>
                            </div>

                            
                        </div>
                        
                    </div>
                    
                </div>
                <div class="card" id="viewYesPMPJ" style="display: {{ (is_object($value_pmpj) && $value_pmpj->pmpj_sederhana == 'yes') ? 'block' : 'none' }}">
                <div class="card-header"><h6 class="mb-0">{{__('III. PMPJ SEDERHANA')}}</h6></div>
                    <div class="col-12">
                        <div class="card-body table-border-style">
                            <p><strong>1. Identifikasi</strong></p>
                            <td>
                            <p>Jenis pengguna jasa</p>
                                <select class="form-control select" name="jenis_pengguna_jasa" id="jenis_pengguna_jasa" style="width: 300px;">
                                    <option value="0" {{ (is_object($value_pmpj) && $value_pmpj->jenis_pengguna_jasa == '0') ? 'selected' : '' }}>Pilih jenis pengguna jasa</option>
                                    <option value="orang" {{ (is_object($value_pmpj) && $value_pmpj->jenis_pengguna_jasa == 'orang') ? 'selected' : '' }}>Pengguna Jasa Perorangan</option>
                                    <option value="korporasi" {{ (is_object($value_pmpj) && $value_pmpj->jenis_pengguna_jasa == 'korporasi') ? 'selected' : '' }}>Pengguna Jasa Korporasi</option>
                                </select>
                            </td>
                            <br>
                            <td>
                            <p>Pengguna jasa bertindak untuk siapa?</p>
                                <select class="form-control select" name="pengguna_jasa_bertindak_untuk" id="pengguna_jasa_bertindak_untuk" style="width: 300px;">
                                    <option value="0" {{ (is_object($value_pmpj) && $value_pmpj->pengguna_jasa_bertindak_untuk == '0') ? 'selected' : '' }}>Pilih jenis pengguna jasa</option>
                                    <option value="sendiri" {{ (is_object($value_pmpj) && $value_pmpj->pengguna_jasa_bertindak_untuk == 'sendiri') ? 'selected' : '' }}>Untuk diri sendiri</option>
                                    <option value="bo" {{ (is_object($value_pmpj) && $value_pmpj->pengguna_jasa_bertindak_untuk == 'bo') ? 'selected' : '' }}>Atas nama beneficial owner</option>
                                </select>
                            </td>
                            <br>
                            <div id="form1" style="display: none;">
                            <p><strong>FORM I</strong></p>
                                <div class="row">
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">Nama Pengguna Jasa</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                    @if(isset($value_pmpj->namapenggunajasa))
                                                        {{ Form::text('namapenggunajasa', $value_pmpj->namapenggunajasa, ['class' => 'form-control namapenggunajasa', 'style' => 'width: 500px']) }}
                                                    @else
                                                    {{ Form::text('namapenggunajasa', null, ['class' => 'form-control namapenggunajasa','style' => ' width: 500px']) }}
                                                    @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">No Identitas / NIB Pengguna Jasa</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->nib))
                                                            {{ Form::text('nib', $value_pmpj->nib, ['class' => 'form-control nib', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::text('nib', null, ['class' => 'form-control nib','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">Alamat Pengguna Jasa</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->alamatpengguna))
                                                            {{ Form::textarea('alamatpengguna', $value_pmpj->alamatpengguna, ['class' => 'form-control alamatpengguna', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::textarea('alamatpengguna', null, ['class' => 'form-control alamatpengguna','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">Nomor Telepon</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->no_telp))
                                                            {{ Form::text('no_telp', $value_pmpj->no_telp, ['class' => 'form-control no_telp', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::text('no_telp', null, ['class' => 'form-control no_telp','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p><strong>Pihak mempunyai wewenang atas Korporasi :</strong></p>
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">Nama</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->namapihak))
                                                            {{ Form::text('namapihak', $value_pmpj->namapihak, ['class' => 'form-control namapihak', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::text('namapihak', null, ['class' => 'form-control namapihak','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">Jabatan</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->jabatanpihak))
                                                            {{ Form::text('jabatanpihak', $value_pmpj->jabatanpihak, ['class' => 'form-control jabatanpihak', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::text('jabatanpihak', null, ['class' => 'form-control jabatanpihak','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">No Identitas</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->noidentitaspihak))
                                                            {{ Form::text('noidentitaspihak', $value_pmpj->noidentitaspihak, ['class' => 'form-control noidentitaspihak', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::text('noidentitaspihak', null, ['class' => 'form-control noidentitaspihak','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="form2" style="display: none;">
                            <p><strong>FORM II</strong></p>
                                <div class="row">
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">Nama Beneficial Owner</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->namabo))
                                                            {{ Form::text('namabo', $value_pmpj->namabo, ['class' => 'form-control namabo', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::text('namabo', null, ['class' => 'form-control namabo','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">No Identitas / NIB Beneficial Owner</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->nibbo))
                                                            {{ Form::text('nibbo', $value_pmpj->nibbo, ['class' => 'form-control nibbo', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::text('nibbo', null, ['class' => 'form-control nibbo','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">Alamat Beneficial Owner</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->nibbo))
                                                            {{ Form::textarea('alamatbo', $value_pmpj->nibbo, ['class' => 'form-control alamatbo', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::textarea('alamatbo', null, ['class' => 'form-control alamatbo','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">Nomor Telepon</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->no_telpbo))
                                                            {{ Form::textarea('no_telpbo', $value_pmpj->no_telpbo, ['class' => 'form-control no_telpbo', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::textarea('no_telpbo', null, ['class' => 'form-control no_telpbo','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p><strong>Pihak mempunyai wewenang atas BO Korporasi :</strong></p>
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">Nama</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->namapihakbo))
                                                            {{ Form::text('namapihakbo', $value_pmpj->namapihakbo, ['class' => 'form-control namapihakbo', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::text('namapihakbo', null, ['class' => 'form-control namapihakbo','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">Jabatan</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->jabatanpihakbo))
                                                            {{ Form::text('jabatanpihakbo', $value_pmpj->jabatanpihakbo, ['class' => 'form-control jabatanpihakbo', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::text('jabatanpihakbo', null, ['class' => 'form-control jabatanpihakbo','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-md-4">
                                        <div class="form-group">
                                            <div class="col-sm-3 col-md-12">
                                                    <div class="form-group">
                                                        <p class="form-control">No Identitas</p>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="col-auto">
                                                    <div class="form-group">
                                                        @if(isset($value_pmpj->noidentitaspihakbo))
                                                            {{ Form::text('noidentitaspihakbo', $value_pmpj->noidentitaspihakbo, ['class' => 'form-control noidentitaspihakbo', 'style' => 'width: 500px']) }}
                                                        @else
                                                            {{ Form::text('noidentitaspihakbo', null, ['class' => 'form-control noidentitaspihakbo','style' => ' width: 500px']) }}
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p>Kami telah mendapatkan infomasi dan dokumen terkait Pengguna Jasa yang telah diarsipkan dalam cloud storage pada tautan berikut :
                            </p>
                            <div class="form-group">
                                @if (is_object($value_pmpj) && !empty($value_pmpj->link_arsip))
                                    <a href="{{ $value_pmpj->link_arsip }}" target="_blank" style="color: blue;">
                                        <input type="url" name="link_arsip" class="form-control" value="{{ $value_pmpj->link_arsip }}">
                                    </a>
                                @else
                                    <input type="url" name="link_arsip" class="form-control" value="">
                                @endif
                            </div>
                            <br>
                            <br>
                            <br>
                            <p><strong>2. Verifikasi</strong></p>
                            <p>Deskripsikan prosedur yang telah dilakukan untuk melakukan verifikasi pengguna jasa beserta hasil dan kesimpulan!.</p>
                            <div class="row">
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-auto">
                                                <div class="form-group" style = "width: 800px">
                                                        @if(isset($value_pmpj->verifikasi))
                                                            {{ Form::textarea('verifikasi', $value_pmpj->verifikasi, ['class' => 'summernote-simple verifikasi']) }}
                                                        @else
                                                            {{ Form::textarea('verifikasi', null, ['class' => 'summernote-simple verifikasi']) }}
                                                        @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <br>
                            <br>
                            <p><strong>3. Pemantauan Transaksi</strong></p>
                            <p>Deskripsikan hasil pemantauan transaksi pengguna jasa.</p>
                            <div class="row">
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-auto">
                                                <div class="form-group" style = "width: 800px">
                                                        @if(isset($value_pmpj->ptransaksi))
                                                            {{ Form::textarea('ptransaksi', $value_pmpj->ptransaksi, ['class' => 'summernote-simple ptransaksi']) }}
                                                        @else
                                                            {{ Form::textarea('ptransaksi', null, ['class' => 'summernote-simple ptransaksi']) }}
                                                        @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <br>
                            <br>
                            <p><strong>4. Dokumentasi</strong></p>
                            <p>Kami telah mengarsipkan data-data yang didapatkan terkait prosedur PMPJ sederhana dalam cloud storage.</p>
                            <div class="row">
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-auto">
                                                <div class="form-group" style = "width: 800px">
                                                        @if(isset($value_pmpj->dokumentasi))
                                                            {{ Form::textarea('dokumentasi', $value_pmpj->dokumentasi, ['class' => 'summernote-simple dokumentasi']) }}
                                                        @else
                                                            {{ Form::textarea('dokumentasi', null, ['class' => 'summernote-simple dokumentasi']) }}
                                                        @endif
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <br>
                            <br>
                            <p><strong>5. Lapor PPTAK</strong></p>
                            <p>Apabila terdapat indikasi tindakan pencucian uang dan atau pendanaan terorisme dari hasil verifikasi dokumen dan pemantauan transaksi laporkan ke PPTK.</p>
                            <div class="row">
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="col-auto">
                                                <div class="form-group">
                                                    <td>
                                                        <a class="btn btn-simpan  btn-primary" href="https://goaml.ppatk.go.id/Home" target="_blank">Lapor PPATK</a>
                                                    </td>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="submit" value="{{__('Save')}}" class="btn  btn-primary">
    </div>
{{ Form::close() }}


@endsection
