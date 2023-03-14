
{{Form::model($user,array('route' => array('list.users.update', $user->id), 'method' => 'POST')) }}
<div class="modal-body">

    <div class="row">
        <div class="form-group col-md-12">
        {{ Form::label('projects', __('Project'),['class'=>'form-label'])}}
        {{ Form::select('project_id', $project, null, ['class' => 'form-control select project_select', 'placeholder' => __('Select Project'), 'id' => 'project_select', 'data-toggle' => 'select']) }}
        </div>
        <div class="form-group col-md-12" id="task_div">
        {{ Form::label('task', __('Task'),['class'=>'form-label'])}}
            <select class="form-control select" id="task_id" name="task_id[]" placeholder="Select Task" >
                <option value="">{{__('Select Task')}}</option>
            </select>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}


<script type="text/javascript">
        $(document).on('change', '.project_select', function () {

            var project_id = $(this).val();

            getparent(project_id);
        });
        function getparent(bid) {

            $.ajax({
                url: `{{ url('assignuser/projects/select')}}/${bid}`,
                type: 'GET',
                success: function (data) {
                    $("#task_div").html('');
                    $('#task_div').append('<select class="form-control" id="task_id" name="task_id[]"  multiple></select>');

                    $('#task_id').append('<option value="">{{__('Select Task')}}</option>');

                    $.each(data, function (i, item) {

                        $('#task_id').append('<option value="' + item.id + '">' + item.name + '</option>');
                    });

                    var multipleCancelButton = new Choices('#task_id', {
                        removeItemButton: true,
                    });

                    if (data == '') {
                        $('#task_id').empty();
                    }
                }
            });
        }

        $(document).ready(function () {

            $('.date').daterangepicker({
                "singleDatePicker": true,
                "timePicker": true,
                "locale": {
                    "format": 'MM/DD/YYYY H:mm'
                },
                "timePicker24Hour": true,
            }, function(start, end, label) {
                console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
            });
            getProjects($('#client_id').val());
        });

</script>


