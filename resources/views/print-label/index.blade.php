@extends('layouts.admin')

@section('page-title')
    {{__('Print Label')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Print Label')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <form id="labelForm" method="POST" action="{{ route('print.labels') }}">
                        @csrf
                        <div id="labelInputContainer">
                            <div class="label-group">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="sender_receiver">{{__('Penerima/Pengirim')}}</label>
                                        <input type="text" name="sender_receiver[]" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="client_name">{{__('Nama Client')}}</label>
                                        <input type="text" name="client_name[]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="client_address">{{__('Alamat Client')}}</label>
                                        <input type="text" name="client_address[]" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="pic_name">{{__('Nama PIC')}}</label>
                                        <input type="text" name="pic_name[]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="pic_phone">{{__('PIC (NO HP)')}}</label>
                                        <input type="text" name="pic_phone[]" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="remarks">{{__('Keterangan')}}</label>
                                        <input type="text" name="remarks[]" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <button type="button" class="btn btn-primary" onclick="addLabel()">{{__('Tambah Label')}}</button>
                        <button type="submit" class="btn btn-success">{{__('Cetak Label')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    const MAX_LABELS = 8;

    function addLabel() {
        const container = document.getElementById('labelInputContainer');
        const labelCount = container.getElementsByClassName('label-group').length;

        if (labelCount >= MAX_LABELS) {
            alert(`Anda hanya dapat menambahkan hingga ${MAX_LABELS} label.`);
            return;
        }

        const labelTemplate = `
            <div class="label-group">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="sender_receiver">{{__('Penerima/Pengirim')}}</label>
                        <input type="text" name="sender_receiver[]" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="client_name">{{__('Nama Client')}}</label>
                        <input type="text" name="client_name[]" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="client_address">{{__('Alamat Client')}}</label>
                        <input type="text" name="client_address[]" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="pic_name">{{__('Nama PIC')}}</label>
                        <input type="text" name="pic_name[]" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="pic_phone">{{__('PIC (NO HP)')}}</label>
                        <input type="text" name="pic_phone[]" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="remarks">{{__('Keterangan')}}</label>
                        <input type="text" name="remarks[]" class="form-control">
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-label" onclick="removeLabel(this)">{{__('Hapus')}}</button>
            </div>`;

        container.insertAdjacentHTML('beforeend', labelTemplate);
    }

    function removeLabel(button) {
        const labelGroup = button.closest('.label-group');
        labelGroup.remove();
    }
</script>
@endpush
