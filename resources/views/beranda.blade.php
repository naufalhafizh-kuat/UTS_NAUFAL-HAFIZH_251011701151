@extends('layouts.app')
@section('title', 'Beranda')

@section('content')
@php
    $foto = fn ($n) => file_exists(public_path("assets/images/$n.jpg")) ? asset("assets/images/$n.jpg") : null;

    $slides = [
        ['foto' => $foto('hero-1'), 'bg' => '#101010', 'tag' => 'Koleksi Terbaru',
         'judul' => 'Gaya Baru,<br><em>Percaya Diri</em> Baru',
         'teks' => 'Baju dan celana pilihan dengan bahan nyaman dan potongan rapi untuk keseharianmu.',
         'url' => route('produk.index'), 'label' => 'Lihat Produk'],
        ['foto' => $foto('hero-2'), 'bg' => '#1f1810', 'tag' => 'Essentials',
         'judul' => 'Kaos Basic<br>untuk <em>Setiap Hari</em>',
         'teks' => 'Kaos polos berkualitas yang mudah dipadukan dengan apa saja.',
         'url' => route('produk.index', ['kategori' => 'Kaos']), 'label' => 'Lihat Kaos'],
        ['foto' => $foto('hero-3'), 'bg' => '#14181c', 'tag' => 'Denim Edit',
         'judul' => 'Jeans &amp; Celana<br>yang <em>Pas</em> di Kamu',
         'teks' => 'Dari jeans barrel sampai chino, temukan potongan favoritmu.',
         'url' => route('produk.index', ['kategori' => 'Jeans']), 'label' => 'Lihat Jeans'],
    ];
@endphp

<div id="hero" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        @foreach ($slides as $i => $s)
            <button type="button" data-bs-target="#hero" data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
        @endforeach
    </div>
    <div class="carousel-inner">
        @foreach ($slides as $i => $s)
            <div class="carousel-item {{ $i === 0 ? 'active' : '' }}" data-bs-interval="6000">
                <div class="slide" style="background: {{ $s['bg'] }};">
                    <div class="slide-teks">
                        <span class="tag">{{ $s['tag'] }}</span>
                        <h1>{!! $s['judul'] !!}</h1>
                        <p>{{ $s['teks'] }}</p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="{{ $s['url'] }}" class="btn btn-emas">{{ $s['label'] }}</a>
                            <a href="{{ route('produk.index') }}" class="btn btn-garis">Semua Produk</a>
                        </div>
                    </div>
                    <div class="slide-foto" style="@if($s['foto'])background-image:url('{{ $s['foto'] }}');@endif"></div>
                </div>
            </div>
        @endforeach
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#hero" data-bs-slide="prev" aria-label="Sebelumnya">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#hero" data-bs-slide="next" aria-label="Berikutnya">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>

<section class="keunggulan">
    <div class="container">
        <div class="row g-0">
            <div class="col-6 col-lg-3"><div class="item">
                <div class="bulat"><svg viewBox="0 0 24 24"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/></svg></div>
                <div><b>Kualitas Terjamin</b><small>Bahan nyaman dan awet</small></div>
            </div></div>
            <div class="col-6 col-lg-3"><div class="item">
                <div class="bulat"><svg viewBox="0 0 24 24"><path d="M3 12l9-9 9 9-9 9z"/></svg></div>
                <div><b>Model Terbaru</b><small>Koleksi baru tiap minggu</small></div>
            </div></div>
            <div class="col-6 col-lg-3"><div class="item">
                <div class="bulat"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg></div>
                <div><b>Harga Bersahabat</b><small>Terjangkau untuk semua</small></div>
            </div></div>
            <div class="col-6 col-lg-3"><div class="item">
                <div class="bulat"><svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"/></svg></div>
                <div><b>Banyak Pilihan</b><small>Kaos, kemeja, jeans, dan lainnya</small></div>
            </div></div>
        </div>
    </div>
</section>

<section class="container mt-5 pt-4">
    <div class="judul-bagian"><span>Jelajahi</span><h2>Belanja per Kategori</h2></div>
    <div class="row g-4 justify-content-center">
        @foreach ($kategoriList as $k)
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('produk.index', ['kategori' => $k['nama']]) }}" class="kat-bulat">
                    <div class="foto"><img src="{{ $k['produk']->gambar_url }}" alt="{{ $k['nama'] }}" loading="lazy"></div>
                    <b>{{ $k['nama'] }}</b>
                    <small>{{ $k['jumlah'] }} produk</small>
                </a>
            </div>
        @endforeach
    </div>
</section>

<section class="container mt-5 pt-4">
    <div class="judul-bagian"><span>Terbaru</span><h2>Barang Baru</h2></div>
    <div class="row g-4">
        @foreach ($baru as $p)
            <div class="col-6 col-md-4 col-lg-3">
                @include('partials.kartu', ['p' => $p])
            </div>
        @endforeach
    </div>
    <div class="text-center mt-5">
        <a href="{{ route('produk.index') }}" class="btn btn-mono">Lihat Semua Produk</a>
    </div>
</section>

<section class="testi py-5 mt-5">
    <div class="container py-3">
        <div class="judul-bagian"><span>Testimoni</span><h2>Kata Mereka</h2></div>
        <div class="row g-4">
            @foreach ([
                ['Rina, Jakarta', 'Bahannya adem dan jahitannya rapi. Kaos hitamnya sudah saya beli dua kali.'],
                ['Dimas, Bandung', 'Ukurannya pas. Jeans barrel-nya nyaman dipakai seharian dan modelnya kekinian.'],
                ['Alya, Surabaya', 'Hoodie-nya tebal dan hangat, warnanya sesuai foto. Pasti belanja lagi di sini.'],
            ] as $t)
                <div class="col-md-4">
                    <div class="kartu-testi">
                        <div class="bintang">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                        <p class="mt-2">{{ $t[1] }}</p>
                        <div class="fw-bold">{{ $t[0] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@includeIf('partials.identitas')
@endsection