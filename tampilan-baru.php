<?php
/*
 * tampilan-baru.php - Jalankan dari root project Laravel:  php tampilan-baru.php
 * Redesain Luxcend: font lebih besar, aksen warna emas, carousel hero,
 * strip keunggulan, kategori bulat, badge BARU, hover kartu produk,
 * banner member, testimoni, newsletter, dan footer lengkap.
 * Semua fungsi (tas, checkout, favorit, akun) tetap jalan. Database tidak diubah.
 * Syarat: sudah menjalankan kembali.php / fungsi.php sebelumnya.
 */

if (!file_exists(__DIR__ . '/artisan')) {
    exit("ERROR: jalankan dari dalam folder project Laravel (yang ada file 'artisan').\n");
}

$routeFile = __DIR__ . '/routes/web.php';
$routes = file_exists($routeFile) ? file_get_contents($routeFile) : '';
if (strpos($routes, "name('tas.index')") === false) {
    exit("ERROR: fitur tas belanja belum terpasang. Jalankan dulu: php kembali.php (atau php fungsi.php), lalu ulangi perintah ini.\n");
}

function tulis(string $path, string $isi): void
{
    $full = __DIR__ . '/' . $path;
    if (!is_dir(dirname($full))) {
        mkdir(dirname($full), 0777, true);
    }
    file_put_contents($full, $isi);
    echo "OK  $path\n";
}

/* ---------- Route newsletter (ditambahkan sekali) ---------- */
if (strpos($routes, "name('newsletter')") === false) {
    $routes .= <<<'EOT'


// Newsletter (form di beranda dan footer)
Route::post('/newsletter', function (\Illuminate\Http\Request $request) {
    $request->validate(['email_newsletter' => 'required|email'], [
        'email_newsletter.required' => 'Email untuk newsletter wajib diisi.',
        'email_newsletter.email'    => 'Format email newsletter tidak valid.',
    ]);
    return back()->with('pesan', 'Terima kasih! Kamu sudah berlangganan newsletter kami.');
})->name('newsletter');
EOT;
    file_put_contents($routeFile, $routes);
    echo "OK  routes/web.php (route newsletter ditambahkan)\n";
} else {
    echo "OK  routes/web.php (route newsletter sudah ada)\n";
}

/* =====================================================================
 *  CONTROLLER PRODUK
 * ===================================================================== */
tulis('app/Http/Controllers/ProdukController.php', <<<'EOT'
<?php

namespace App\Http\Controllers;

use App\Models\Ecommerce;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function beranda()
    {
        $baru = Ecommerce::orderByDesc('id')->take(8)->get();

        $kategoriList = collect(['Kaos', 'Kemeja', 'Jaket', 'Celana', 'Jeans', 'Knitwear'])
            ->map(fn ($k) => [
                'nama'   => $k,
                'produk' => Ecommerce::where('category', $k)->first(),
                'jumlah' => Ecommerce::where('category', $k)->count(),
            ])
            ->filter(fn ($t) => $t['produk'] !== null);

        return view('beranda', compact('baru', 'kategoriList'));
    }

    public function index(Request $request)
    {
        $query = Ecommerce::query();

        $kategori = $request->query('kategori');
        $cari     = $request->query('q');
        $urut     = $request->query('urut');

        if ($kategori) {
            $query->where('category', $kategori);
        }
        if ($cari) {
            $query->where('name', 'like', '%' . $cari . '%');
        }

        if ($urut === 'termurah') {
            $query->orderBy('price');
        } elseif ($urut === 'termahal') {
            $query->orderByDesc('price');
        } else {
            $query->orderBy('id');
        }

        $produk = $query->get();

        return view('produk.index', compact('produk', 'kategori', 'cari', 'urut'));
    }

    public function show($id)
    {
        $produk = Ecommerce::findOrFail($id);
        $serupa = Ecommerce::where('category', $produk->category)
            ->where('id', '!=', $produk->id)
            ->take(4)
            ->get();

        return view('produk.show', compact('produk', 'serupa'));
    }
}
EOT);

/* =====================================================================
 *  LAYOUT
 * ===================================================================== */
