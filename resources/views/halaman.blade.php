@extends('layouts.app')
@section('title', $halaman['judul'])

@section('content')
<section class="hero-halaman py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Beranda</a></li>
                <li class="breadcrumb-item active">{{ $halaman['judul'] }}</li>
            </ol>
        </nav>
        <h1>{{ $halaman['judul'] }}</h1>
    </div>
</section>

<div class="container mt-5" style="max-width: 820px;">
    @foreach ($halaman['isi'] as $paragraf)
        <p class="fs-5">{{ $paragraf }}</p>
    @endforeach

    @if (!empty($halaman['daftar']))
        <ul class="list-group list-group-flush border-top border-bottom mb-4">
            @foreach ($halaman['daftar'] as $item)
                <li class="list-group-item py-3">{{ $item }}</li>
            @endforeach
        </ul>
    @endif

    <a href="{{ route('beranda') }}" class="btn btn-mono">&larr; Kembali ke Beranda</a>
</div>
@endsection