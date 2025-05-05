@if($projects->isNotEmpty())
    @foreach($projects as $p)
        <li>
            <strong>{{ $p->project_name }}</strong><br>
            <small>Deadline: {{ \Carbon\Carbon::parse($p->end_date)->format('d M Y') }} ({{ $p->days_left }} hari lagi)</small>
        </li>
        <hr>
    @endforeach
@else
    <li>No Reminder.</li>
@endif