{{Form::model($overtime,array('route' => array('overtime.update', $overtime->id), 'method' => 'PUT')) }}
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
                {{Form::select('approval',$approval,null,array('class'=>'form-control select2','id'=>'approval','placeholder'=>__('Select Approval')))}}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('project_id',__('Project') ,['class'=>'form-label'])}}
                {{Form::select('project_id',$project,null,array('class'=>'form-control select2','id'=>'project_id','placeholder'=>__('Select Project')))}}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'),['class'=>'form-label']) }}
                {{ Form::text('start_date',null,array('class'=>'form-control', 'id'=>'datepicker')) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_time', __('Start Time'),['class'=>'form-label']) }}
                {{Form::time('start_time',null,array('class'=>'form-control timepicker'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_time', __('End Time'),['class'=>'form-label']) }}
                {{Form::time('end_time',null,array('class'=>'form-control timepicker'))}}
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
    $( function() {
        var currentDate = new Date();
			var currentMonth = currentDate.getMonth();
			var currentYear = currentDate.getFullYear();
			var maxDate = new Date(currentYear, currentMonth, 27);
			if (currentDate.getDate() > 27) {
				if (currentMonth == 11) {
					maxDate.setFullYear(currentYear + 1);
					maxDate.setMonth(0);
				} else {
					maxDate.setMonth(currentMonth + 1);
				}
			}
			
			// initialize datepicker with maximum date
			$( "#datepicker" ).datepicker({
				maxDate: maxDate,
				dateFormat: 'yy-mm-dd'
			});
    });
</script>
