<!-- Card/Grid View -->
<div id="gridView" class="row">
    @foreach ($topics as $topic)
        <div class="col-lg-4 col-xl-3 col-md-6 mb-4">
            <a href="{{ route('topics.edit', $topic->id) }}" class="text-decoration-none">
                <div class="card h-100 topic-card">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title text-dark mb-3">{{ $topic->name }}</h5>
                            <p class="text-muted small mb-0">{{ Str::limit($topic->description, 80) }}</p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="badge bg-secondary">{{ $topic->keywords ? count(explode(',', $topic->keywords)) : 0 }} keywords</span>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

@push('css')
<style>
    .topic-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }
    .topic-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-color: #6c7ae0;
    }
    .view-toggle.active {
        background-color: #e9ecef !important;
    }
</style>
@endpush

@push('js')
<script>
    $(document).ready(function() {
        // View toggle - show grid view
        $('.view-toggle').click(function() {
            $('.view-toggle').removeClass('active');
            $(this).addClass('active');
            
            var view = $(this).data('view');
            
            if (view === 'grid') {
                $('#listView').hide();
                $('#gridView').show();
            } else {
                $('#gridView').hide();
                $('#listView').show();
            }
        });
    });
</script>
@endpush
