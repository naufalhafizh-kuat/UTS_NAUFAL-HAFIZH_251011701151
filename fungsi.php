<?php
/*
 * fungsi.php - Jalankan dari root project Laravel:  php fungsi.php
 * Membuat semua tombol berfungsi:
 *  - Tas belanja (tambah, ubah jumlah, hapus, kosongkan) + checkout + halaman sukses
 *  - Favorit (simpan/hapus dari kartu produk dan halaman detail)
 *  - Daftar, masuk, keluar, halaman akun (pakai tabel users bawaan Laravel)
 *  - Halaman info (Customer Service, Lokasi Toko, Unduh Aplikasi, dll) + form kontak
 * TIDAK mengubah database, jadi data produk dan akun tidak terhapus.
 */

if (!file_exists(__DIR__ . '/artisan')) {
    exit("ERROR: jalankan dari dalam folder project Laravel (yang ada file 'artisan').\n");
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

/* =====================================================================
 *  ROUTES
 * ===================================================================== */
tulis('routes/web.php', <<<'EOT'
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoritController;
use App\Http\Controllers\HalamanController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;

// Toko
Route::get('/', [ProdukController::class, 'beranda'])->name('beranda');
Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
Route::get('/produk/{id}', [ProdukController::class, 'show'])->name('produk.show');

// Tas belanja & checkout
Route::get('/tas', [KeranjangController::class, 'index'])->name('tas.index');
Route::post('/tas/tambah/{id}', [KeranjangController::class, 'tambah'])->name('tas.tambah');
Route::patch('/tas/{key}', [KeranjangController::class, 'ubah'])->name('tas.ubah');
Route::delete('/tas/{key}', [KeranjangController::class, 'hapus'])->name('tas.hapus');
Route::delete('/tas', [KeranjangController::class, 'kosongkan'])->name('tas.kosongkan');
Route::get('/checkout', [KeranjangController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [KeranjangController::class, 'proses'])->name('checkout.proses');
Route::get('/checkout/sukses', [KeranjangController::class, 'sukses'])->name('checkout.sukses');

// Favorit
Route::get('/favorit', [FavoritController::class, 'index'])->name('favorit.index');
Route::post('/favorit/{id}', [FavoritController::class, 'toggle'])->name('favorit.toggle');

// Halaman info & kontak
Route::get('/halaman/{slug}', [HalamanController::class, 'tampil'])->name('halaman');
Route::post('/kontak', [HalamanController::class, 'kirimKontak'])->name('kontak.kirim');

// Akun
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'formMasuk'])->name('login');
    Route::post('/masuk', [AuthController::class, 'masuk'])->name('masuk.proses');
    Route::get('/daftar', [AuthController::class, 'formDaftar'])->name('daftar');
    Route::post('/daftar', [AuthController::class, 'daftar'])->name('daftar.proses');
});
Route::middleware('auth')->group(function () {
    Route::get('/akun', [AuthController::class, 'akun'])->name('akun');
    Route::post('/keluar', [AuthController::class, 'keluar'])->name('keluar');
});
EOT);

/* =====================================================================
 *  CONTROLLERS
 * ===================================================================== */
