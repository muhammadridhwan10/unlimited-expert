@extends('layouts.admin')

@section('page-title')
    {{__('Employee')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('employee.index')}}">{{__('Employee')}}</a></li>
    <li class="breadcrumb-item">{{$employeesId}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('edit employee')
            <a href="{{route('employee.edit',\Illuminate\Support\Facades\Crypt::encrypt($employee->id))}}" data-bs-toggle="tooltip" title="{{__('Edit')}}"class="btn btn-sm btn-primary">
                <i class="ti ti-pencil"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- Card Utama -->
        <div class="card shadow-sm">
            <div class="card-header text-white">
                <h5 class="mb-0">Detail Karyawan</h5>
            </div>
            <div class="card-body">

                <!-- Informasi Perusahaan -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3"><i class="ti ti-building me-2"></i>Data Perusahaan</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th>ID Karyawan</th>
                                <td style="word-wrap: break-word; white-space: normal;">: {{ $employeesId }}</td>
                            </tr>
                            <tr>
                                <th>Status Karyawan</th>
                                <td>: {{ $employee->detail->employee_status ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Bergabung</th>
                                <td>: {{ \Carbon\Carbon::parse($employee->company_doj)->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th>Cabang</th>
                                <td style="word-wrap: break-word; white-space: normal;">: {{ $branches[$employee->branch_id] ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3"><i class="ti ti-users me-2"></i>Posisi & Departemen</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th>Departemen</th>
                                <td style="word-wrap: break-word; white-space: normal;">: {{ $departments[$employee->department_id] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jabatan</th>
                                <td style="word-wrap: break-word; white-space: normal;">: {{ $designations[$employee->designation_id] ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Informasi Umum -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3"><i class="ti ti-user me-2"></i>Informasi Pribadi</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th>Nama</th>
                                <td style="word-wrap: break-word; white-space: normal;">: {{ $employee->name }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Lahir</th>
                                <td>: {{ \Carbon\Carbon::parse($employee->dob)->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th>Jenis Kelamin</th>
                                <td>: {{ $employee->gender }}</td>
                            </tr>
                            <tr>
                                <th>Agama</th>
                                <td>: {{ $employee->detail->religion ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Golongan Darah</th>
                                <td>: {{ $employee->detail->blood_type ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3"><i class="ti ti-phone me-2"></i>Kontak & Alamat</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th>No. Telepon</th>
                                <td>: {{ $employee->phone }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>: {{ $employee->email }}</td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td style="word-wrap: break-word; white-space: normal;">: {{ $employee->address }}</td>
                            </tr>
                            <tr>
                                <th>Kontak Darurat</th>
                                <td style="word-wrap: break-word; white-space: normal;">: {{ $employee->detail->emergency_contact ?? '-' }} ({{ $employee->detail->emergency_phone ?? '-' }})</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Informasi Pendidikan -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6 class="text-muted mb-3"><i class="ti ti-school me-2"></i>Pendidikan Terakhir</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th>Pendidikan Terakhir</th>
                                <td style="word-wrap: break-word; white-space: normal;">: {{ $employee->detail->last_education ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Nama Institusi</th>
                                <td style="word-wrap: break-word; white-space: normal;">: {{ $employee->detail->name_of_educational_institution ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jurusan</th>
                                <td style="word-wrap: break-word; white-space: normal;">: {{ $employee->detail->major ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Dokumen -->
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="text-muted mb-3"><i class="ti ti-file me-2"></i>Dokumen</h6>
                        <div class="table-responsive mt-2">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Dokumen</th>
                                        <th>File</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($documents as $key => $document)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td style="word-wrap: break-word; white-space: normal;">{{ $document->name }}</td>
                                        <td>
                                            @php
                                                $employeeDocument = $employee->documents()->where('document_id', $document->id)->first();
                                            @endphp
                                            @if ($employeeDocument)
                                                <a href="{{ asset('storage/uploads/document/' . $employeeDocument->document_value) }}" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="ti ti-download"></i> Unduh
                                                </a>
                                            @else
                                                <span class="badge bg-danger">Tidak Ada</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada dokumen tersedia.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
