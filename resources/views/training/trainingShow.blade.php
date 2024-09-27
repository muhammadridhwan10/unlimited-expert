<div class="modal-body">
    <div class="row mt-2">
        <table class="table datatable">
            <thead>
            <tr>
                <th style="border: none;">{{__('Training Title')}}</th>
                <th style="border: none;">{{__('Training Type')}}</th>
                <th style="border: none;">{{__('Internal Or External')}}</th>
                <th style="border: none;">{{__('Location')}}</th>
                <th style="border: none;">{{__('Year')}}</th>
                <th style="border: none;">{{__('Description')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($training as $employee_training)
                <tr>
                    <td class="text-end" style="border: none;">{{ !empty($employee_training->training_title)?$employee_training->training_title:'-' }}</td>
                    <td class="text-end" style="border: none;">{{ !empty($employee_training->types)?$employee_training->types->name:'-' }}</td>
                    <td class="text-end" style="border: none;">{{ !empty($employee_training->trainer_option)?$employee_training->trainer_option:'-' }}</td>
                    <td class="text-end" style="border: none;">{{ !empty($employee_training->location)?$employee_training->location:'-' }}</td>
                    <td class="text-end" style="border: none;">{{ !empty($employee_training->year)?$employee_training->year:'-' }}</td>
                    <td class="text-end" style="border: none;">{{ !empty($employee_training->description)?$employee_training->description:'-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
