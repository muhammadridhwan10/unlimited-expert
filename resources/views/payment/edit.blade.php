{{ Form::model($payment, array('route' => array('payment.update', $payment->id), 'method' => 'PUT','enctype' => 'multipart/form-data')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Issue Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{Form::date('date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('currency', __('Currency'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            <select name="currency" id="currency" class="form-control main-element">
                <option value="0">{{__('Select Currency')}}</option>
                @foreach(\App\Models\Payment::$currency as $k => $v)
                    <option value="{{$k}}">{{__($v)}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('operator', __('Operator'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            <select name="operator" id="operator" class="form-control main-element">
                <option value="0">{{__('Select Operator')}}</option>
                @foreach(\App\Models\Payment::$operator as $k => $v)
                    <option value="{{$k}}">{{__($v)}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6" id="kurs-container" style="display: none;">
            {{ Form::label('kurs', __('Kurs Rupiah'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Form::number('kurs', '', array('class' => 'form-control','step'=>'0.01', 'id' => 'kurs')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount_before_tax', __('Amount Before Tax'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Form::number('amount_before_tax', '', array('class' => 'form-control','required'=>'required','step'=>'0.01', 'id' => 'amount_before_tax')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('tax', __('Tax (%)'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Form::number('tax', '', array('class' => 'form-control','required'=>'required','step'=>'0.01', 'id' => 'tax')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount After Tax'),['class'=>'form-label']) }}
            {{ Form::number('amount', '', array('class' => 'form-control','required'=>'required','step'=>'0.01', 'id' => 'amount', 'readonly' => 'readonly')) }}
        </div>
        @if(\Auth::user()->type != 'partners')
            <div class="form-group col-md-6">
                {{ Form::label('user_id', __('Partner'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                {{ Form::select('user_id',$partners,null, array('class' => 'form-control select','required'=>'required')) }}
            </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('vender_id', __('Vendor'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Form::select('vender_id', $venders,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        {{-- <div class="form-group col-md-6" id="approval-container">
            <div class="form-group">
                {{Form::label('approval',__('Approval By') ,['class'=>'form-label'])}}<span class="text-danger">*</span>
                {{Form::select('approval',$approval,null,array('class'=>'form-control select2','id'=>'approval','placeholder'=>__('Select Approval')))}}
            </div>
        </div> --}}
        <div class="form-group  col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {{ Form::textarea('description', '', array('class' => 'form-control','rows'=>3)) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('category_id', __('Category'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Form::select('category_id', $categories,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        {{-- <div class="form-group col-md-6">
            {{ Form::label('reference', __('Reference'),['class'=>'form-label']) }}
            {{ Form::text('reference', '', array('class' => 'form-control')) }}
        </div> --}}

        <div class="form-group col-md-6">
            {{Form::label('add_receipt',__('Payment Receipt'),['class' => 'form-label'])}}
            {{Form::file('add_receipt',array('class'=>'form-control', 'id'=>'files'))}}
            <img id="image" class="mt-2" style="width:25%;"/>
        </div>

         <div class="form-group col-md-6">
            {{Form::label('add_bill',__('Bill to Pay'),['class' => 'form-label'])}}
            {{Form::file('add_bill',array('class'=>'form-control','id'=>'filess', 'accept' => '.pdf'))}}
            <img id="image" class="mt-2" style="width:25%;"/>
        </div>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{ Form::close() }}


<script>
    var userType = "{{ \Auth::user()->type }}";
    document.getElementById('files').onchange = function () {
        var src = URL.createObjectURL(this.files[0]);
        document.getElementById('image').src = src;
    }

    document.getElementById('filess').onchange = function () {
        var src = URL.createObjectURL(this.files[0]);
        document.getElementById('image').src = src;
    }

    function calculateAmountAfterTax() {
        var amountBeforeTax = parseFloat(document.getElementById('amount_before_tax').value) || 0;
        var taxPercentage = parseFloat(document.getElementById('tax').value) || 0;
        var operator = document.getElementById('operator').value;
        var currency = document.getElementById('currency').value;
        var kurs = parseFloat(document.getElementById('kurs').value) || 0;
        var amountAfterTax;

        var taxAmount;
        if (currency === '€') {
            var amountInRupiah = amountBeforeTax * kurs;
            taxAmount = amountInRupiah * (taxPercentage / 100);
            amountAfterTax = operator === '-' ? amountInRupiah - taxAmount : amountInRupiah + taxAmount;
        } else {
            taxAmount = amountBeforeTax * (taxPercentage / 100);
            amountAfterTax = operator === '-' ? amountBeforeTax - taxAmount : amountBeforeTax + taxAmount;
        }

        document.getElementById('amount').value = amountAfterTax.toFixed(0);
    }

    function toggleKursField() {
        var currency = document.getElementById('currency').value;
        var kursContainer = document.getElementById('kurs-container');
        kursContainer.style.display = currency === '€' ? 'block' : 'none';
    }

    {{-- function toggleApprovalField() {
        if (userType !== 'partners') {
            var accountId = document.getElementById('account_id').value;
            var approvalContainer = document.getElementById('approval-container');
            if (approvalContainer) {
                approvalContainer.style.display = (accountId === '45' || accountId === '47') ? 'block' : 'none';
            }
        }
    } --}}

    document.getElementById('amount_before_tax').addEventListener('input', calculateAmountAfterTax);
    document.getElementById('tax').addEventListener('input', calculateAmountAfterTax);
    document.getElementById('operator').addEventListener('change', calculateAmountAfterTax);
    document.getElementById('currency').addEventListener('change', function() {
        toggleKursField();
        calculateAmountAfterTax();
    });
    document.getElementById('account_id').addEventListener('change', toggleApprovalField);

    toggleKursField();
    {{-- toggleApprovalField(); --}}
</script>

