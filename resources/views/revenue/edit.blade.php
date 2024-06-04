{{ Form::model($revenue, array('route' => array('revenue.update', $revenue->id), 'method' => 'post','enctype' => 'multipart/form-data')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}
            {{Form::date('date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('invoice_id', __('Invoice'), ['class'=>'form-label']) }}
            <select class="form-control select2" required="required" id="invoice_id" name="invoice_id">
                <option value="">{{ __('Select Invoice') }}</option>
                @foreach($invoices as $key => $invoice)
                    <option value="{{ $key }}" {{ old('invoice_id', $revenue->invoice_id) == $key ? 'selected' : '' }}>
                        {{ $invoice }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}
            {{ Form::number('amount', null, array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('user_id', __('Partner'),['class'=>'form-label']) }}
            {{ Form::select('user_id', $user,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
    </div>
    <div class="form-group  col-md-12">
        {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
        {{ Form::textarea('description', '', array('class' => 'form-control','rows'=>3)) }}
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}



<script>
    document.getElementById('files').onchange = function () {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('image').src = src
    }
</script>
