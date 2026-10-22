@extends('layouts.app')
@section('title', 'Beranda')

@section('content')
@php
    // Foto hero: hero-1.jpg, hero-2.jpg, hero-3.jpg (di public/assets/images)
    $foto = fn ($n) => file_exists(public_path("assets/images/$n.jpg")) ? asset("assets/images/$n.jpg") : null;

    $slides = [
        ['foto' => $foto('hero-1'), 'bg' => '#101010', 'tag' => 'Koleksi Terbaru',
         'judul' => 'Gaya Baru,<br><em>Percaya Diri</em> Baru',
         'teks' => 'Baju dan celana pilihan dengan bahan nyaman dan potongan rapi untuk keseharianmu.',
         'url' => route('produk.index'), 'label' => 'Belanja Sekarang'],
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

{{-- Hero carousel --}}
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

{{-- Keunggulan --}}
<section class="keunggulan">
    <div class="container">
        <div class="row g-0">
            <div class="col-6 col-lg-3"><div class="item">
                <div class="bulat"><svg viewBox="0 0 24 24"><path d="M2 7h11v9H2zM13 10h4l3 3v3h-7z"/><circle cx="6" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/></svg></div>
                <div><b>Gratis Ongkir</b><small>Min. belanja Rp 499.000</small></div>
            </div></div>
            <div class="col-6 col-lg-3"><div class="item">
                <div class="bulat"><svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg></div>
                <div><b>Retur 14 Hari</b><small>Mudah dan tanpa ribet</small></div>
            </div></div>
            <div class="col-6 col-lg-3"><div class="item">
                <div class="bulat"><svg viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="11" rx="1"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></div>
                <div><b>Pembayaran Aman</b><small>Transfer, COD, E-Wallet</small></div>
            </div></div>
            <div class="col-6 col-lg-3"><div class="item">
                <div class="bulat"><svg viewBox="0 0 24 24"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/></svg></div>
                <div><b>Kualitas Terjamin</b><small>Bahan nyaman dan awet</small></div>
            </div></div>
        </div>
    </div>
</section>

{{-- Belanja per kategori --}}
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

{{-- Barang baru --}}
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

{{-- Banner member --}}
<section class="banner-member mt-5 py-5">
    <div class="container position-relative py-4">
        <div class="angka d-none d-md-block">10%</div>
        <div style="max-width: 620px; position: relative; z-index: 1;">
            <span class="tag d-inline-block mb-3" style="background: var(--emas); color: #000; font-weight: 700; letter-spacing: 3px; font-size: .82rem; padding: 6px 16px;">KEANGGOTAAN</span>
            @auth
                <h2>Halo, <em>{{ Str::before(Auth::user()->name, ' ') }}</em>!</h2>
                <p class="fs-5 text-light mb-4">Terima kasih sudah menjadi member {{ config('app.name') }}. Nikmati koleksi terbaru kami.</p>
                <a href="{{ route('produk.index') }}" class="btn btn-emas">Belanja Sekarang</a>
            @else
                <h2>Diskon <em>10%</em> untuk Member Baru</h2>
                <p class="fs-5 text-light mb-4">Daftar gratis sekarang dan dapatkan penawaran khusus untuk pembelian pertamamu.</p>
                <a href="{{ route('daftar') }}" class="btn btn-emas">Daftar Sekarang</a>
            @endauth
        </div>
    </div>
</section>

{{-- Testimoni --}}
<section class="testi py-5 mt-0">
    <div class="container py-3">
        <div class="judul-bagian"><span>Testimoni</span><h2>Kata Mereka</h2></div>
        <div class="row g-4">
            @foreach ([
                ['Rina, Jakarta', 'Bahannya adem dan jahitannya rapi. Kaos hitamnya sudah saya beli dua kali, pengiriman juga cepat!'],
                ['Dimas, Bandung', 'Ukurannya pas sesuai pilihan. Jeans barrel-nya nyaman dipakai seharian dan modelnya kekinian.'],
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

{{-- Newsletter --}}
<section class="newsletter py-5">
    <div class="container py-2">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <h3>Dapatkan Info &amp; Promo Terbaru</h3>
                <p class="mb-0 fs-5">Berlangganan newsletter {{ config('app.name') }} untuk kabar koleksi baru dan penawaran eksklusif.</p>
            </div>
            <div class="col-lg-6">
                <form method="POST" action="{{ route('newsletter') }}" class="d-flex flex-column flex-sm-row gap-2">
                    @csrf
                    <input type="email" name="email_newsletter" class="form-control form-control-lg" placeholder="Masukkan email kamu" required>
                    <button type="submit" class="btn btn-mono px-4">Berlangganan</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection