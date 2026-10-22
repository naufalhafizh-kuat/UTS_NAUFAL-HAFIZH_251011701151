@extends('layouts.app')
@section('title', $produk->name)

@section('content')
@php
    $fav  = in_array($produk->id, session('favorit', []));
    $baru = $produk->id >= 9;
@endphp

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

            <form method="POST" action="{{ route('tas.tambah', $produk->id) }}">
                @csrf
                <div class="mb-4">
                    <div class="fw-bold text-uppercase mb-2" style="letter-spacing: 2px; font-size: .9rem;">Pilih Ukuran</div>
                    @foreach (['S', 'M', 'L', 'XL'] as $u)
                        <input type="radio" class="btn-check" name="ukuran" value="{{ $u }}" id="ukuran{{ $u }}" autocomplete="off" @checked($loop->first)>
                        <label class="btn btn-outline-mono me-2 py-2 px-4" for="ukuran{{ $u }}">{{ $u }}</label>
                    @endforeach
                </div>
                <div class="d-grid mb-2">
                    <button type="submit" class="btn btn-emas py-3 fs-6">Tambah ke Tas</button>
                </div>
            </form>

            <form method="POST" action="{{ route('favorit.toggle', $produk->id) }}" class="d-grid mb-4">
                @csrf
                <button type="submit" class="btn btn-outline-mono py-2">{{ $fav ? '♥ Hapus dari Favorit' : '♡ Simpan ke Favorit' }}</button>
            </form>

            <ul class="info-mini">
                <li>Gratis ongkir untuk belanja minimal Rp 499.000</li>
                <li>Retur mudah maksimal 14 hari</li>
                <li>Bahan nyaman dan awet dipakai</li>
            </ul>

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
                        <button class="accordion-button collapsed text-uppercase fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#a2">Pengiriman &amp; Pengembalian</button>
                    </h2>
                    <div id="a2" class="accordion-collapse collapse" data-bs-parent="#info">
                        <div class="accordion-body">Gratis ongkir untuk belanja minimal Rp 499.000, selain itu ongkir Rp 20.000. Pengembalian barang maksimal 14 hari setelah diterima.</div>
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