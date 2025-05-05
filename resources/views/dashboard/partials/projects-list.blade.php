@foreach ($projects as $p)
    <tr>
        <td><a href="{{ route('projects.show', $p->id) }}">{{ $p->project_name }}</a></td>
        <td>{{ ucfirst($p->status) }}</td>
        <td>{{ $p->end_date ?? '-' }}</td>
    </tr>
@endforeach