tulis('app/Http/Controllers/KeranjangController.php', <<<'EOT'
<?php

namespace App\Http\Controllers;

use App\Models\Ecommerce;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KeranjangController extends Controller
{
    private const GRATIS_ONGKIR = 499000;
    private const ONGKIR = 20000;
    private const PEMBAYARAN = ['Transfer Bank', 'COD (Bayar di Tempat)', 'E-Wallet'];

    private function ambilItem(): array
    {
        $tas = session('keranjang', []);
        $produk = Ecommerce::whereIn('id', collect($tas)->pluck('id')->all())->get()->keyBy('id');

        $items = [];
        foreach ($tas as $key => $row) {
            if (!isset($produk[$row['id']])) {
                continue;
            }
            $p = $produk[$row['id']];
            $items[] = [
                'key'      => $key,
                'produk'   => $p,
                'ukuran'   => $row['ukuran'],
                'qty'      => $row['qty'],
                'subtotal' => $p->price * $row['qty'],
            ];
        }
        return $items;
    }

    private function hitung(array $items): array
    {
        $subtotal = (int) collect($items)->sum('subtotal');
        $ongkir   = ($subtotal === 0 || $subtotal >= self::GRATIS_ONGKIR) ? 0 : self::ONGKIR;
        return [$subtotal, $ongkir, $subtotal + $ongkir];
    }

    public function index()
    {
        $items = $this->ambilItem();
        [$subtotal, $ongkir, $total] = $this->hitung($items);
        $kurang = max(0, self::GRATIS_ONGKIR - $subtotal);

        return view('tas', compact('items', 'subtotal', 'ongkir', 'total', 'kurang'));
    }

    public function tambah(Request $request, $id)
    {
        $produk = Ecommerce::findOrFail($id);

        $ukuran = $request->input('ukuran', 'M');
        if (!in_array($ukuran, ['S', 'M', 'L', 'XL'])) {
            $ukuran = 'M';
        }

        $tas = session('keranjang', []);
        $key = $produk->id . '-' . $ukuran;

        if (isset($tas[$key])) {
            $tas[$key]['qty'] = min(20, $tas[$key]['qty'] + 1);
        } else {
            $tas[$key] = ['id' => $produk->id, 'ukuran' => $ukuran, 'qty' => 1];
        }
        session(['keranjang' => $tas]);

        return back()
            ->with('pesan', $produk->name . ' (ukuran ' . $ukuran . ') ditambahkan ke tas.')
            ->with('pesan_link', route('tas.index'))
            ->with('pesan_link_label', 'Lihat tas');
    }

    public function ubah(Request $request, $key)
    {
        $tas = session('keranjang', []);
        if (isset($tas[$key])) {
            $qty = $tas[$key]['qty'] + ($request->input('aksi') === 'kurang' ? -1 : 1);
            if ($qty < 1) {
                unset($tas[$key]);
            } else {
                $tas[$key]['qty'] = min(20, $qty);
            }
            session(['keranjang' => $tas]);
        }
        return redirect()->route('tas.index');
    }

    public function hapus($key)
    {
        $tas = session('keranjang', []);
        unset($tas[$key]);
        session(['keranjang' => $tas]);
        return redirect()->route('tas.index')->with('pesan', 'Produk dihapus dari tas.');
    }

    public function kosongkan()
    {
        session()->forget('keranjang');
        return redirect()->route('tas.index')->with('pesan', 'Tas belanja dikosongkan.');
    }

    public function checkout()
    {
        $items = $this->ambilItem();
        if (!$items) {
            return redirect()->route('tas.index')->with('pesan', 'Tas belanjamu masih kosong.');
        }
        [$subtotal, $ongkir, $total] = $this->hitung($items);
        $pembayaran = self::PEMBAYARAN;

        return view('checkout', compact('items', 'subtotal', 'ongkir', 'total', 'pembayaran'));
    }

    public function proses(Request $request)
    {
        $items = $this->ambilItem();
        if (!$items) {
            return redirect()->route('tas.index')->with('pesan', 'Tas belanjamu masih kosong.');
        }

        $data = $request->validate([
            'nama'       => 'required|string|max:100',
            'telepon'    => 'required|string|max:20',
            'alamat'     => 'required|string|max:255',
            'pembayaran' => ['required', Rule::in(self::PEMBAYARAN)],
        ], [
            'nama.required'       => 'Nama penerima wajib diisi.',
            'telepon.required'    => 'Nomor telepon wajib diisi.',
            'alamat.required'     => 'Alamat pengiriman wajib diisi.',
            'pembayaran.required' => 'Pilih metode pembayaran.',
            'pembayaran.in'       => 'Metode pembayaran tidak valid.',
        ]);

        [$subtotal, $ongkir, $total] = $this->hitung($items);

        $pesanan = [
            'kode'     => 'LXC-' . date('ymd') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 5)),
            'tanggal'  => now()->format('d/m/Y H:i'),
            'data'     => $data,
            'items'    => collect($items)->map(fn ($i) => [
                'nama'     => $i['produk']->name,
                'ukuran'   => $i['ukuran'],
                'qty'      => $i['qty'],
                'subtotal' => $i['subtotal'],
            ])->all(),
            'subtotal' => $subtotal,
            'ongkir'   => $ongkir,
            'total'    => $total,
        ];

        session(['pesanan_terakhir' => $pesanan]);
        session()->forget('keranjang');

        return redirect()->route('checkout.sukses');
    }

    public function sukses()
    {
        $pesanan = session('pesanan_terakhir');
        if (!$pesanan) {
            return redirect()->route('beranda');
        }
        return view('sukses', compact('pesanan'));
    }
}
EOT);

tulis('app/Http/Controllers/FavoritController.php', <<<'EOT'
<?php

namespace App\Http\Controllers;

use App\Models\Ecommerce;

class FavoritController extends Controller
{
    public function index()
    {
        $ids = session('favorit', []);
        $produk = Ecommerce::whereIn('id', $ids)->get();
        return view('favorit', compact('produk'));
    }

    public function toggle($id)
    {
        $produk = Ecommerce::findOrFail($id);
        $id = (int) $produk->id;

        $favorit = session('favorit', []);
        if (in_array($id, $favorit)) {
            $favorit = array_values(array_diff($favorit, [$id]));
            $pesan = $produk->name . ' dihapus dari favorit.';
        } else {
            $favorit[] = $id;
            $pesan = $produk->name . ' disimpan ke favorit.';
        }
        session(['favorit' => $favorit]);

        return back()->with('pesan', $pesan);
    }
}
EOT);

