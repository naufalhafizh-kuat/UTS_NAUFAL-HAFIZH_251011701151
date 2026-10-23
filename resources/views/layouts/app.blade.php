<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Fashion Online') | {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('Bootstrap/css/bootstrap.min.css') }}">

    <style>
        :root { --hitam: #111111; --emas: #c9a227; --emas-tua: #a8861a; --krem: #faf6ee; --garis: #e6dcc6; }
        html { font-size: 17px; }
        body { background: #fff; color: var(--hitam); font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif; line-height: 1.65; }
        a { color: inherit; text-decoration: none; }
        h1, h2, h3, h4, h5 { font-family: 'Playfair Display', Georgia, serif; }
        ::selection { background: var(--emas); color: #000; }

        .promo-marquee { background: var(--hitam); color: var(--emas); overflow: hidden; font-size: .82rem; letter-spacing: 2px; font-weight: 500; }
        .marquee-isi { display: inline-flex; white-space: nowrap; padding: 9px 0; animation: geser 32s linear infinite; }
        .marquee-isi span { margin-right: 2.4rem; }
        .promo-marquee:hover .marquee-isi { animation-play-state: paused; }
        @keyframes geser { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        .hdr { z-index: 1030; box-shadow: 0 2px 14px rgba(0,0,0,.07); }
        .logo { font-family: 'Playfair Display', Georgia, serif; font-weight: 900; font-size: 2.1rem; letter-spacing: 5px; text-transform: uppercase; color: var(--hitam); line-height: 1; }
        .logo:hover { color: var(--hitam); }
        .hdr .nav-link { color: var(--hitam); font-size: .92rem; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 500; padding: 1.3rem .9rem; border-bottom: 3px solid transparent; }
        .hdr .nav-link:hover, .hdr .nav-link.active { border-bottom-color: var(--emas); color: var(--hitam); }
        .ikon { color: var(--hitam); display: inline-flex; margin-left: 18px; background: none; border: 0; padding: 0; transition: color .2s, transform .2s; }
        .ikon:hover { color: var(--emas-tua); transform: translateY(-2px); }
        .ikon svg { width: 27px; height: 27px; fill: none; stroke: currentColor; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
        .cari-bar input { font-size: 1.1rem; padding: 14px 20px; border: 2px solid var(--hitam); border-radius: 0; }
        .cari-bar input:focus { box-shadow: none; border-color: var(--emas); }

        .btn { font-family: 'Poppins', sans-serif; }
        .btn-mono { background: var(--hitam); color: #fff; border: 2px solid var(--hitam); border-radius: 0; text-transform: uppercase; letter-spacing: 1.5px; font-size: .9rem; font-weight: 600; padding: 12px 28px; transition: all .2s; }
        .btn-mono:hover { background: var(--emas); border-color: var(--emas); color: #000; }
        .btn-emas { background: var(--emas); color: #000; border: 2px solid var(--emas); border-radius: 0; text-transform: uppercase; letter-spacing: 1.5px; font-size: .9rem; font-weight: 700; padding: 13px 30px; transition: all .2s; }
        .btn-emas:hover { background: #fff; border-color: #fff; color: #000; transform: translateY(-2px); }
        .btn-garis { background: transparent; color: #fff; border: 2px solid #fff; border-radius: 0; text-transform: uppercase; letter-spacing: 1.5px; font-size: .9rem; font-weight: 600; padding: 13px 30px; transition: all .2s; }
        .btn-garis:hover { background: #fff; color: #000; }
        .btn-outline-mono { border: 2px solid var(--hitam); color: var(--hitam); border-radius: 0; text-transform: uppercase; letter-spacing: 1.5px; font-size: .9rem; font-weight: 600; background: #fff; min-width: 52px; transition: all .2s; }
        .btn-outline-mono:hover { background: var(--hitam); color: #fff; }
        .form-select { border-radius: 0; border: 1.5px solid #aaa; padding: .65rem .9rem; font-size: 1rem; }
        .form-select:focus { box-shadow: none; border-color: var(--emas); }

        .judul-bagian { margin-bottom: 2.2rem; text-align: center; }
        .judul-bagian span { display: block; color: var(--emas-tua); letter-spacing: 5px; font-size: .82rem; font-weight: 600; text-transform: uppercase; margin-bottom: 6px; }
        .judul-bagian h2 { font-size: 2.4rem; font-weight: 700; margin: 0; position: relative; display: inline-block; padding-bottom: 16px; }
        .judul-bagian h2::after { content: ''; position: absolute; left: 50%; bottom: 0; width: 64px; height: 4px; background: var(--emas); transform: translateX(-50%); }

        .slide { display: flex; min-height: 80vh; color: #fff; }
        .slide-teks { flex: 1; padding: 70px 6vw; display: flex; flex-direction: column; justify-content: center; align-items: flex-start; }
        .slide-teks .tag { display: inline-block; background: var(--emas); color: #000; font-weight: 700; font-size: .82rem; letter-spacing: 3px; padding: 6px 16px; margin-bottom: 22px; text-transform: uppercase; }
        .slide-teks h1 { font-size: clamp(2.5rem, 5.2vw, 4.6rem); font-weight: 900; line-height: 1.1; margin-bottom: 20px; }
        .slide-teks h1 em { color: var(--emas); font-style: italic; }
        .slide-teks p { font-size: 1.2rem; max-width: 520px; color: #ddd; margin-bottom: 30px; }
        .slide-foto { flex: 0 0 42%; background: linear-gradient(135deg, #3a3a3a, #111) center top / cover no-repeat; position: relative; }
        .slide-foto::after { content: ''; position: absolute; inset: 0; box-shadow: inset 14px 0 0 var(--emas); }
        .carousel-indicators [data-bs-target] { width: 38px; height: 4px; background: var(--emas); border: 0; }
        @media (max-width: 768px) {
            .slide { flex-direction: column-reverse; min-height: 0; }
            .slide-foto { flex: none; height: 46vh; }
            .slide-foto::after { box-shadow: inset 0 -10px 0 var(--emas); }
            .slide-teks { padding: 36px 24px 56px; }
        }

        .keunggulan { background: var(--krem); border-top: 1px solid var(--garis); border-bottom: 1px solid var(--garis); }
        .keunggulan .item { display: flex; align-items: center; gap: 14px; padding: 22px 8px; }
        .keunggulan .bulat { flex: none; width: 54px; height: 54px; border-radius: 50%; background: var(--hitam); color: var(--emas); display: flex; align-items: center; justify-content: center; }
        .keunggulan svg { width: 27px; height: 27px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
        .keunggulan b { display: block; font-size: 1rem; }
        .keunggulan small { color: #666; font-size: .85rem; }

        .kat-bulat { display: block; text-align: center; }
        .kat-bulat .foto { width: 150px; height: 150px; margin: 0 auto 14px; border-radius: 50%; overflow: hidden; border: 4px solid #fff; box-shadow: 0 0 0 3px var(--garis); transition: box-shadow .25s, transform .25s; background: #e5e5e5; }
        .kat-bulat .foto img { width: 100%; height: 100%; object-fit: cover; object-position: center 20%; }
        .kat-bulat:hover .foto { box-shadow: 0 0 0 4px var(--emas); transform: translateY(-5px); }
        .kat-bulat b { display: block; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 1.5px; }
        .kat-bulat small { color: #777; }

        .kartu-produk .gambar-wrap { position: relative; overflow: hidden; background: #ececec; }
        .kartu-produk .gambar { display: block; aspect-ratio: 3 / 4; }
        .kartu-produk .gambar img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s; }
        .kartu-produk:hover .gambar img { transform: scale(1.07); }
        .badge-baru { position: absolute; top: 12px; left: 12px; z-index: 2; background: var(--emas); color: #000; font-weight: 700; font-size: .75rem; letter-spacing: 2px; padding: 4px 12px; }
        .kartu-produk .info { padding-top: 14px; }
        .kartu-produk .kat { font-size: .75rem; color: var(--emas-tua); text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }
        .kartu-produk .nama { display: block; font-size: 1.08rem; font-weight: 500; margin: 2px 0 4px; }
        .kartu-produk .nama:hover { color: var(--emas-tua); }
        .kartu-produk .harga { font-size: 1.2rem; font-weight: 700; }

        .testi { background: var(--krem); }
        .testi .kartu-testi { background: #fff; border: 1px solid var(--garis); padding: 28px; height: 100%; position: relative; }
        .testi .kartu-testi::before { content: '\201C'; position: absolute; top: -8px; left: 20px; font-family: 'Playfair Display', serif; font-size: 5rem; color: var(--emas); line-height: 1; }
        .testi .bintang { color: var(--emas); letter-spacing: 3px; font-size: 1.15rem; margin-top: 14px; }

        .hero-halaman { background: var(--hitam); color: #fff; border-bottom: 5px solid var(--emas); }
        .hero-halaman h1 { font-size: 2.8rem; font-weight: 900; }
        .hero-halaman .breadcrumb { --bs-breadcrumb-divider-color: #999; --bs-breadcrumb-item-active-color: var(--emas); margin-bottom: 6px; font-size: .9rem; }
        .hero-halaman .breadcrumb a { color: #ddd; }

        .pill { display: inline-block; border: 2px solid var(--hitam); padding: 7px 20px; margin: 0 8px 10px 0; font-size: .9rem; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; transition: all .2s; }
        .pill.aktif, .pill:hover { background: var(--hitam); color: var(--emas); }
        .accordion-button { font-size: .95rem; }
        .accordion-button:not(.collapsed) { background: var(--krem); color: #000; box-shadow: none; }
        .accordion-button:focus { box-shadow: none; }
        .info-mini { list-style: none; padding: 0; margin: 0 0 1.5rem; }
        .info-mini li { padding: 6px 0; font-size: .98rem; }
        .info-mini li::before { content: '\2713'; color: var(--emas-tua); font-weight: 700; margin-right: 10px; }
        .harga-besar { font-size: 2rem; font-weight: 700; }

        footer { background: var(--hitam); color: #bbb; font-size: .98rem; }
        footer h6 { color: var(--emas); letter-spacing: 3px; font-weight: 600; font-size: .9rem; margin-bottom: 18px; text-transform: uppercase; font-family: 'Poppins', sans-serif; }
        footer a { display: block; padding: 4px 0; color: #bbb; transition: color .2s, padding-left .2s; }
        footer a:hover { color: var(--emas); padding-left: 5px; }
        footer .logo { color: #fff; }
    </style>
</head>
<body>

@php $menu = ['Kaos', 'Kemeja', 'Jaket', 'Celana', 'Jeans', 'Knitwear']; @endphp

<div class="promo-marquee">
    <div class="marquee-isi">
        @for ($i = 0; $i < 2; $i++)
            <span>KOLEKSI BARU SETIAP MINGGU</span><span>&#9670;</span>
            <span>BAHAN NYAMAN DAN AWET</span><span>&#9670;</span>
            <span>MODEL TERBARU</span><span>&#9670;</span>
            <span>HARGA BERSAHABAT</span><span>&#9670;</span>
        @endfor
    </div>
</div>

<header class="hdr sticky-top bg-white">
    <nav class="navbar navbar-expand-xl py-0">
        <div class="container-fluid px-3 px-xl-5">
            <a class="logo py-3" href="{{ route('beranda') }}">{{ config('app.name') }}</a>

            <div class="d-flex align-items-center order-xl-last">
                <button class="ikon" type="button" data-bs-toggle="collapse" data-bs-target="#cari" aria-label="Cari">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
                </button>
                <button class="navbar-toggler border-0 ms-3 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-label="Menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse justify-content-center" id="menu">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('produk.index') && !request('kategori') ? 'active' : '' }}" href="{{ route('produk.index') }}">Semua</a>
                    </li>
                    @foreach ($menu as $m)
                        <li class="nav-item">
                            <a class="nav-link {{ request('kategori') === $m ? 'active' : '' }}" href="{{ route('produk.index', ['kategori' => $m]) }}">{{ $m }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </nav>

    <div class="collapse border-top" id="cari">
        <div class="container py-3">
            <form class="cari-bar" action="{{ route('produk.index') }}" method="GET">
                <input type="text" name="q" class="form-control" placeholder="Cari produk, lalu tekan Enter..." value="{{ request('q') }}">
            </form>
        </div>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="mt-5 pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="logo fs-3 mb-3">{{ config('app.name') }}</div>
                <p>Fashion simpel, nyaman, dan berkelas untuk keseharianmu. Koleksi baju dan celana pilihan dengan harga bersahabat.</p>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Belanja</h6>
                <a href="{{ route('produk.index') }}">Semua Produk</a>
                @foreach ($menu as $m)
                    <a href="{{ route('produk.index', ['kategori' => $m]) }}">{{ $m }}</a>
                @endforeach
            </div>
            <div class="col-6 col-lg-3">
                <h6>Info Perusahaan</h6>
                <a href="{{ route('halaman', 'tentang') }}">Tentang {{ config('app.name') }}</a>
                <a href="{{ route('halaman', 'karir') }}">Berkarir</a>
                <a href="{{ route('halaman', 'keberlanjutan') }}">Keberlanjutan</a>
                <a href="{{ route('halaman', 'pers') }}">Pers</a>
            </div>
            <div class="col-6 col-lg-3">
                <h6>Bantuan</h6>
                <a href="{{ route('halaman', 'customer-service') }}">Customer Service</a>
                <a href="{{ route('halaman', 'lokasi-toko') }}">Lokasi Toko</a>
                <a href="{{ route('halaman', 'kebijakan-privasi') }}">Kebijakan Privasi</a>
                <a href="{{ route('halaman', 'kontak') }}">Kontak</a>
            </div>
        </div>
        <hr class="border-secondary mt-5">
        <p class="mb-0 text-center">&copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi. Dibuat untuk UTS Rekayasa Web.</p>
    </div>
</footer>

<script src="{{ asset('Bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>