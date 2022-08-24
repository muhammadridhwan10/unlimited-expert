
{{Form::open(array('url'=>'event','method'=>'post'))}}
<div class="modal-body">

    <div class="row">
        <div class="col-md-4">

            {{Form::label('branch_id',__('Branch'),['class'=>'form-label'])}}
            <select class="form-control select" name="branch_id" id="branch_id" placeholder="{{__('Select Branch')}}">
                <option value="">{{__('Select Branch')}}</option>

                @foreach($branch as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>

        </div>
        <div class="col-md-4">

            {{Form::label('department_id',__('Department'),['class'=>'form-label'])}}
            <select class="form-control " name="department_id" id="department_id"  placeholder="{{__('Select Department')}}">
                <option value="">{{__('Select Department')}}</option>
            </select>
        </div>
        <div class="col-md-4">

            {{Form::label('employee_id',__('Employee'),['class'=>'form-label'])}}
            <select class="form-control " name="employee_id" id="employee_id" placeholder="{{__('Select Employee')}}" >
                <option value="">{{__('Select Employee')}}</option>

            </select>

        </div>
    </div>


    <div class="row mt-2">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('title',__('Event Title'),['class'=>'form-label'])}}
                {{Form::text('title',null,array('class'=>'form-control','placeholder'=>__('Enter Event Title')))}}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('start_date',__('Event start Date'),['class'=>'form-label'])}}
                {{Form::date('start_date',null,array('class'=>'form-control '))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('end_date',__('Event End Date'),['class'=>'form-label'])}}
                {{Form::date('end_date',null,array('class'=>'form-control '))}}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{Form::label('color',__('Event Select Color'),['class'=>'form-label d-block mb-3'])}}
                <div class="btn-group btn-group-toggle btn-group-colors event-tag" data-toggle="buttons">
                    <label class="btn bg-info active mr-2">
                        <input type="radio" name="color" value="bg-info" autocomplete="off" checked style="display: none; ">
                    </label>
                    <label class="btn bg-warning mr-2">
                        <input type="radio" name="color" value="bg-warning" autocomplete="off" style="display: none">
                    </label>
                    <label class="btn bg-danger mr-2">
                        <input type="radio" name="color" value="bg-danger" autocomplete="off" style="display: none">
                    </label>
                    <label class="btn bg-success mr-2">
                        <input type="radio" name="color" value="bg-success" autocomplete="off" style="display: none">
                    </label>
                    <label class="btn bg-secondary mr-2">
                        <input type="radio" name="color" value="bg-secondary" autocomplete="off" style="display: none">
                    </label>
                    <label class="btn bg-primary mr-2">
                        <input type="radio" name="color" value="bg-primary" autocomplete="off" style="display: none">
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('description',__('Event Description'),['class'=>'form-label'])}}
                {{Form::textarea('description',null,array('class'=>'form-control','placeholder'=>__('Enter Event Description')))}}
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}

@push('script-page')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>


    <script>
        $(document).ready(function () {
            var b_id = $('#branch_id').val();
            getDepartment(b_id);
        });
        $(document).on('change', 'select[name=branch_id]', function () {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });

        function getDepartment(bid) {

            $.ajax({
                url: '{{route('event.getdepartment')}}',
                type: 'POST',
                data: {
                    "branch_id": bid, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $("#department_id").html('');
                    $('#department_id').append('<select class="form-control" id="department_id" name="department_id[]" ></select>');

                    // $('#department_id').empty();
                    $('#department_id').append('<option value="">{{__('Select Department')}}</option>');

                    $('#department_id').append('<option value="0"> {{__('All Department')}} </option>');
                    $.each(data, function (key, value) {
                        $('#department_id').append('<option value="' + key + '">' + value + '</option>');
                    });

                    // var multipleCancelButton = new Choices('#department_id', {
                    //     removeItemButton: true,
                    // });
                }
            });
        }

        $(document).on('change', '#department_id', function () {
            var department_id = $(this).val();
            getEmployee(department_id);
        });

        function getEmployee(did) {
            $.ajax({
                url: '{{route('event.getemployee')}}',
                type: 'POST',
                data: {
                    "department_id": did, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {

                    $("#employee_id").html('');
                    $('#employee_id').append('<select class="form-control" id="employee_id" name="employee_id[]"  multiple></select>');

                    $('#employee_id').empty();
                    $('#employee_id').append('<option value="">{{__('Select Employee')}}</option>');
                    $('#employee_id').append('<option value="0"> {{__('All Employee')}} </option>');

                    $.each(data, function (key, value) {
                        $('#employee_id').append('<option value="' + key + '">' + value + '</option>');
                    });
                    var multipleCancelButton = new Choices('#employee_id', {
                        removeItemButton: true,
                    });
                }
            });
        }
    </script>
@endpush



