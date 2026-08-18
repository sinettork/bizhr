<x-layouts::app title="Announcements & News">
    <x-workspace-command-bar title="Company News & Announcements" icon="fa-newspaper" context="Communications">
        <x-slot:filters>
            <span class="small text-body-secondary"><i class="fa-solid fa-bullhorn me-1 text-primary"></i>Official company communications & notices</span>
        </x-slot:filters>
        <x-slot:actions>
            @can('announcement.manage')
                <a class="btn btn-action-link btn-sm" href="{{ route('announcements.index') }}">
                    <i class="fa-solid fa-gear"></i><span>Manage announcements</span>
                </a>
            @endcan
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-9">
            @forelse($items as $item)
                <div class="card mb-3">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if($item->is_urgent)
                                <span class="badge text-bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Urgent</span>
                            @endif
                            @if($item->is_pinned)
                                <span class="badge text-bg-primary"><i class="fa-solid fa-thumbtack me-1"></i>Pinned</span>
                            @endif
                            <span class="fw-semibold text-dark">{{ $item->title }}</span>
                        </div>
                        <div class="small text-body-secondary">
                            <i class="fa-regular fa-calendar me-1"></i>{{ $item->published_at?->format('d M Y, h:i A') }}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 text-dark" style="white-space: pre-line; line-height: 1.6;">{{ $item->content }}</div>

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
                            <div class="small text-body-secondary">
                                <span class="badge text-bg-light border text-dark"><i class="fa-solid fa-users me-1"></i>{{ str($item->audience_type ?? 'All Staff')->title() }}</span>
                            </div>

                            @if($item->requires_acknowledgement)
                                @if($item->acknowledged_by_me)
                                    <span class="small text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Acknowledged</span>
                                @else
                                    <form method="POST" action="{{ route('announcements.acknowledge', $item) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary" type="submit">
                                            <i class="fa-solid fa-check-double me-1"></i>Acknowledge reading
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-body text-center py-5 text-body-secondary">
                        <i class="fa-solid fa-newspaper fa-xl d-block mb-3 text-primary"></i>
                        No active company announcements at this time.
                    </div>
                </div>
            @endforelse

            <div class="mt-3">
                <x-pagination-footer :paginator="$items" />
            </div>
        </div>
    </div>
</x-layouts::app>
