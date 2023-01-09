
<ul>
    @foreach($subtasks as $subtask)
        <?php
            dd($subtask);
        ?>
            <li>{{$subtask->name}}</li>
            @if(count($subtask->subtasks))
                @include('project_task.subCategoryList',['subtasks' => $subtask->subtasks])
            @endif
    @endforeach
</ul>

