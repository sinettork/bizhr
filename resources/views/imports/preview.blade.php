<x-layouts::app :title="'Preview '.$definition['title'].' import'">
    <x-workspace-command-bar :title="'Preview '.$definition['title']" icon="fa-file-circle-check" context="Import data">
        <x-slot:actions>
            <a class="btn btn-action-link btn-sm" href="{{ route('imports.index') }}"><i class="fa-solid fa-xmark"></i><span>Cancel</span></a>
            @if ($invalidCount === 0)
                <form method="POST" action="{{ route('imports.confirm', $type) }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <button class="btn btn-action-link btn-sm" type="submit"><i class="fa-solid fa-file-import"></i><span>Confirm import</span></button>
                </form>
            @endif
        </x-slot:actions>
    </x-workspace-command-bar>

    <div class="alert alert-{{ $invalidCount === 0 ? 'success' : 'warning' }}">
        {{ number_format($totalRows) }} rows found · {{ number_format($invalidCount) }} rows need attention.
        @if ($invalidCount > 0) Import is disabled until every row is valid. @endif
    </div>

    <div class="reference-list">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th class="ps-3">Row</th><th>Result</th>@foreach ($definition['headers'] as $header)<th>{{ \Illuminate\Support\Str::headline($header) }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td class="ps-3">{{ $row['line'] }}</td>
                            <td>@if ($row['errors'])<span class="badge text-bg-danger">Invalid</span><small class="d-block text-danger">{{ implode(' ', $row['errors']) }}</small>@else<span class="badge text-bg-success">Ready</span>@endif</td>
                            @foreach ($definition['headers'] as $header)<td>{{ $row['raw'][$header] ?: '—' }}</td>@endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$paginator" />
    </div>
</x-layouts::app>
