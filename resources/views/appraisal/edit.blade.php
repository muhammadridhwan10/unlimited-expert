{{Form::model($appraisal,array('route' => array('appraisal.update', $appraisal->id), 'method' => 'PUT')) }}
<div class="modal-body">

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('branch',__('Branch'),['class'=>'form-label'])}}
                {{Form::select('branch',$brances,null,array('class'=>'form-control select','required'=>'required','id'=>'branch'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('employee',__('Employee'),['class'=>'form-label'])}}
                <select class="select form-control select2-multiple" id="employee" name="employee" data-toggle="select2" data-placeholder="{{ __('Select Employee') }}" required>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('appraisal_date',__('Select Month'),['class'=>'form-label'])}}
                {{ Form::date('appraisal_date',null, array('class' => 'form-control')) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('remark',__('Remarks'),['class'=>'form-label'])}}
                {{Form::textarea('remark',null,array('class'=>'form-control'))}}
            </div>
        </div>
    </div>
    @foreach($performance as $performances)

    <div class="row">
        <div class="col-md-12 mt-3">
            <h6>{{$performances->name}}</h6>
            <hr class="mt-0">
        </div>
        @foreach($performances->types as $types )

            <div class="col-6">
                {{$types->name}}
            </div>
            <div class="col-6">
                <fieldset id='demo1' class="rating">
                    <input class="stars" type="radio" id="technical-5-{{$types->id}}" name="rating[{{$types->id}}]" value="5" {{ (isset($ratings[$types->id]) && $ratings[$types->id] == 5)? 'checked':''}}>
                    <label class="full" for="technical-5-{{$types->id}}" title="Awesome - 5 stars"></label>
                    <input class="stars" type="radio" id="technical-4-{{$types->id}}" name="rating[{{$types->id}}]" value="4" {{ (isset($ratings[$types->id]) && $ratings[$types->id] == 4)? 'checked':''}}>
                    <label class="full" for="technical-4-{{$types->id}}" title="Pretty good - 4 stars"></label>
                    <input class="stars" type="radio" id="technical-3-{{$types->id}}" name="rating[{{$types->id}}]" value="3" {{ (isset($ratings[$types->id]) && $ratings[$types->id] == 3)? 'checked':''}}>
                    <label class="full" for="technical-3-{{$types->id}}" title="Meh - 3 stars"></label>
                    <input class="stars" type="radio" id="technical-2-{{$types->id}}" name="rating[{{$types->id}}]" value="2" {{ (isset($ratings[$types->id]) && $ratings[$types->id] == 2)? 'checked':''}}>
                    <label class="full" for="technical-2-{{$types->id}}" title="Kinda bad - 2 stars"></label>
                    <input class="stars" type="radio" id="technical-1-{{$types->id}}" name="rating[{{$types->id}}]" value="1" {{ (isset($ratings[$types->id]) && $ratings[$types->id] == 1)? 'checked':''}}>
                    <label class="full" for="technical-1-{{$types->id}}" title="Sucks big time - 1 star"></label>
                </fieldset>
            </div>
        @endforeach
    </div>
    @endforeach
    </div>
    <div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{Form::close()}}


<script type="text/javascript">
    function getEmployee(did) {
        $.ajax({
            url: '{{route('branch.employee.json')}}',
            type: 'POST',
            data: {
                "branch": did, "_token": "{{ csrf_token() }}",
            },
            success: function (data) {
                $('#employee').empty();
                $('#employee').append('<option value="">{{__('Select Branch')}}</option>');
                $.each(data, function (key, value) {
                    var select = '';
                    if (key == '{{ $appraisal->employee }}') {
                        select = 'selected';
                    }

                    $('#employee').append('<option value="' + key + '"  ' + select + '>' + value + '</option>');
                });
            }
        });
    }

    $(document).ready(function () {
        var d_id = $('#branch').val();
        getEmployee(d_id);
    });

</script>



