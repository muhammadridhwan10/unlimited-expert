{{ Form::open(['route' => ['projects.tasks.store.financial.statement',[$project_id, $task_id]],'id' => 'create_financial_statement']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('m', __('M'),['class'=>'form-label']) }}
                {{ Form::select('m',$materialitas,null, array('class' => 'form-control select')) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('lk', __('LK'),['class' => 'form-label']) }}
                <select name="lk" id="lk" class="form-control main-element">
                    @foreach(\App\Models\ProjectTask::$financial_statement as $k => $v)
                        <option value="{{$k}}">{{__($k)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('cn', __('C/N'),['class' => 'form-label']) }}
                <select name="cn" id="cn" class="form-control main-element">
                    @foreach(\App\Models\ProjectTask::$lancar as $k => $v)
                        <option value="{{$k}}">{{__($k)}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('rp', __('RP'),['class' => 'form-label']) }}
                {{ Form::text('rp', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('add1', __('Add 1'),['class' => 'form-label']) }}
                {{ Form::text('add1', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('add2', __('Add 2'),['class' => 'form-label']) }}
                {{ Form::text('add2', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('add3', __('Add 3'),['class' => 'form-label']) }}
                {{ Form::text('add3', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('coa', __('CoA'),['class' => 'form-label']) }}
                {{ Form::text('coa', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="form-group">
                {{ Form::label('account', __('Account'),['class' => 'form-label']) }}
                {{ Form::text('account', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('prior_period2', __(date(' Y', strtotime('-3 year', strtotime($project->book_year)))),['class' => 'form-label']) }}
                {{ Form::text('prior_period2', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('prior_period', __(date(' Y', strtotime('-2 year', strtotime($project->book_year)))),['class' => 'form-label']) }}
                {{ Form::text('prior_period', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-4 col-md-4">
            <div class="form-group">
                {{ Form::label('inhouse', __('Inhouse ' . $project->book_year),['class' => 'form-label']) }}
                {{ Form::text('inhouse', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('jan', __('January'),['class' => 'form-label']) }}
                {{ Form::text('jan', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('feb', __('February'),['class' => 'form-label']) }}
                {{ Form::text('feb', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('mar', __('March'),['class' => 'form-label']) }}
                {{ Form::text('mar', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('apr', __('April'),['class' => 'form-label']) }}
                {{ Form::text('apr', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('may', __('May'),['class' => 'form-label']) }}
                {{ Form::text('may', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('jun', __('June'),['class' => 'form-label']) }}
                {{ Form::text('jun', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('jul', __('July'),['class' => 'form-label']) }}
                {{ Form::text('jul', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('aug', __('August'),['class' => 'form-label']) }}
                {{ Form::text('aug', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('sep', __('September'),['class' => 'form-label']) }}
                {{ Form::text('sep', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('oct', __('October'),['class' => 'form-label']) }}
                {{ Form::text('oct', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('nov', __('November'),['class' => 'form-label']) }}
                {{ Form::text('nov', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('dec', __('December'),['class' => 'form-label']) }}
                {{ Form::text('dec', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('triwulan1', __('Triwulan 1'),['class' => 'form-label']) }}
                {{ Form::text('triwulan1', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('triwulan2', __('Triwulan 2'),['class' => 'form-label']) }}
                {{ Form::text('triwulan2', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('triwulan3', __('Triwulan 3'),['class' => 'form-label']) }}
                {{ Form::text('triwulan3', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
        <div class="col-sm-3 col-md-3">
            <div class="form-group">
                {{ Form::label('triwulan4', __('Triwulan 4'),['class' => 'form-label']) }}
                {{ Form::text('triwulan4', null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}

