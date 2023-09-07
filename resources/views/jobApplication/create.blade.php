{{Form::open(array('url'=>'job-application','method'=>'post', 'enctype' => "multipart/form-data"))}}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{Form::label('job',__('Job'),['class'=>'form-label'])}}
            {{Form::select('job',$jobs,null,array('class'=>'form-control select2','id'=>'jobs'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('name',__('Name'),['class'=>'form-label'])}}
            {{Form::text('name',null,array('class'=>'form-control name'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('email',__('Email'),['class'=>'form-label'])}}
            {{Form::text('email',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('phone',__('Phone'),['class'=>'form-label'])}}
            {{Form::text('phone',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-6 dob d-none">
            {!! Form::label('dob', __('Date of Birth'),['class'=>'form-label']) !!}
            {!! Form::date('dob', old('dob'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-md-6 gender d-none">
            {!! Form::label('gender', __('Gender'),['class'=>'form-label']) !!}
            <div class="d-flex radio-check">
                <div class="form-check form-check-inline form-group">
                    <input type="radio" id="g_male" value="Male" name="gender" class="form-check-input">
                    <label class="form-check-label" for="g_male">{{__('Male')}}</label>
                </div>
                <div class="form-check form-check-inline form-group">
                    <input type="radio" id="g_female" value="Female" name="gender" class="form-check-input">
                    <label class="form-check-label" for="g_female">{{__('Female')}}</label>
                </div>
            </div>
        </div>
        <div class="form-group col-md-6">
            {{Form::label('country',__('Country'),['class'=>'form-label'])}}
            <select id="country" class="form-control">
                <option value="">Select Country</option>
                @foreach($countries as $country)
                    <option value="{{ $country->code }}">{{ $country->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6 state">
            {{Form::label('state',__('State'),['class'=>'form-label'])}}
            <select id="state" class="form-control">
                <option value="">Select State</option>
            </select>
        </div>
        <div class="form-group col-md-6 city">
            {{Form::label('city',__('City'),['class'=>'form-label'])}}
            <select id="city" class="form-control">
                <option value="">Select City</option>
            </select>
        </div>

        <div class="form-group col-md-6 profile d-none">
            {{Form::label('profile',__('Profile'),['class'=>'form-label'])}}
            <div class="choose-file form-group">
                <label for="profile" class="form-label">
                    <div>{{__('Choose file here')}}</div>
                    <input type="file"  accept=".png, .jpg, .jpeg" class="form-control" name="profile" id="profile" data-filename="profile_create">
                </label>
                <p class="profile_create"></p>
            </div>
        </div>
        <div class="form-group col-md-6 resume d-none">
            {{Form::label('resume',__('CV / Resume'),['class'=>'form-label'])}}
            <div class="choose-file form-group">
                <label for="resume" class="form-label">
                    <div>{{__('Choose file here')}}</div>
                    <input type="file" accept=".png, .jpg, .jpeg, .pdf" class="form-control" name="resume" id="resume" data-filename="resume_create">
                </label>
                <p class="resume_create"></p>
            </div>
        </div>
        <div class="form-group col-md-6 kk d-none">
            {{Form::label('kk',__('KK'),['class'=>'form-label'])}}
            <div class="choose-file form-group">
                <label for="kk" class="form-label">
                    <div>{{__('Choose file here')}}</div>
                    <input type="file" accept=".png, .jpg, .jpeg, .pdf" class="form-control" name="kk" id="kk" data-filename="kk_create">
                </label>
                <p class="kk_create"></p>
            </div>
        </div>
        <div class="form-group col-md-6 ktp d-none">
            {{Form::label('ktp',__('KTP'),['class'=>'form-label'])}}
            <div class="choose-file form-group">
                <label for="ktp" class="form-label">
                    <div>{{__('Choose file here')}}</div>
                    <input type="file" accept=".png, .jpg, .jpeg, .pdf" class="form-control" name="ktp" id="ktp" data-filename="ktp_create">
                </label>
                <p class="ktp_create"></p>
            </div>
        </div>
        <div class="form-group col-md-6 transkrip_nilai d-none">
            {{Form::label('transkrip_nilai',__('Transkrip Nilai'),['class'=>'form-label'])}}
            <div class="choose-file form-group">
                <label for="transkrip_nilai" class="form-label">
                    <div>{{__('Choose file here')}}</div>
                    <input type="file" accept=".png, .jpg, .jpeg, .pdf" class="form-control" name="transkrip_nilai" id="transkrip_nilai" data-filename="transkrip_nilai_create">
                </label>
                <p class="transkrip_nilai_create"></p>
            </div>
        </div>
        <div class="form-group col-md-6 ijazah d-none">
            {{Form::label('ijazah',__('Ijazah'),['class'=>'form-label'])}}
            <div class="choose-file form-group">
                <label for="ijazah" class="form-label">
                    <div>{{__('Choose file here')}}</div>
                    <input type="file" accept=".png, .jpg, .jpeg, .pdf" class="form-control" name="ijazah" id="ijazah" data-filename="ijazah_create">
                </label>
                <p class="ijazah_create"></p>
            </div>
        </div>
        <div class="form-group col-md-6 certificate d-none">
            {{Form::label('certificate',__('Certificate'),['class'=>'form-label'])}}
            <div class="choose-file form-group">
                <label for="certificate" class="form-label">
                    <div>{{__('Choose file here')}}</div>
                    <input type="file" accept=".png, .jpg, .jpeg, .pdf" class="form-control" name="certificate" id="certificate" data-filename="certificate_create">
                </label>
                <p class="certificate_create"></p>
            </div>
        </div>
        <div class="form-group col-md-12 letter d-none">
            {{Form::label('cover_letter',__('Cover Letter'),['class'=>'form-label'])}}
            {{Form::textarea('cover_letter',null,array('class'=>'form-control'))}}
        </div>
        @foreach($questions as $question)
            <div class="form-group col-md-12  question question_{{$question->id}} d-none">
                {{Form::label($question->question,$question->question,['class'=>'form-label'])}}
                <input type="text" class="form-control" name="question[{{$question->question}}]" {{($question->is_required=='yes')?'required':''}}>
            </div>
        @endforeach
      
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
<script>
    $('#country').change(function() {
        var countryCode = $(this).val();
        $('#selected_country').val(countryCode);

        if (countryCode) {
            $.ajax({
                url: '{{ route("get.states.by.country") }}',
                type: 'GET',
                data: { country_code: countryCode },
                dataType: 'json',
                success: function(data) {
                    $('#state').empty().append('<option value="">Select State</option>');
                    $.each(data, function(index, state) {
                        $('#state').append('<option value="' + state.district + '">' + state.name + '</option>');
                    });
                }
            });
        } else {
            $('#state').empty().append('<option value="">Select State</option>');
            $('#city').empty().append('<option value="">Select City</option>');
        }
    });

    $('#state').change(function() {
        var stateDistrict = $(this).val();
        $('#selected_state').val(stateDistrict);

        if (stateDistrict) {
            $.ajax({
                url: '{{ route("get.cities.by.state") }}',
                type: 'GET',
                data: { state_district: stateDistrict },
                dataType: 'json',
                success: function(data) {
                    $('#city').empty().append('<option value="">Select City</option>');
                    $.each(data, function(index, city) {
                        $('#city').append('<option value="' + city.id + '">' + city.name + '</option>');
                    });
                }
            });
        } else {
            $('#city').empty().append('<option value="">Select City</option>');
        }
    });

    $('#city').change(function() {
        var cityId = $(this).val();
        $('#selected_city').val(cityId);
    });
</script>

{{Form::close()}}


