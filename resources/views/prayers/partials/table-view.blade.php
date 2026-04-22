<div id="table-view" style="display: none;">
    @if($prayers->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="mdi mdi-heart-outline mdi-48px mb-3 d-block" style="color: var(--sword-gold);"></i>
                <h5 class="text-muted mb-1">No prayers recorded yet</h5>
                <p class="text-muted small">Click <strong>Create New</strong> to add your first entry.</p>
            </div>
        </div>
    @else
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            @foreach ($prayerTypes as $type)
                                <th>{{ $type->name }}</th>
                            @endforeach
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prayers as $date => $dayPrayers)
                            @php
                                $prayersByType = $dayPrayers->keyBy('prayer_type_id');
                            @endphp
                            <tr>
                                <td class="text-nowrap">
                                    <strong>{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($date)->format('l') }}</small>
                                </td>
                                @foreach ($prayerTypes as $type)
                                    <td>
                                        @if (isset($prayersByType[$type->id]))
                                            <span class="text-muted">{{ Str::limit($prayersByType[$type->id]->content, 100) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-icon" data-bs-toggle="modal" data-bs-target="#sendPrayerModal" data-date="{{ $date }}" data-prayers='@json($dayPrayers)'>
                                        <i class="mdi mdi-email-outline"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