tulis('app/Http/Controllers/HalamanController.php', <<<'EOT'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HalamanController extends Controller
{
    private function daftar(): array
    {
        $nama = config('app.name');

        $customerService = [
            'judul' => 'Customer Service',
            'isi'   => [
                'Ada pertanyaan tentang pesanan, ukuran, atau pengembalian? Tim kami siap membantu setiap hari pukul 09.00 - 21.00 WIB.',
                'Kirim pesanmu lewat formulir di bawah ini dan kami akan membalas lewat email.',
            ],
            'form'  => true,
        ];

        return [
            'customer-service' => $customerService,
            'kontak'           => array_merge($customerService, ['judul' => 'Kontak']),
            'lokasi-toko'      => [
                'judul'  => 'Lokasi Toko',
                'isi'    => ['Kunjungi toko ' . $nama . ' terdekat. Jam buka setiap hari 10.00 - 22.00 WIB.'],
                'daftar' => [
                    $nama . ' Jakarta - Mall Kota Kasablanka, Lt. 2, Jakarta Selatan',
                    $nama . ' Tangerang Selatan - Pamulang Square, Lt. 1, Tangerang Selatan',
                    $nama . ' Bandung - Paris Van Java, Lt. 1, Bandung',
                    $nama . ' Surabaya - Tunjungan Plaza, Lt. 3, Surabaya',
                ],
            ],
            'unduh-aplikasi'   => [
                'judul'  => 'Unduh Aplikasi',
                'isi'    => ['Aplikasi ' . $nama . ' akan segera hadir di App Store dan Google Play. Sementara itu, kamu bisa berbelanja langsung lewat website ini.'],
            ],
            'tentang'          => [
                'judul' => 'Tentang ' . $nama,
                'isi'   => [
                    $nama . ' adalah toko fashion online dengan koleksi baju dan celana bergaya simpel dan monokrom untuk keseharian.',
                    'Kami percaya pakaian yang baik tidak harus mahal: bahan nyaman, potongan rapi, dan mudah dipadukan.',
                ],
            ],
            'karir'            => [
                'judul' => 'Berkarir',
                'isi'   => ['Saat ini belum ada lowongan terbuka. Pantau terus halaman ini untuk informasi terbaru dari tim ' . $nama . '.'],
            ],
            'keberlanjutan'    => [
                'judul' => 'Keberlanjutan',
                'isi'   => [
                    'Kami berkomitmen memakai kemasan ramah lingkungan dan memilih bahan yang lebih bertanggung jawab secara bertahap.',
                ],
            ],
            'pers'             => [
                'judul' => 'Pers',
                'isi'   => ['Untuk kebutuhan media dan kerja sama, hubungi kami lewat halaman Kontak.'],
            ],
            'kebijakan-privasi' => [
                'judul' => 'Kebijakan Privasi',
                'isi'   => [
                    'Kami hanya menyimpan data yang diperlukan untuk memproses pesanan dan akunmu, dan tidak membagikannya kepada pihak ketiga tanpa persetujuanmu.',
                    'Kamu dapat meminta penghapusan datamu kapan saja lewat halaman Kontak.',
                ],
            ],
        ];
    }

    public function tampil($slug)
    {
        $halaman = $this->daftar()[$slug] ?? abort(404);
        return view('halaman', compact('halaman'));
    }

    public function kirimKontak(Request $request)
    {
        $request->validate([
            'nama'  => 'required|string|max:100',
            'email' => 'required|email',
            'pesan' => 'required|string|min:10|max:1000',
        ], [
            'nama.required'  => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'pesan.required' => 'Pesan wajib diisi.',
            'pesan.min'      => 'Pesan minimal 10 karakter.',
        ]);

        return back()->with('pesan', 'Terima kasih, pesanmu sudah terkirim. Kami akan membalas lewat email.');
    }
}
EOT);

