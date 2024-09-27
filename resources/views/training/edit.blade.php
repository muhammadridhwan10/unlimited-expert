{{Form::model($training,array('route' => array('training.update', $training->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('training_title',__('Training Title'),['class'=>'form-label'])}}
                {{Form::text('training_title',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('branch',__('Branch'),['class'=>'form-label'])}}
                {{Form::select('branch',$branches,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('training_type',__('Training Type'),['class'=>'form-label'])}}
                {{Form::select('training_type',$trainingTypes,null,array('class'=>'form-control select','required'=>'required','id'=>'training_type'))}}
            </div>
        </div>
        <div class="col-md-6" id="trainer_option" style="display: none;">
            <div class="form-group">
                {{Form::label('trainer_option',__('Training Option'),['class'=>'form-label'])}}
                {{Form::select('trainer_option',$options,null,array('class'=>'form-control select'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('year',__('Year'),['class'=>'form-label'])}}
                {{Form::number('year',null,array('class'=>'form-control','required'=>'required','min'=>'1900','max'=>date('Y')))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('location',__('Location'),['class'=>'form-label'])}}
                {{Form::text('location',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
        </div>
        <div class="form-group col-lg-12">
            {{Form::label('description',__('Description'),['class'=>'form-label'])}}
            {{Form::textarea('description',null,array('class'=>'form-control','placeholder'=>__('Description')))}}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{Form::close()}}

<script>
    $(document).ready(function() {

        var initialTrainingType = $('#training_type').val();
        toggleTrainerOption(initialTrainingType);

        $('#training_type').change(function() {
            var selectedCategory = $(this).val();
            toggleTrainerOption(selectedCategory);
        });

        function toggleTrainerOption(trainingType) {
            if(trainingType === '2') {
                $('#trainer_option').show();
            } else {
                $('#trainer_option').hide();
            }
        }
    });
</script>
