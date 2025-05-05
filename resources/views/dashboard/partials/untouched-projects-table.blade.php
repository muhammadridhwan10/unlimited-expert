<div id="untouched-table">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Status</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($untouchedProjects as $p)
                    <tr>
                        <td><a href="{{ route('projects.show', \Crypt::encrypt($p->id)) }}">{{ $p->project_name }}</a></td>
                        <td>{{ ucfirst($p->status) }}</td>
                        <td>{{ $p->end_date ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(\Auth::user()->type == "partners")

        <div class="d-flex mt-4" style="font-size:10px">
            {!! $untouchedProjects->appends(['inprogress_page' => request('inprogress_page')])
                ->withPath(route('dashboard.untouched.projects'))
                ->links() !!}
        </div>

    @else

        <div class="d-flex mt-4">
            {!! $untouchedProjects->appends(['inprogress_page' => request('inprogress_page')])
                ->withPath(route('dashboard.untouched.projects'))
                ->links() !!}
        </div>

    @endif
</div>