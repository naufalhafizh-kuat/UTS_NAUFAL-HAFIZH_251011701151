@extends('layouts.app')
@section('title', 'Favorit')

@section('content')
<div class="container mt-4">
    <h1 class="seksi-judul fs-3 mb-4">Favorit</h1>

    @if ($produk->count())
        <div class="row g-3">
            @foreach ($produk as $p)
                <div class="col-6 col-md-4 col-lg-3">
                    @include('partials.kartu', ['p' => $p])
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <p class="mb-3">Belum ada produk favorit. Tekan ikon hati pada produk untuk menyimpannya di sini.</p>
            <a href="{{ route('produk.index') }}" class="btn btn-mono">Lihat Produk</a>
        </div>
    @endif
</div>
@endsection