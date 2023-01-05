<?php
    $note  = "Perikatan Tahun Pertama";
    $notes = "Perikatan Berulang";
    $engagement_types_text = "Audit Atas Laporan Keuangan";
    $auditing_standard_text = "Standard Profesional Akuntan Publik";
?>
<div class="modal-body">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <strong class='form-label'>{{ 'Auditor Identity' }}</strong>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('client_identity', __('Client Identity'), ['class' => 'form-label']) }}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('auditor_engagement', __('Auditor Engagement'), ['class' => 'form-label']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('auditor_identity', __('Public Accountant Firm Name (KAP)'), ['class' => 'form-label']) }}
                {{ Form::text('client', !empty($project->accountant->office->name) ? $project->accountant->office->name:'', array('class' => 'form-control', 'readonly' => 'true')) }}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('client_identity', __('Client Name'), ['class' => 'form-label']) }}
                {{ Form::text('client_name', $project->user->name, array('class' => 'form-control', 'readonly' => 'true')) }}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('book_year', __('Book Year'), ['class' => 'form-label']) }}
                {{ Form::text('book_year', $project->user->book_year, array('class' => 'form-control', 'readonly' => 'true')) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('auditor_identity', __('Public Accountant Name (AP)'), ['class' => 'form-label']) }}
                {{ Form::text('client', !empty($project->accountant->name) ? $project->accountant->name:'', array('class' => 'form-control', 'readonly' => 'true')) }}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('address', __('Client Address'), ['class' => 'form-label']) }}
                {{ Form::text('address', $project->user->alamat, array('class' => 'form-control', 'readonly' => 'true')) }}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                @if ($project->user->engagement_type = "perikatan_tahun_pertama")
                    {{ Form::label('engagement_type', __('Engagement Type'), ['class' => 'form-label']) }}
                    {{ Form::text('engagement_type', $note, array('class' => 'form-control', 'readonly' => 'true')) }}
                @else if ($project->user->engagement_type = "perikatan_berulang")
                    {{ Form::label('engagement_type', __('Engagement Type'), ['class' => 'form-label']) }}
                    {{ Form::text('engagement_type', $notes, array('class' => 'form-control', 'readonly' => 'true')) }}
                @endif
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('telephone', __('Telephone'), ['class' => 'form-label']) }}
                {{ Form::text('telephone', $project->user->telp, array('class' => 'form-control', 'readonly' => 'true')) }}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
            @if ($project->user->engagement_types = "audit_atas_laporan_keuangan")
                {{ Form::label('engagement_types', __('Engagement Types'), ['class' => 'form-label']) }}
                {{ Form::text('engagement_types', $engagement_types_text, array('class' => 'form-control', 'readonly' => 'true')) }}
            @endif
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">

            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('npwp', __('NPWP'), ['class' => 'form-label']) }}
                {{ Form::text('npwp', $project->user->npwp, array('class' => 'form-control', 'readonly' => 'true')) }}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
            @if ($project->user->auditing_standard = "audit_atas_laporan_keuangan")
                {{ Form::label('auditing_standard', __('Auditing Standard'), ['class' => 'form-label']) }}
                {{ Form::text('auditing_standard', $auditing_standard_text, array('class' => 'form-control', 'readonly' => 'true')) }}
            @endif
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('client_business_sector_id', __('Client Business Sector'), ['class' => 'form-label']) }}
                {{ Form::text('client_business_sector_id', !empty($project->user->business_sector->name)? $project->user->business_sector->name:'' , array('class' => 'form-control', 'readonly' => 'true')) }}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('client_accounting_standard_id', __('Client Accounting Standard'), ['class' => 'form-label']) }}
                {{ Form::text('client_accounting_standard_id', !empty($project->user->accounting_standard->name)? $project->user->accounting_standard->name:'', array('class' => 'form-control', 'readonly' => 'true')) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
        </div>
        <div class="col-sm-4">
        <div class="form-group">
            {{ Form::label('client_ownership_status_id', __('Client Ownership Status'), ['class' => 'form-label']) }}
            {{ Form::text('client_ownership_status_id', !empty($project->user->ownership_status->name)? $project->user->ownership_status->name:'', array('class' => 'form-control', 'readonly' => 'true')) }}
        </div>
        </div>
        <div class="col-sm-4">
        </div>
    </div>
    <div class="row">
    <div class="col-sm-12">
            <div class="form-group">
                <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5>{{__('Engagement Team Structure')}}</h5>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush list" id="project_userss">
                    </ul>
                </div>
            </div>
            </div>
        </div>
    </div>    
    {{ Form::hidden('project_id', $project_id,['id'=>'project_id']) }}
</div>
<script>

    $(document).ready(function () {
        loadProjectUser();
        $(document).on('click', '.invite_usr', function () {
            var project_id = $('#project_id').val();
            var user_id = $(this).attr('data-id');

            $.ajax({
                url: '{{ route('invite.project.user.member') }}',
                method: 'POST',
                dataType: 'json',
                data: {
                    'project_id': project_id,
                    'user_id': user_id,
                    "_token": "{{ csrf_token() }}"
                },
                success: function (data) {
                    if (data.code == '200') {
                        show_toastr(data.status, data.success, 'success')
                        setInterval('location.reload()', 5000);
                        loadProjectUser();
                    } else if (data.code == '404') {
                        show_toastr(data.status, data.errors, 'error')
                    }
                }
            });
        });
    });

    function loadProjectUser() {
        var mainEle = $('#project_userss');
        var project_id = '{{$project->id}}';

        $.ajax({
            url: '{{ route('project.user') }}',
            data: {project_id: project_id},
            beforeSend: function () {
                $('#project_userss').html('<tr><th colspan="2" class="h6 text-center pt-5">{{__('Loading...')}}</th></tr>');
            },
            success: function (data) {
                mainEle.html(data.html);
                $('[id^=fire-modal]').remove();
                loadConfirm();
            }
        });
    }

    function loadProjectClient() {
        var mainEle = $('#project_client');
        var project_id = '{{$project->id}}';

        $.ajax({
            url: '{{ route('project.client') }}',
            data: {project_id: project_id},
            beforeSend: function () {
                $('#project_client').html('<tr><th colspan="2" class="h6 text-center pt-5">{{__('Loading...')}}</th></tr>');
            },
            success: function (data) {
                mainEle.html(data.html);
                $('[id^=fire-modal]').remove();
                loadConfirm();
            }
        });
    }

</script>
