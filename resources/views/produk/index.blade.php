@extends('layouts.app')
@section('title', $kategori ?: 'Semua Produk')

@section('content')
@php $semuaKategori = ['Kaos', 'Kemeja', 'Jaket', 'Celana', 'Jeans', 'Knitwear']; @endphp

<section class="hero-halaman py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Beranda</a></li>
                <li class="breadcrumb-item active">{{ $kategori ?: 'Semua Produk' }}</li>
            </ol>
        </nav>
        <h1>{{ $kategori ?: 'Semua Produk' }}</h1>
        <p class="mb-0 text-light">
            {{ $produk->count() }} produk ditemukan
            @if ($cari) untuk pencarian "{{ $cari }}" @endif
        </p>
    </div>
</section>

<div class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('produk.index', array_filter(['q' => $cari])) }}" class="pill {{ !$kategori ? 'aktif' : '' }}">Semua</a>
            @foreach ($semuaKategori as $k)
                <a href="{{ route('produk.index', array_filter(['kategori' => $k, 'q' => $cari])) }}" class="pill {{ $kategori === $k ? 'aktif' : '' }}">{{ $k }}</a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('produk.index') }}" class="mb-2">
            @if ($kategori) <input type="hidden" name="kategori" value="{{ $kategori }}"> @endif
            @if ($cari) <input type="hidden" name="q" value="{{ $cari }}"> @endif
            <select name="urut" class="form-select" onchange="this.form.submit()">
                <option value="" {{ !$urut ? 'selected' : '' }}>Urutkan: Terbaru</option>
                <option value="termurah" {{ $urut === 'termurah' ? 'selected' : '' }}>Harga terendah</option>
                <option value="termahal" {{ $urut === 'termahal' ? 'selected' : '' }}>Harga tertinggi</option>
            </select>
        </form>
    </div>

    <div class="row g-4">
        @forelse ($produk as $p)
            <div class="col-6 col-md-4 col-lg-3">
                @include('partials.kartu', ['p' => $p])
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <p class="fs-5 mb-3">Produk tidak ditemukan.</p>
                <a href="{{ route('produk.index') }}" class="btn btn-mono">Lihat Semua Produk</a>
            </div>
        @endforelse
    </div>
</div>
@endsection