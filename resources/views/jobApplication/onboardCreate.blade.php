{{Form::open(array('route'=>array('job.on.board.store',$id),'method'=>'post'))}}
<div class="modal-body">

    <div class="row">
        @if($id==0)
            <div class="form-group col-md-12">
                {{Form::label('application',__('Interviewer'),['class'=>'form-label'])}}
                {{Form::select('application',$applications,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        @endif
        <div class="form-group col-md-12">
            {!! Form::label('joining_date', __('Joining Date'),['class'=>'form-label']) !!}
            {!! Form::date('joining_date', null, ['class' => 'form-control ']) !!}
        </div>
        <div class="form-group col-md-12">
            {{Form::label('status',__('Status'),['class'=>'form-label'])}}
            {{Form::select('status',$status,null,array('class'=>'form-control select'))}}
        </div>
       
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>

{{Form::close()}}