tulis('resources/views/layouts/app.blade.php', <<<'EOT'
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
        :root {
            --hitam: #111111;
            --emas: #c9a227;
            --emas-tua: #a8861a;
            --krem: #faf6ee;
            --garis: #e6dcc6;
        }
        html { font-size: 17px; }
        body { background: #fff; color: var(--hitam); font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif; line-height: 1.65; }
        a { color: inherit; text-decoration: none; }
        h1, h2, h3, h4, h5 { font-family: 'Playfair Display', Georgia, serif; }
        ::selection { background: var(--emas); color: #000; }

        /* ---------- Promo berjalan ---------- */
        .promo-marquee { background: var(--hitam); color: var(--emas); overflow: hidden; font-size: .82rem; letter-spacing: 2px; font-weight: 500; }
        .marquee-isi { display: inline-flex; white-space: nowrap; padding: 9px 0; animation: geser 32s linear infinite; }
        .marquee-isi span { margin-right: 2.4rem; }
        .promo-marquee:hover .marquee-isi { animation-play-state: paused; }
        @keyframes geser { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        /* ---------- Header ---------- */
        .hdr { z-index: 1030; box-shadow: 0 2px 14px rgba(0,0,0,.07); }
        .logo { font-family: 'Playfair Display', Georgia, serif; font-weight: 900; font-size: 2.1rem; letter-spacing: 5px;
                text-transform: uppercase; color: var(--hitam); line-height: 1; }
        .logo span { color: var(--emas); }
        .logo:hover { color: var(--hitam); }
        .hdr .nav-link { color: var(--hitam); font-size: .92rem; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 500;
                         padding: 1.3rem .9rem; border-bottom: 3px solid transparent; }
        .hdr .nav-link:hover, .hdr .nav-link.active { border-bottom-color: var(--emas); color: var(--hitam); }
        .ikon { color: var(--hitam); display: inline-flex; position: relative; margin-left: 18px; background: none; border: 0; padding: 0; transition: color .2s, transform .2s; }
        .ikon:hover { color: var(--emas-tua); transform: translateY(-2px); }
        .ikon svg { width: 27px; height: 27px; fill: none; stroke: currentColor; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
        .badge-tas { position: absolute; top: -8px; right: -10px; background: var(--emas); color: #000; font-size: .68rem; font-weight: 700; border-radius: 50%; min-width: 19px; height: 19px; display: flex; align-items: center; justify-content: center; }
        .cari-bar input { font-size: 1.1rem; padding: 14px 20px; border: 2px solid var(--hitam); border-radius: 0; }
        .cari-bar input:focus { box-shadow: none; border-color: var(--emas); }

        /* ---------- Tombol & form ---------- */
        .btn { font-family: 'Poppins', sans-serif; }
        .btn-mono { background: var(--hitam); color: #fff; border: 2px solid var(--hitam); border-radius: 0; text-transform: uppercase; letter-spacing: 1.5px; font-size: .9rem; font-weight: 600; padding: 12px 28px; transition: all .2s; }
        .btn-mono:hover { background: var(--emas); border-color: var(--emas); color: #000; }
        .btn-emas { background: var(--emas); color: #000; border: 2px solid var(--emas); border-radius: 0; text-transform: uppercase; letter-spacing: 1.5px; font-size: .9rem; font-weight: 700; padding: 13px 30px; transition: all .2s; }
        .btn-emas:hover { background: #fff; border-color: #fff; color: #000; transform: translateY(-2px); }
        .btn-garis { background: transparent; color: #fff; border: 2px solid #fff; border-radius: 0; text-transform: uppercase; letter-spacing: 1.5px; font-size: .9rem; font-weight: 600; padding: 13px 30px; transition: all .2s; }
        .btn-garis:hover { background: #fff; color: #000; }
        .btn-putih { background: #fff; color: #000; border: 2px solid #fff; border-radius: 0; text-transform: uppercase; letter-spacing: 1.5px; font-size: .9rem; font-weight: 700; padding: 12px 28px; }
        .btn-putih:hover { background: transparent; color: #fff; }
        .btn-outline-mono { border: 2px solid var(--hitam); color: var(--hitam); border-radius: 0; text-transform: uppercase; letter-spacing: 1.5px; font-size: .9rem; font-weight: 600; background: #fff; min-width: 52px; transition: all .2s; }
        .btn-outline-mono:hover { background: var(--hitam); color: #fff; }
        .btn-check:checked + .btn-outline-mono { background: var(--hitam); color: var(--emas); border-color: var(--hitam); }
        .form-control, .form-select { border-radius: 0; border: 1.5px solid #aaa; padding: .65rem .9rem; font-size: 1rem; }
        .form-control:focus, .form-select:focus { box-shadow: none; border-color: var(--emas); }
        .form-label { font-weight: 600; font-size: .9rem; }

        /* ---------- Judul seksi ---------- */
        .judul-bagian { margin-bottom: 2.2rem; text-align: center; }
        .judul-bagian span { display: block; color: var(--emas-tua); letter-spacing: 5px; font-size: .82rem; font-weight: 600; text-transform: uppercase; margin-bottom: 6px; }
        .judul-bagian h2 { font-size: 2.4rem; font-weight: 700; margin: 0; position: relative; display: inline-block; padding-bottom: 16px; }
        .judul-bagian h2::after { content: ''; position: absolute; left: 50%; bottom: 0; width: 64px; height: 4px; background: var(--emas); transform: translateX(-50%); }
        .seksi-judul { font-family: 'Playfair Display', Georgia, serif; font-weight: 700; letter-spacing: 1px; font-size: 1.5rem; text-transform: uppercase; }
        .lihat-semua { font-size: .9rem; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 2px solid var(--emas); padding-bottom: 2px; font-weight: 600; }
        .lihat-semua:hover { color: var(--emas-tua); }

        /* ---------- Hero carousel ---------- */
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

        /* ---------- Strip keunggulan ---------- */
        .keunggulan { background: var(--krem); border-top: 1px solid var(--garis); border-bottom: 1px solid var(--garis); }
        .keunggulan .item { display: flex; align-items: center; gap: 14px; padding: 22px 8px; }
        .keunggulan .bulat { flex: none; width: 54px; height: 54px; border-radius: 50%; background: var(--hitam); color: var(--emas); display: flex; align-items: center; justify-content: center; }
        .keunggulan svg { width: 27px; height: 27px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
        .keunggulan b { display: block; font-size: 1rem; }
        .keunggulan small { color: #666; font-size: .85rem; }

        /* ---------- Kategori bulat ---------- */
        .kat-bulat { display: block; text-align: center; }
        .kat-bulat .foto { width: 150px; height: 150px; margin: 0 auto 14px; border-radius: 50%; overflow: hidden; border: 4px solid #fff; box-shadow: 0 0 0 3px var(--garis); transition: box-shadow .25s, transform .25s; background: #e5e5e5; }
        .kat-bulat .foto img { width: 100%; height: 100%; object-fit: cover; object-position: center 20%; }
        .kat-bulat:hover .foto { box-shadow: 0 0 0 4px var(--emas); transform: translateY(-5px); }
        .kat-bulat b { display: block; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 1.5px; }
        .kat-bulat small { color: #777; }

        /* ---------- Kartu produk ---------- */
        .kartu-produk .gambar-wrap { position: relative; overflow: hidden; background: #ececec; }
        .kartu-produk .gambar { display: block; aspect-ratio: 3 / 4; }
        .kartu-produk .gambar img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s; }
        .kartu-produk:hover .gambar img { transform: scale(1.07); }
        .kartu-cta { position: absolute; left: 0; right: 0; bottom: 0; background: var(--hitam); color: #fff; text-align: center; text-transform: uppercase; letter-spacing: 2px; font-size: .85rem; font-weight: 600; padding: 13px; transform: translateY(101%); transition: transform .3s; }
        .kartu-cta:hover { background: var(--emas); color: #000; }
        .kartu-produk:hover .kartu-cta { transform: translateY(0); }
        .badge-baru { position: absolute; top: 12px; left: 12px; z-index: 2; background: var(--emas); color: #000; font-weight: 700; font-size: .75rem; letter-spacing: 2px; padding: 4px 12px; }
        .kartu-produk .info { padding-top: 14px; }
        .kartu-produk .kat { font-size: .75rem; color: var(--emas-tua); text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }
        .kartu-produk .nama { display: block; font-size: 1.08rem; font-weight: 500; margin: 2px 0 4px; }
        .kartu-produk .nama:hover { color: var(--emas-tua); }
        .kartu-produk .harga { font-size: 1.2rem; font-weight: 700; }
        .btn-fav { position: absolute; top: 10px; right: 10px; z-index: 2; margin: 0; }
        .btn-fav button { background: #fff; border: 0; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,.25); padding: 0; transition: transform .2s; }
        .btn-fav button:hover { transform: scale(1.12); }
        .btn-fav svg { width: 21px; height: 21px; fill: none; stroke: #000; stroke-width: 1.8; stroke-linejoin: round; }
        .btn-fav svg.aktif { fill: #d62839; stroke: #d62839; }

        /* ---------- Banner member ---------- */
        .banner-member { background: linear-gradient(120deg, #0b0b0b 0%, #1d1608 100%); color: #fff; border-top: 5px solid var(--emas); border-bottom: 5px solid var(--emas); position: relative; overflow: hidden; }
        .banner-member .angka { position: absolute; right: 4%; top: 50%; transform: translateY(-50%); font-family: 'Playfair Display', serif; font-weight: 900; font-size: clamp(7rem, 17vw, 15rem); color: var(--emas); opacity: .9; line-height: 1; }
        .banner-member h2 { font-size: 2.8rem; font-weight: 900; }
        .banner-member h2 em { color: var(--emas); }

        /* ---------- Testimoni ---------- */
        .testi { background: var(--krem); }
        .testi .kartu-testi { background: #fff; border: 1px solid var(--garis); padding: 28px; height: 100%; position: relative; }
        .testi .kartu-testi::before { content: '\201C'; position: absolute; top: -8px; left: 20px; font-family: 'Playfair Display', serif; font-size: 5rem; color: var(--emas); line-height: 1; }
        .testi .bintang { color: var(--emas); letter-spacing: 3px; font-size: 1.15rem; margin-top: 14px; }

        /* ---------- Newsletter ---------- */
        .newsletter { background: var(--emas); color: #000; }
        .newsletter h3 { font-size: 2.1rem; font-weight: 900; }
        .newsletter input { border: 2px solid #000; background: #fff; }

        /* ---------- Judul halaman ---------- */
        .hero-halaman { background: var(--hitam); color: #fff; border-bottom: 5px solid var(--emas); }
        .hero-halaman h1 { font-size: 2.8rem; font-weight: 900; }
        .hero-halaman .breadcrumb { --bs-breadcrumb-divider-color: #999; --bs-breadcrumb-item-active-color: var(--emas); margin-bottom: 6px; font-size: .9rem; }
        .hero-halaman .breadcrumb a { color: #ddd; }

        .pill { display: inline-block; border: 2px solid var(--hitam); padding: 7px 20px; margin: 0 8px 10px 0; font-size: .9rem; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; transition: all .2s; }
        .pill.aktif, .pill:hover { background: var(--hitam); color: var(--emas); }
        .accordion-button { font-size: .95rem; }
        .accordion-button:not(.collapsed) { background: var(--krem); color: #000; box-shadow: none; }
        .accordion-button:focus { box-shadow: none; }
        .kotak { border: 1px solid var(--garis); padding: 28px; background: #fff; box-shadow: 0 4px 18px rgba(0,0,0,.04); }
        .info-mini { list-style: none; padding: 0; margin: 0 0 1.5rem; }
        .info-mini li { padding: 6px 0; font-size: .98rem; }
        .info-mini li::before { content: '\2713'; color: var(--emas-tua); font-weight: 700; margin-right: 10px; }
        .harga-besar { font-size: 2rem; font-weight: 700; }

        .pesan-flash { background: var(--hitam); color: #fff; border-left: 6px solid var(--emas); }
        .pesan-flash a { color: var(--emas); }

        /* ---------- Footer ---------- */
        footer { background: var(--hitam); color: #bbb; font-size: .98rem; }
        footer h6 { color: var(--emas); letter-spacing: 3px; font-weight: 600; font-size: .9rem; margin-bottom: 18px; text-transform: uppercase; font-family: 'Poppins', sans-serif; }
        footer a { display: block; padding: 4px 0; color: #bbb; transition: color .2s, padding-left .2s; }
        footer a:hover { color: var(--emas); padding-left: 5px; }
        footer .logo { color: #fff; }
        .sosmed a { display: inline-flex; width: 44px; height: 44px; border: 1.5px solid #555; border-radius: 50%; align-items: center; justify-content: center; margin-right: 10px; color: #fff; font-weight: 600; font-size: .85rem; padding: 0; }
        .sosmed a:hover { background: var(--emas); border-color: var(--emas); color: #000; padding-left: 0; }
    </style>
</head>
<body>

@php
    $menu = ['Kaos', 'Kemeja', 'Jaket', 'Celana', 'Jeans', 'Knitwear'];
    $jumlahTas = collect(session('keranjang', []))->sum('qty');
    $jumlahFav = count(session('favorit', []));
@endphp

<div class="promo-marquee">
    <div class="marquee-isi">
        @for ($i = 0; $i < 2; $i++)
            <span>GRATIS ONGKIR MINIMAL BELANJA RP 499.000</span><span>&#9670;</span>
            <span>DISKON 10% UNTUK MEMBER BARU</span><span>&#9670;</span>
            <span>RETUR MUDAH 14 HARI</span><span>&#9670;</span>
            <span>KOLEKSI BARU SETIAP MINGGU</span><span>&#9670;</span>
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
                <a href="{{ auth()->check() ? route('akun') : route('login') }}" class="ikon" title="{{ auth()->check() ? 'Akun Saya' : 'Masuk' }}">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
                </a>
                <a href="{{ route('favorit.index') }}" class="ikon" title="Favorit">
                    <svg viewBox="0 0 24 24"><path d="M12 21s-8-5.5-8-11a4.5 4.5 0 0 1 8-2.5A4.5 4.5 0 0 1 20 10c0 5.5-8 11-8 11z"/></svg>
                    @if ($jumlahFav > 0) <span class="badge-tas">{{ $jumlahFav }}</span> @endif
                </a>
                <a href="{{ route('tas.index') }}" class="ikon" title="Tas Belanja">
                    <svg viewBox="0 0 24 24"><path d="M5 8h14l-1 13H6L5 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                    <span class="badge-tas">{{ $jumlahTas }}</span>
                </a>
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

@if (session('pesan'))
    <div class="container mt-3">
        <div class="alert pesan-flash rounded-0 d-flex justify-content-between align-items-center mb-0 py-3" role="alert">
            <span>{{ session('pesan') }}</span>
            @if (session('pesan_link'))
                <a href="{{ session('pesan_link') }}" class="fw-bold text-decoration-underline ms-3">{{ session('pesan_link_label', 'Lihat') }}</a>
            @endif
        </div>
    </div>
@endif
@if ($errors->has('email_newsletter'))
    <div class="container mt-3">
        <div class="alert alert-danger rounded-0 mb-0 py-2">{{ $errors->first('email_newsletter') }}</div>
    </div>
@endif

<main>
    @yield('content')
</main>

<footer class="mt-5 pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="logo fs-3 mb-3">{{ config('app.name') }}</div>
                <p>Fashion simpel, nyaman, dan berkelas untuk keseharianmu. Koleksi baju dan celana pilihan dengan harga bersahabat.</p>
                <div class="sosmed mt-3">
                    <a href="https://www.instagram.com" target="_blank" rel="noopener" title="Instagram">IG</a>
                    <a href="https://www.tiktok.com" target="_blank" rel="noopener" title="TikTok">TT</a>
                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" title="WhatsApp">WA</a>
                </div>
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
                <a href="{{ auth()->check() ? route('akun') : route('login') }}">Akun Saya</a>
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
EOT);

/* =====================================================================
 *  KARTU PRODUK
 * ===================================================================== */
tulis('resources/views/partials/kartu.blade.php', <<<'EOT'
@php
    $fav  = in_array($p->id, session('favorit', []));
    $baru = $p->id >= 9;
@endphp
<div class="kartu-produk">
    <div class="gambar-wrap">
        @if ($baru) <span class="badge-baru">BARU</span> @endif

        <form action="{{ route('favorit.toggle', $p->id) }}" method="POST" class="btn-fav">
            @csrf
            <button type="submit" title="{{ $fav ? 'Hapus dari favorit' : 'Simpan ke favorit' }}">
                <svg viewBox="0 0 24 24" class="{{ $fav ? 'aktif' : '' }}"><path d="M12 21s-8-5.5-8-11a4.5 4.5 0 0 1 8-2.5A4.5 4.5 0 0 1 20 10c0 5.5-8 11-8 11z"/></svg>
            </button>
        </form>

        <a href="{{ route('produk.show', $p->id) }}" class="gambar">
            <img src="{{ $p->gambar_url }}" alt="{{ $p->name }}" loading="lazy">
        </a>
        <a href="{{ route('produk.show', $p->id) }}" class="kartu-cta">Lihat Detail</a>
    </div>
    <div class="info">
        <div class="kat">{{ $p->category }}</div>
        <a href="{{ route('produk.show', $p->id) }}" class="nama">{{ $p->name }}</a>
        <div class="harga">Rp {{ number_format($p->price, 0, ',', '.') }}</div>
    </div>
</div>
EOT);

/* =====================================================================
 *  BERANDA
 * ===================================================================== */
tulis('resources/views/beranda.blade.php', <<<'EOT'
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
EOT);

/* =====================================================================
 *  DAFTAR PRODUK
 * ===================================================================== */
tulis('resources/views/produk/index.blade.php', <<<'EOT'
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
EOT);

/* =====================================================================
 *  DETAIL PRODUK
 * ===================================================================== */
tulis('resources/views/produk/show.blade.php', <<<'EOT'
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
EOT);

/* ---------- Bersihkan cache ---------- */
echo "\nMembersihkan cache...\n";
passthru('"' . PHP_BINARY . '" artisan route:clear');
passthru('"' . PHP_BINARY . '" artisan view:clear');
passthru('"' . PHP_BINARY . '" artisan config:clear');

echo "\nSELESAI. Refresh browser (Ctrl+F5). Kalau server belum jalan: php artisan serve\n";
