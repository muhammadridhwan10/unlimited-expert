{{Form::open(array('url'=>'overtime','method'=>'post'))}}
    <div class="modal-body">

    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'client' || \Auth::user()->type == 'staff_client')
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('user_id',__('Employee') ,['class'=>'form-label'])}}
                    {{Form::select('user_id',$employees,null,array('class'=>'form-control select2','id'=>'user_id','placeholder'=>__('Select Employee')))}}
                </div>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('approval',__('Approval By') ,['class'=>'form-label'])}}
                {{Form::select('approval',$approval,null,array('class'=>'form-control select2','required'=>'required','id'=>'approval','placeholder'=>__('Select Approval')))}}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('project_id',__('Project') ,['class'=>'form-label'])}}
                {{Form::select('project_id',$project,null,array('class'=>'form-control select2','required'=>'required','id'=>'project_id','placeholder'=>__('Select Project')))}}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'),['class'=>'form-label']) }}
                {{ Form::text('start_date',null,array('class'=>'form-control', 'id'=>'datepicker', 'required'=>'required')) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_time', __('Start Time'),['class'=>'form-label']) }}
                {{Form::time('start_time',null,array('class'=>'form-control timepicker', 'required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_time', __('End Time'),['class'=>'form-label']) }}
                {{Form::time('end_time',null,array('class'=>'form-control timepicker', 'required'=>'required'))}}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('note',__('Note') ,['class'=>'form-label'])}}
                {{Form::textarea('note',null,array('class'=>'form-control','placeholder'=>__('Note')))}}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}
<script>
  $(function() {
    var currentDate = new Date();
    var currentMonth = currentDate.getMonth() + 1; // bulan dimulai dari 0, jadi ditambah 1
    var currentYear = currentDate.getFullYear();
    var maxDate;

    if (currentMonth === 9) {
      // Bulan ini belum melewati tanggal 28 atau bulan ini adalah May
        maxDate = 25;
    }
    else if (currentMonth === 10)
    {
        maxDate = 26;
    }
    else if (currentMonth === 11)
    {
        maxDate = 27;
    }
    else if (currentMonth === 12)
    {
        maxDate = 26;
    } 
    else {
      maxDate = currentDate.getDate();
    }

    var startDate = new Date(currentYear, currentMonth - 2, 1); // 1 bulan sebelumnya
    var endDate = new Date(currentYear, currentMonth - 1, 0); // akhir bulan ini

    $("#datepicker").datepicker({
      dateFormat: 'yy-mm-dd',
      minDate: startDate,
      maxDate: new Date(currentYear, currentMonth - 1, maxDate),
      beforeShowDay: function(date) {
        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();

        if ((year === currentYear && month === currentMonth && day > maxDate) || 
            (year === currentYear && month === currentMonth - 1 && day < 25)) {
          return [false];
        }
        return [true];
      }
    });

    if (currentDate.getDate() > 28) 
    {
        $("#datepicker").datepicker("option", "disabled", true);
        $("#datepicker").attr("readonly", "readonly");
    }
    
  });
</script>


