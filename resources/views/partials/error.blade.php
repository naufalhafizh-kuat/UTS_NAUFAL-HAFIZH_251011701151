@if ($errors->any())
    <div class="alert alert-danger rounded-0 small py-2">
        @foreach ($errors->all() as $e)
            <div>{{ $e }}</div>
        @endforeach
    </div>
@endif