tulis('app/Http/Controllers/AuthController.php', <<<'EOT'
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function formMasuk()
    {
        return view('auth.masuk');
    }

    public function masuk(Request $request)
    {
        $kredensial = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($kredensial, $request->boolean('ingat'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('akun'))
                ->with('pesan', 'Selamat datang kembali, ' . Auth::user()->name . '.');
        }

        return back()->withErrors(['email' => 'Email atau kata sandi salah.'])->onlyInput('email');
    }

    public function formDaftar()
    {
        return view('auth.daftar');
    }

    public function daftar(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email ini sudah terdaftar.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.min'       => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('akun')
            ->with('pesan', 'Selamat datang di ' . config('app.name') . ', ' . $user->name . '! Akunmu berhasil dibuat.');
    }

    public function akun()
    {
        return view('akun', ['user' => Auth::user()]);
    }

    public function keluar(Request $request)
    {
        Auth::logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')->with('pesan', 'Kamu sudah keluar dari akun.');
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
    <title>@yield('title', 'Belanja Fashion Online') | {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('Bootstrap/css/bootstrap.min.css') }}">

    <style>
        body { background: #fff; color: #111; font-family: 'Helvetica Neue', Arial, sans-serif; }
        a { color: inherit; text-decoration: none; }

        .promo-bar { background: #000; color: #fff; font-size: .78rem; letter-spacing: 1px; text-transform: uppercase; }
        .promo-bar a { color: #fff; text-decoration: underline; margin-left: 10px; font-weight: 700; }
        .util-bar { font-size: .75rem; background: #f5f5f5; }
        .util-bar a { color: #555; }
        .util-bar a:hover { color: #000; text-decoration: underline; }

        .logo { font-family: 'Playfair Display', Georgia, 'Times New Roman', serif; font-weight: 900; font-size: 1.9rem;
                letter-spacing: 4px; text-transform: uppercase; color: #000; line-height: 1; }
        .logo:hover { color: #000; }

        .site-header { z-index: 1030; }
        .site-header .nav-link { color: #111; font-size: .82rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; padding: 1rem .8rem; border-bottom: 2px solid transparent; }
        .site-header .nav-link:hover, .site-header .nav-link.active { border-bottom-color: #000; }
        .search-form input { border-radius: 0; border: 1px solid #ccc; width: 190px; font-size: .85rem; }
        .search-form input:focus { box-shadow: none; border-color: #000; }
        .ikon { color: #111; display: inline-flex; position: relative; margin-left: 14px; }
        .ikon svg { width: 24px; height: 24px; fill: none; stroke: currentColor; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
        .badge-tas { position: absolute; top: -6px; right: -9px; background: #000; color: #fff; font-size: .62rem; border-radius: 50%; min-width: 17px; height: 17px; display: flex; align-items: center; justify-content: center; }

        .banner { position: relative; display: flex; align-items: flex-end; min-height: 260px; padding: 32px; color: #fff;
                  background: #1a1a1a center / cover no-repeat; overflow: hidden; }
        .banner::before { content: ''; position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,.65), rgba(0,0,0,0) 65%); }
        .banner > * { position: relative; }
        .banner-besar { min-height: 536px; background-color: #151515; }
        .banner-b { background-color: #333; }
        .banner-c { background-color: #555; }
        .banner h2 { font-weight: 800; letter-spacing: 2px; text-transform: uppercase; }
        .banner .teks-besar { font-size: 2.6rem; }

        .btn-mono { background: #000; color: #fff; border: 1px solid #000; border-radius: 0; text-transform: uppercase; letter-spacing: 1px; font-size: .8rem; font-weight: 600; padding: 10px 22px; }
        .btn-mono:hover { background: #fff; color: #000; border-color: #000; }
        .btn-putih { background: #fff; color: #000; border: 1px solid #fff; border-radius: 0; text-transform: uppercase; letter-spacing: 1px; font-size: .8rem; font-weight: 700; padding: 10px 22px; }
        .btn-putih:hover { background: transparent; color: #fff; }
        .btn-outline-mono { border: 1px solid #000; color: #000; border-radius: 0; text-transform: uppercase; letter-spacing: 1px; font-size: .82rem; background: #fff; min-width: 44px; }
        .btn-outline-mono:hover { background: #000; color: #fff; }
        .btn-check:checked + .btn-outline-mono { background: #000; color: #fff; }
        .form-control, .form-select { border-radius: 0; border-color: #999; }
        .form-control:focus, .form-select:focus { box-shadow: none; border-color: #000; }

        .seksi-judul { font-weight: 800; letter-spacing: 2px; text-transform: uppercase; font-size: 1.05rem; }
        .lihat-semua { font-size: .78rem; text-transform: uppercase; letter-spacing: 1px; text-decoration: underline; font-weight: 600; }

        .kartu-produk .gambar { aspect-ratio: 3 / 4; background: #ececec; overflow: hidden; }
        .kartu-produk .gambar img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s; }
        .kartu-produk:hover .gambar img { transform: scale(1.04); }
        .kartu-produk .nama { font-size: .85rem; margin: 10px 0 2px; }
        .kartu-produk .harga { font-size: .85rem; font-weight: 700; }
        .kartu-produk .kat { font-size: .7rem; color: #777; text-transform: uppercase; letter-spacing: 1px; }
        .btn-fav { position: absolute; top: 8px; right: 8px; z-index: 2; margin: 0; }
        .btn-fav button { background: #fff; border: 0; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 4px rgba(0,0,0,.25); padding: 0; }
        .btn-fav svg { width: 18px; height: 18px; fill: none; stroke: #000; stroke-width: 1.8; stroke-linejoin: round; }
        .btn-fav svg.aktif { fill: #000; }

        .tile { position: relative; display: block; aspect-ratio: 3 / 4; overflow: hidden; background: #ddd; }
        .tile img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s; }
        .tile:hover img { transform: scale(1.05); }
        .tile .label { position: absolute; left: 0; right: 0; bottom: 0; padding: 18px; color: #fff; background: linear-gradient(to top, rgba(0,0,0,.7), rgba(0,0,0,0)); }
        .tile .label h4 { font-weight: 800; letter-spacing: 2px; margin: 0; text-transform: uppercase; font-size: 1.1rem; }
        .tile .label span { font-size: .75rem; text-decoration: underline; text-transform: uppercase; letter-spacing: 1px; }

        .member { background: #f2f2f2; }
        .pill { display: inline-block; border: 1px solid #000; padding: 6px 16px; margin: 0 6px 8px 0; font-size: .78rem; text-transform: uppercase; letter-spacing: 1px; }
        .pill.aktif, .pill:hover { background: #000; color: #fff; }
        .accordion-button:not(.collapsed) { background: #f5f5f5; color: #000; box-shadow: none; }
        .accordion-button:focus { box-shadow: none; }
        .kotak { border: 1px solid #ddd; padding: 24px; }

        footer { background: #000; color: #bbb; font-size: .85rem; }
        footer h6 { color: #fff; letter-spacing: 2px; font-weight: 700; font-size: .8rem; margin-bottom: 14px; }
        footer a { color: #bbb; display: block; padding: 3px 0; }
        footer a:hover { color: #fff; }
        footer .logo { color: #fff; }
    </style>
</head>
<body>

@php
    $menu = ['Kaos', 'Kemeja', 'Jaket', 'Celana', 'Jeans', 'Knitwear'];
    $jumlahTas = collect(session('keranjang', []))->sum('qty');
    $jumlahFav = count(session('favorit', []));
@endphp

<div class="promo-bar text-center py-2">
    Gratis ongkir untuk belanja minimal Rp 499.000
    <a href="{{ route('produk.index') }}">Belanja sekarang</a>
</div>

<div class="util-bar d-none d-lg-block">
    <div class="container d-flex justify-content-end gap-4 py-1">
        <a href="{{ route('halaman', 'customer-service') }}">Customer Service</a>
        <a href="{{ route('halaman', 'lokasi-toko') }}">Lokasi Toko</a>
        <a href="{{ route('halaman', 'unduh-aplikasi') }}">Unduh Aplikasi</a>
        @auth
            <a href="{{ route('akun') }}">Halo, {{ Str::before(Auth::user()->name, ' ') }}</a>
        @else
            <a href="{{ route('daftar') }}">Keanggotaan {{ config('app.name') }}</a>
        @endauth
    </div>
</div>

<header class="site-header sticky-top bg-white border-bottom">
    <nav class="navbar navbar-expand-lg py-0">
        <div class="container">
            <a class="logo me-lg-4 py-3" href="{{ route('beranda') }}">{{ config('app.name') }}</a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('produk.index') && !request('kategori') ? 'active' : '' }}" href="{{ route('produk.index') }}">Semua</a>
                    </li>
                    @foreach ($menu as $m)
                        <li class="nav-item">
                            <a class="nav-link {{ request('kategori') === $m ? 'active' : '' }}" href="{{ route('produk.index', ['kategori' => $m]) }}">{{ $m }}</a>
                        </li>
                    @endforeach
                </ul>

                <form class="search-form d-flex align-items-center my-2 my-lg-0" action="{{ route('produk.index') }}" method="GET">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari produk" value="{{ request('q') }}">
                </form>

                <div class="d-flex align-items-center my-2 my-lg-0 ms-lg-2">
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
                </div>
            </div>
        </div>
    </nav>
</header>

@if (session('pesan'))
    <div class="container mt-3">
        <div class="alert alert-dark rounded-0 d-flex justify-content-between align-items-center mb-0 py-2" role="alert">
            <span>{{ session('pesan') }}</span>
            @if (session('pesan_link'))
                <a href="{{ session('pesan_link') }}" class="fw-bold text-decoration-underline ms-3">{{ session('pesan_link_label', 'Lihat') }}</a>
            @endif
        </div>
    </div>
@endif

<main>
    @yield('content')
</main>

<section class="member mt-5 py-5 text-center">
    <div class="container">
        @auth
            <h5 class="seksi-judul">Halo, {{ Auth::user()->name }}</h5>
            <p class="mb-3">Terima kasih sudah menjadi member {{ config('app.name') }}.</p>
            <a href="{{ route('akun') }}" class="btn btn-mono">Akun Saya</a>
        @else
            <h5 class="seksi-judul">Jadilah Member {{ config('app.name') }}</h5>
            <p class="mb-3">Bergabunglah sekarang dan dapatkan diskon 10% untuk pembelian pertamamu.</p>
            <a href="{{ route('daftar') }}" class="btn btn-mono">Bergabung Sekarang</a>
        @endauth
    </div>
</section>

<footer class="pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <h6>BELANJA</h6>
                <a href="{{ route('produk.index') }}">Semua Produk</a>
                @foreach ($menu as $m)
                    <a href="{{ route('produk.index', ['kategori' => $m]) }}">{{ $m }}</a>
                @endforeach
            </div>
            <div class="col-6 col-md-3">
                <h6>INFO PERUSAHAAN</h6>
                <a href="{{ route('halaman', 'tentang') }}">Tentang {{ config('app.name') }}</a>
                <a href="{{ route('halaman', 'karir') }}">Berkarir</a>
                <a href="{{ route('halaman', 'keberlanjutan') }}">Keberlanjutan</a>
                <a href="{{ route('halaman', 'pers') }}">Pers</a>
            </div>
            <div class="col-6 col-md-3">
                <h6>BANTUAN</h6>
                <a href="{{ route('halaman', 'customer-service') }}">Customer Service</a>
                <a href="{{ auth()->check() ? route('akun') : route('login') }}">Akun Saya</a>
                <a href="{{ route('halaman', 'lokasi-toko') }}">Lokasi Toko</a>
                <a href="{{ route('halaman', 'kebijakan-privasi') }}">Kebijakan Privasi</a>
                <a href="{{ route('halaman', 'kontak') }}">Kontak</a>
            </div>
            <div class="col-6 col-md-3">
                <h6>UNDUH APLIKASI</h6>
                <a href="{{ route('halaman', 'unduh-aplikasi') }}">App Store</a>
                <a href="{{ route('halaman', 'unduh-aplikasi') }}">Google Play</a>
                <div class="logo mt-4 fs-4">{{ config('app.name') }}</div>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <p class="small mb-0 text-center">&copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi. Dibuat untuk UTS Rekayasa Web.</p>
    </div>
</footer>

<script src="{{ asset('Bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
EOT);

/* =====================================================================
 *  PARTIALS
 * ===================================================================== */
tulis('resources/views/partials/kartu.blade.php', <<<'EOT'
@php $fav = in_array($p->id, session('favorit', [])); @endphp
<div class="kartu-produk position-relative">
    <form action="{{ route('favorit.toggle', $p->id) }}" method="POST" class="btn-fav">
        @csrf
        <button type="submit" title="{{ $fav ? 'Hapus dari favorit' : 'Simpan ke favorit' }}">
            <svg viewBox="0 0 24 24" class="{{ $fav ? 'aktif' : '' }}"><path d="M12 21s-8-5.5-8-11a4.5 4.5 0 0 1 8-2.5A4.5 4.5 0 0 1 20 10c0 5.5-8 11-8 11z"/></svg>
        </button>
    </form>

    <a href="{{ route('produk.show', $p->id) }}" class="d-block">
        <div class="gambar">
            <img src="{{ $p->gambar_url }}" alt="{{ $p->name }}" loading="lazy">
        </div>
        <div class="kat mt-2">{{ $p->category }}</div>
        <div class="nama">{{ $p->name }}</div>
        <div class="harga">Rp {{ number_format($p->price, 0, ',', '.') }}</div>
    </a>
</div>
EOT);

tulis('resources/views/partials/error.blade.php', <<<'EOT'
@if ($errors->any())
    <div class="alert alert-danger rounded-0 small py-2">
        @foreach ($errors->all() as $e)
            <div>{{ $e }}</div>
        @endforeach
    </div>
@endif
EOT);

/* =====================================================================
 *  DETAIL PRODUK (form tas + favorit)
 * ===================================================================== */
tulis('resources/views/produk/show.blade.php', <<<'EOT'
@extends('layouts.app')
@section('title', $produk->name)

@section('content')
@php $fav = in_array($produk->id, session('favorit', [])); @endphp
<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('produk.index', ['kategori' => $produk->category]) }}">{{ $produk->category }}</a></li>
            <li class="breadcrumb-item active">{{ $produk->name }}</li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-md-6 col-lg-5">
            <div style="aspect-ratio: 3 / 4; background: #ececec;">
                <img src="{{ $produk->gambar_url }}" alt="{{ $produk->name }}" class="w-100 h-100" style="object-fit: cover;">
            </div>
        </div>

        <div class="col-md-6 col-lg-5">
            <div class="text-secondary small text-uppercase" style="letter-spacing: 1px;">{{ $produk->category }}</div>
            <h1 class="fw-bold fs-3 mt-1">{{ $produk->name }}</h1>
            <p class="fs-4 fw-bold">Rp {{ number_format($produk->price, 0, ',', '.') }}</p>
            <p class="text-secondary">{{ $produk->description }}</p>

            <form method="POST" action="{{ route('tas.tambah', $produk->id) }}">
                @csrf
                <div class="mb-3">
                    <div class="small fw-bold text-uppercase mb-2" style="letter-spacing: 1px;">Ukuran</div>
                    @foreach (['S', 'M', 'L', 'XL'] as $u)
                        <input type="radio" class="btn-check" name="ukuran" value="{{ $u }}" id="ukuran{{ $u }}" autocomplete="off" @checked($loop->first)>
                        <label class="btn btn-outline-mono me-1" for="ukuran{{ $u }}">{{ $u }}</label>
                    @endforeach
                </div>
                <div class="d-grid mb-2">
                    <button type="submit" class="btn btn-mono py-3">Tambah ke tas</button>
                </div>
            </form>

            <form method="POST" action="{{ route('favorit.toggle', $produk->id) }}" class="d-grid mb-2">
                @csrf
                <button type="submit" class="btn btn-outline-mono py-2">{{ $fav ? '♥ Hapus dari favorit' : '♡ Simpan ke favorit' }}</button>
            </form>

            <div class="d-grid mb-4">
                <a href="{{ route('produk.index', ['kategori' => $produk->category]) }}" class="btn btn-outline-mono py-2">&larr; Kembali ke {{ $produk->category }}</a>
            </div>

            <div class="accordion accordion-flush border-top" id="info">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed text-uppercase fw-semibold small" type="button" data-bs-toggle="collapse" data-bs-target="#a1">Deskripsi &amp; Detail</button>
                    </h2>
                    <div id="a1" class="accordion-collapse collapse" data-bs-parent="#info">
                        <div class="accordion-body small">{{ $produk->description }}</div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed text-uppercase fw-semibold small" type="button" data-bs-toggle="collapse" data-bs-target="#a2">Pengiriman &amp; Pengembalian</button>
                    </h2>
                    <div id="a2" class="accordion-collapse collapse" data-bs-parent="#info">
                        <div class="accordion-body small">Gratis ongkir untuk belanja minimal Rp 499.000, selain itu ongkir Rp 20.000. Pengembalian barang maksimal 14 hari setelah diterima.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($serupa->count())
        <section class="mt-5">
            <h3 class="seksi-judul mb-3">Mungkin Kamu Suka</h3>
            <div class="row g-3">
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

/* =====================================================================
 *  TAS BELANJA
 * ===================================================================== */
tulis('resources/views/tas.blade.php', <<<'EOT'
@extends('layouts.app')
@section('title', 'Tas Belanja')

@section('content')
<div class="container mt-4">
    <h1 class="seksi-judul fs-3 mb-4">Tas Belanja</h1>

    @if (count($items))
        <div class="row g-4">
            <div class="col-lg-8">
                @foreach ($items as $i)
                    <div class="d-flex gap-3 py-3 border-bottom">
                        <a href="{{ route('produk.show', $i['produk']->id) }}" style="width: 110px; flex: none;">
                            <div style="aspect-ratio: 3 / 4; background: #ececec;">
                                <img src="{{ $i['produk']->gambar_url }}" class="w-100 h-100" style="object-fit: cover;" alt="{{ $i['produk']->name }}">
                            </div>
                        </a>
                        <div class="flex-fill">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('produk.show', $i['produk']->id) }}" class="fw-semibold">{{ $i['produk']->name }}</a>
                                    <div class="small text-secondary">{{ $i['produk']->category }} &middot; Ukuran {{ $i['ukuran'] }}</div>
                                    <div class="small">Rp {{ number_format($i['produk']->price, 0, ',', '.') }}</div>
                                </div>
                                <div class="fw-bold">Rp {{ number_format($i['subtotal'], 0, ',', '.') }}</div>
                            </div>

                            <div class="d-flex align-items-center gap-2 mt-3">
                                <form method="POST" action="{{ route('tas.ubah', $i['key']) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="aksi" value="kurang">
                                    <button type="submit" class="btn btn-outline-mono btn-sm">&minus;</button>
                                </form>
                                <span class="px-2">{{ $i['qty'] }}</span>
                                <form method="POST" action="{{ route('tas.ubah', $i['key']) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="aksi" value="tambah">
                                    <button type="submit" class="btn btn-outline-mono btn-sm">+</button>
                                </form>

                                <form method="POST" action="{{ route('tas.hapus', $i['key']) }}" class="ms-auto">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm text-dark">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('produk.index') }}" class="lihat-semua">&larr; Lanjut belanja</a>
                    <form method="POST" action="{{ route('tas.kosongkan') }}" onsubmit="return confirm('Kosongkan semua isi tas?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-link btn-sm text-dark lihat-semua p-0">Kosongkan tas</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="kotak">
                    <h5 class="seksi-judul mb-3">Ringkasan</h5>
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Ongkos kirim</span><span>{{ $ongkir === 0 ? 'Gratis' : 'Rp ' . number_format($ongkir, 0, ',', '.') }}</span></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5 mb-3"><span>Total</span><span>Rp {{ number_format($total, 0, ',', '.') }}</span></div>
                    @if ($kurang > 0)
                        <p class="small text-secondary">Belanja Rp {{ number_format($kurang, 0, ',', '.') }} lagi untuk gratis ongkir.</p>
                    @endif
                    <div class="d-grid">
                        <a href="{{ route('checkout') }}" class="btn btn-mono py-3">Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <p class="mb-3">Tas belanjamu masih kosong.</p>
            <a href="{{ route('produk.index') }}" class="btn btn-mono">Mulai Belanja</a>
        </div>
    @endif
</div>
@endsection
EOT);

/* =====================================================================
 *  CHECKOUT & SUKSES
 * ===================================================================== */
tulis('resources/views/checkout.blade.php', <<<'EOT'
@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<div class="container mt-4">
    <h1 class="seksi-judul fs-3 mb-4">Checkout</h1>
    @include('partials.error')

    <div class="row g-4">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('checkout.proses') }}" class="kotak">
                @csrf
                <h5 class="seksi-judul mb-3">Data Pengiriman</h5>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama penerima</label>
                    <input type="text" name="nama" class="form-control" value="{{ old('nama', auth()->check() ? auth()->user()->name : '') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nomor telepon</label>
                    <input type="text" name="telepon" class="form-control" value="{{ old('telepon') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Alamat lengkap</label>
                    <textarea name="alamat" rows="3" class="form-control" required>{{ old('alamat') }}</textarea>
                </div>

                <div class="mb-4">
                    <div class="small fw-bold mb-2">Metode pembayaran</div>
                    @foreach ($pembayaran as $m)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pembayaran" value="{{ $m }}" id="bayar{{ $loop->index }}" {{ old('pembayaran', $pembayaran[0]) === $m ? 'checked' : '' }}>
                            <label class="form-check-label" for="bayar{{ $loop->index }}">{{ $m }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-mono py-3">Buat Pesanan</button>
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="kotak">
                <h5 class="seksi-judul mb-3">Pesananmu</h5>
                @foreach ($items as $i)
                    <div class="d-flex justify-content-between small mb-2">
                        <span>{{ $i['produk']->name }} ({{ $i['ukuran'] }}) &times; {{ $i['qty'] }}</span>
                        <span>Rp {{ number_format($i['subtotal'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <hr>
                <div class="d-flex justify-content-between mb-1"><span>Subtotal</span><span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
                <div class="d-flex justify-content-between mb-1"><span>Ongkos kirim</span><span>{{ $ongkir === 0 ? 'Gratis' : 'Rp ' . number_format($ongkir, 0, ',', '.') }}</span></div>
                <div class="d-flex justify-content-between fw-bold fs-5 mt-2"><span>Total</span><span>Rp {{ number_format($total, 0, ',', '.') }}</span></div>
                <a href="{{ route('tas.index') }}" class="lihat-semua d-inline-block mt-3">&larr; Ubah isi tas</a>
            </div>
        </div>
    </div>
</div>
@endsection
EOT);

tulis('resources/views/sukses.blade.php', <<<'EOT'
@extends('layouts.app')
@section('title', 'Pesanan Berhasil')

@section('content')
<div class="container mt-5" style="max-width: 720px;">
    <div class="text-center mb-4">
        <div style="font-size: 3rem;">&#10003;</div>
        <h1 class="seksi-judul fs-3">Pesanan Berhasil Dibuat</h1>
        <p class="text-secondary">Terima kasih, {{ $pesanan['data']['nama'] }}! Pesananmu sedang kami proses.</p>
    </div>

    <div class="kotak">
        <div class="d-flex justify-content-between mb-3">
            <div><div class="small text-secondary">Kode pesanan</div><div class="fw-bold">{{ $pesanan['kode'] }}</div></div>
            <div class="text-end"><div class="small text-secondary">Tanggal</div><div>{{ $pesanan['tanggal'] }}</div></div>
        </div>
        <hr>
        @foreach ($pesanan['items'] as $i)
            <div class="d-flex justify-content-between small mb-2">
                <span>{{ $i['nama'] }} ({{ $i['ukuran'] }}) &times; {{ $i['qty'] }}</span>
                <span>Rp {{ number_format($i['subtotal'], 0, ',', '.') }}</span>
            </div>
        @endforeach
        <hr>
        <div class="d-flex justify-content-between mb-1"><span>Subtotal</span><span>Rp {{ number_format($pesanan['subtotal'], 0, ',', '.') }}</span></div>
        <div class="d-flex justify-content-between mb-1"><span>Ongkos kirim</span><span>{{ $pesanan['ongkir'] === 0 ? 'Gratis' : 'Rp ' . number_format($pesanan['ongkir'], 0, ',', '.') }}</span></div>
        <div class="d-flex justify-content-between fw-bold fs-5 mt-2"><span>Total</span><span>Rp {{ number_format($pesanan['total'], 0, ',', '.') }}</span></div>
        <hr>
        <div class="small">
            <div><strong>Pembayaran:</strong> {{ $pesanan['data']['pembayaran'] }}</div>
            <div><strong>Dikirim ke:</strong> {{ $pesanan['data']['nama'] }}, {{ $pesanan['data']['telepon'] }}</div>
            <div>{{ $pesanan['data']['alamat'] }}</div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('produk.index') }}" class="btn btn-mono">Lanjut Belanja</a>
    </div>
</div>
@endsection
EOT);

/* =====================================================================
 *  FAVORIT
 * ===================================================================== */
tulis('resources/views/favorit.blade.php', <<<'EOT'
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
EOT);

/* =====================================================================
 *  HALAMAN INFO
 * ===================================================================== */
tulis('resources/views/halaman.blade.php', <<<'EOT'
@extends('layouts.app')
@section('title', $halaman['judul'])

@section('content')
<div class="container mt-4" style="max-width: 760px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('beranda') }}">Beranda</a></li>
            <li class="breadcrumb-item active">{{ $halaman['judul'] }}</li>
        </ol>
    </nav>

    <h1 class="seksi-judul fs-3 mb-4">{{ $halaman['judul'] }}</h1>

    @foreach ($halaman['isi'] as $paragraf)
        <p>{{ $paragraf }}</p>
    @endforeach

    @if (!empty($halaman['daftar']))
        <ul class="list-group list-group-flush border-top border-bottom mb-4">
            @foreach ($halaman['daftar'] as $item)
                <li class="list-group-item">{{ $item }}</li>
            @endforeach
        </ul>
    @endif

    @if (!empty($halaman['form']))
        @include('partials.error')
        <form method="POST" action="{{ route('kontak.kirim') }}" class="kotak">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-bold">Nama</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama', auth()->check() ? auth()->user()->name : '') }}">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Pesan</label>
                <textarea name="pesan" rows="4" class="form-control">{{ old('pesan') }}</textarea>
            </div>
            <button type="submit" class="btn btn-mono">Kirim Pesan</button>
        </form>
    @endif
</div>
@endsection
EOT);

/* =====================================================================
 *  AUTH & AKUN
 * ===================================================================== */
tulis('resources/views/auth/masuk.blade.php', <<<'EOT'
@extends('layouts.app')
@section('title', 'Masuk')

@section('content')
<div class="container mt-5" style="max-width: 440px;">
    <h1 class="seksi-judul fs-3 mb-4 text-center">Masuk</h1>
    @include('partials.error')

    <form method="POST" action="{{ route('masuk.proses') }}" class="kotak">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-bold">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">Kata sandi</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="ingat" id="ingat">
            <label class="form-check-label small" for="ingat">Ingat saya</label>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-mono py-3">Masuk</button>
        </div>
    </form>

    <p class="text-center small mt-3">Belum punya akun? <a href="{{ route('daftar') }}" class="fw-bold text-decoration-underline">Daftar sekarang</a></p>
</div>
@endsection
EOT);

tulis('resources/views/auth/daftar.blade.php', <<<'EOT'
@extends('layouts.app')
@section('title', 'Daftar')

@section('content')
<div class="container mt-5" style="max-width: 440px;">
    <h1 class="seksi-judul fs-3 mb-4 text-center">Daftar Member</h1>
    @include('partials.error')

    <form method="POST" action="{{ route('daftar.proses') }}" class="kotak">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-bold">Nama lengkap</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">Kata sandi (minimal 6 karakter)</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold">Ulangi kata sandi</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-mono py-3">Daftar</button>
        </div>
    </form>

    <p class="text-center small mt-3">Sudah punya akun? <a href="{{ route('login') }}" class="fw-bold text-decoration-underline">Masuk</a></p>
</div>
@endsection
EOT);

tulis('resources/views/akun.blade.php', <<<'EOT'
@extends('layouts.app')
@section('title', 'Akun Saya')

@section('content')
<div class="container mt-4" style="max-width: 760px;">
    <h1 class="seksi-judul fs-3 mb-4">Akun Saya</h1>

    <div class="kotak mb-4">
        <div class="small text-secondary">Nama</div>
        <div class="fw-bold mb-3">{{ $user->name }}</div>
        <div class="small text-secondary">Email</div>
        <div class="fw-bold mb-3">{{ $user->email }}</div>
        <div class="small text-secondary">Member sejak</div>
        <div class="fw-bold">{{ optional($user->created_at)->format('d/m/Y') }}</div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('tas.index') }}" class="btn btn-outline-mono">Tas Belanja</a>
        <a href="{{ route('favorit.index') }}" class="btn btn-outline-mono">Favorit</a>
        <a href="{{ route('produk.index') }}" class="btn btn-outline-mono">Belanja</a>
    </div>

    @if (session('pesanan_terakhir'))
        @php $ps = session('pesanan_terakhir'); @endphp
        <div class="kotak mb-4">
            <h5 class="seksi-judul mb-3">Pesanan Terakhir</h5>
            <div class="d-flex justify-content-between">
                <div><div class="fw-bold">{{ $ps['kode'] }}</div><div class="small text-secondary">{{ $ps['tanggal'] }}</div></div>
                <div class="fw-bold">Rp {{ number_format($ps['total'], 0, ',', '.') }}</div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('keluar') }}">
        @csrf
        <button type="submit" class="btn btn-mono">Keluar</button>
    </form>
</div>
@endsection
EOT);

/* ---------- Bersihkan cache ---------- */
echo "\nMembersihkan cache...\n";
passthru('"' . PHP_BINARY . '" artisan route:clear');
passthru('"' . PHP_BINARY . '" artisan view:clear');
passthru('"' . PHP_BINARY . '" artisan config:clear');

echo "\nSELESAI. Refresh browser (Ctrl+F5). Kalau server belum jalan: php artisan serve\n";
