<!-- Datatable/List View -->
<div id="listView" class="row" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable-topics" class="table table-bordered table-hover table-compact">
                        <thead>
                            <tr>
                                <th>Topic</th>
                                <th>Description</th>
                                <th>Keywords</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topics as $topic)
                                <tr class="topic-row" data-href="{{ route('topics.edit', $topic->id) }}" style="cursor: pointer;">
                                    <td>{{ $topic->name }}</td>
                                    <td>{{ Str::limit($topic->description, 50) }}</td>
                                    <td>{{ $topic->keywords }}</td>
                                    <td>
                                        <a href="{{ route('topics.edit', $topic->id) }}" class="btn btn-outline-primary" style="padding: 1px 6px; font-size: 0.75rem; line-height: 1.4;">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
.table-compact th,
.table-compact td {
    padding: 0.28rem 0.55rem !important;
    font-size: 0.82rem !important;
    vertical-align: middle !important;
}
</style>
@endpush

@push('js')
<script>
    $(document).ready(function() {
        let table = null;

        // Initialize DataTable when list view is shown
        $(document).on('click', '.view-toggle[data-view="list"]', function() {
            if (!table) {
                table = $('#datatable-topics').DataTable({
                    "order": [
                        [0, "desc"],
                    ],
                    "pageLength": 25,
                    "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
                });
            }
        });

        // Make table rows clickable
        $(document).on('click', '.topic-row', function(e) {
            // Don't navigate if clicking on a link/button
            if ($(e.target).closest('a, button').length === 0) {
                window.location.href = $(this).data('href');
            }
        });
    });
</script>
@endpush
