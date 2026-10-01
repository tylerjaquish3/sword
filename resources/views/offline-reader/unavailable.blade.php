@extends('base.layout')

@section('title', 'Not Available Offline')

@section('content')
<div class="text-center py-5">
    <p class="mb-3">This section isn't available offline.</p>
    <p class="text-muted small mb-4">
        Offline access covers reading the Bible, your comments and highlights, prayers,
        and weekly digest reflections.
    </p>
    <div class="d-flex gap-2 justify-content-center">
        <a href="{{ route('offline-reader.index') }}" class="btn btn-primary btn-sm">Read</a>
        <a href="{{ route('offline-reader.prayers') }}" class="btn btn-primary btn-sm">Prayers</a>
        <a href="{{ route('offline-reader.digest') }}" class="btn btn-primary btn-sm">Digest</a>
    </div>
</div>
@endsection
