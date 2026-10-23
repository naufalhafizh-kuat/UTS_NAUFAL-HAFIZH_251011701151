@extends('layouts.app')
@section('title', $produk->name)

@section('content')
@php $baru = $produk->id >= 9; @endphp

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('produk.index', ['kategori' => $produk->category]) }}">{{ $produk->category }}</a></li>
            <li class="breadcrumb-item active">{{ $produk->name }}</li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-md-6 col-lg-5">
            <div class="position-relative" style="aspect-ratio: 3 / 4; background: #ececec; border-bottom: 5px solid var(--emas);">
                @if ($baru) <span class="badge-baru">BARU</span> @endif
                <img src="{{ $produk->gambar_url }}" alt="{{ $produk->name }}" class="w-100 h-100" style="object-fit: cover;">
            </div>
        </div>

        <div class="col-md-6 col-lg-6">
            <div class="text-uppercase fw-semibold" style="letter-spacing: 3px; color: var(--emas-tua); font-size: .85rem;">{{ $produk->category }}</div>
            <h1 class="fw-bold mt-1" style="font-size: 2.4rem;">{{ $produk->name }}</h1>
            <p class="harga-besar">Rp {{ number_format($produk->price, 0, ',', '.') }}</p>
            <p class="fs-6 text-secondary">{{ $produk->description }}</p>

            <p class="mb-1 fw-bold text-uppercase" style="letter-spacing: 2px; font-size: .9rem;">Ukuran tersedia</p>
            <p class="mb-4">S, M, L, XL</p>

            <ul class="info-mini">
                <li>Bahan nyaman dan awet dipakai</li>
                <li>Model sederhana, mudah dipadukan</li>
                <li>Kategori: {{ $produk->category }}</li>
            </ul>

            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="{{ route('produk.index', ['kategori' => $produk->category]) }}" class="btn btn-mono">&larr; Kembali ke {{ $produk->category }}</a>
                <a href="{{ route('produk.index') }}" class="btn btn-outline-mono">Semua Produk</a>
            </div>

            <div class="accordion accordion-flush border-top" id="info">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed text-uppercase fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#a1">Deskripsi &amp; Detail</button>
                    </h2>
                    <div id="a1" class="accordion-collapse collapse" data-bs-parent="#info">
                        <div class="accordion-body">{{ $produk->description }}</div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed text-uppercase fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#a2">Perawatan</button>
                    </h2>
                    <div id="a2" class="accordion-collapse collapse" data-bs-parent="#info">
                        <div class="accordion-body">Cuci dengan air dingin, jangan gunakan pemutih, dan setrika dengan suhu sedang agar bahan tetap awet.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($serupa->count())
        <section class="mt-5 pt-4">
            <div class="judul-bagian"><span>Rekomendasi</span><h2>Mungkin Kamu Suka</h2></div>
            <div class="row g-4">
                @foreach ($serupa as $p)
                    <div class="col-6 col-md-3">
                        @include('partials.kartu', ['p' => $p])
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection