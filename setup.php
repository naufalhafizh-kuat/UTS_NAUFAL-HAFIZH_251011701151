<?php
/*
 * setup.php - Jalankan dari root project Laravel:  php setup.php
 * Fungsi: set .env, buat database, tulis semua file (migration, seeder, model,
 * controller, route, view, gambar), lalu migrate + seed.
 */

$nim    = '251011701151';
$dbName = 'db_uts_' . $nim;

if (!file_exists(__DIR__ . '/artisan')) {
    exit("ERROR: jalankan file ini dari dalam folder project Laravel (yang ada file 'artisan').\n");
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

/* ---------- 1. .env ---------- */
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile) && file_exists(__DIR__ . '/.env.example')) {
    copy(__DIR__ . '/.env.example', $envFile);
}
$env = file_get_contents($envFile);
$set = [
    'DB_CONNECTION' => 'mysql',
    'DB_HOST'       => '127.0.0.1',
    'DB_PORT'       => '3306',
    'DB_DATABASE'   => $dbName,
    'DB_USERNAME'   => 'root',
    'DB_PASSWORD'   => '',
];
foreach ($set as $k => $v) {
    $pola = '/^#?\s*' . $k . '=.*$/m';
    if (preg_match($pola, $env)) {
        $env = preg_replace($pola, $k . '=' . $v, $env, 1);
    } else {
        $env .= "\n" . $k . '=' . $v;
    }
}
file_put_contents($envFile, $env);
echo "OK  .env (database: $dbName)\n";

/* ---------- 2. Buat database ---------- */
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
    echo "OK  database $dbName siap\n";
} catch (Throwable $e) {
    echo "PERINGATAN: gagal membuat database otomatis: " . $e->getMessage() . "\n";
    echo "Pastikan MySQL di Laragon sudah Start, lalu jalankan ulang: php setup.php\n";
}

/* ---------- 3. Migration ---------- */
tulis('database/migrations/2026_10_22_000000_create_ecommerce_table.php', <<<'EOT'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('price');
            $table->string('image');
            $table->string('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce');
    }
};
EOT);

/* ---------- 4. Model ---------- */
tulis('app/Models/Ecommerce.php', <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ecommerce extends Model
{
    protected $table = 'ecommerce';
    public $timestamps = false;

    protected $fillable = ['name', 'description', 'price', 'image', 'category'];
}
EOT);

/* ---------- 5. Seeder ---------- */
tulis('database/seeders/EcommerceSeeder.php', <<<'EOT'
<?php

namespace Database\Seeders;

use App\Models\Ecommerce;
use Illuminate\Database\Seeder;

class EcommerceSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Kaos Polos Hijau',     'category' => 'Kaos',   'price' => 75000,  'image' => 'kaos-hijau.svg',     'description' => 'Kaos katun combed 30s yang adem dan nyaman dipakai sehari-hari.'],
            ['name' => 'Kemeja Kuning Casual', 'category' => 'Kemeja', 'price' => 135000, 'image' => 'kemeja-kuning.svg',  'description' => 'Kemeja lengan panjang bahan katun, cocok untuk santai maupun kerja.'],
            ['name' => 'Hoodie Olive',         'category' => 'Jaket',  'price' => 185000, 'image' => 'hoodie-olive.svg',   'description' => 'Hoodie fleece tebal dengan kantong depan, hangat dan stylish.'],
            ['name' => 'Celana Chino Krem',    'category' => 'Celana', 'price' => 160000, 'image' => 'celana-chino.svg',   'description' => 'Celana chino slim fit dengan bahan stretch yang nyaman bergerak.'],
            ['name' => 'Kaos Oversize Kuning', 'category' => 'Kaos',   'price' => 89000,  'image' => 'kaos-kuning.svg',    'description' => 'Kaos oversize bahan tebal dengan potongan kekinian.'],
            ['name' => 'Jaket Bomber Hijau',   'category' => 'Jaket',  'price' => 225000, 'image' => 'jaket-bomber.svg',   'description' => 'Jaket bomber water-resistant dengan lapisan dalam yang lembut.'],
            ['name' => 'Kemeja Flannel Hijau', 'category' => 'Kemeja', 'price' => 145000, 'image' => 'kemeja-flannel.svg', 'description' => 'Kemeja flannel motif kotak, hangat dan cocok untuk gaya kasual.'],
            ['name' => 'Celana Jogger Kuning', 'category' => 'Celana', 'price' => 120000, 'image' => 'celana-jogger.svg',  'description' => 'Celana jogger ringan dengan karet di pergelangan, santai dan praktis.'],
        ];

        foreach ($data as $item) {
            Ecommerce::create($item);
        }
    }
}
EOT);

tulis('database/seeders/DatabaseSeeder.php', <<<'EOT'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(EcommerceSeeder::class);
    }
}
EOT);

/* ---------- 6. Controller & route ---------- */
tulis('app/Http/Controllers/ProdukController.php', <<<'EOT'
<?php

namespace App\Http\Controllers;

use App\Models\Ecommerce;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Ecommerce::all();
        return view('produk.index', compact('produk'));
    }

    public function show($id)
    {
        $produk = Ecommerce::findOrFail($id);
        return view('produk.show', compact('produk'));
    }
}
EOT);

tulis('routes/web.php', <<<'EOT'
<?php

use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProdukController::class, 'index'])->name('produk.index');
Route::get('/produk/{id}', [ProdukController::class, 'show'])->name('produk.show');
EOT);

/* ---------- 7. Views ---------- */
tulis('resources/views/layouts/app.blade.php', <<<'EOT'
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'E-commerce Baju') | HijauKuning Store</title>

    <link rel="stylesheet" href="{{ asset('Bootstrap/css/bootstrap.min.css') }}">

    <style>
        :root { --hijau: #1b7a3d; --hijau-tua: #0f5228; --kuning: #f9c80e; }
        .navbar-hijau { background-color: var(--hijau); }
        .navbar-hijau .navbar-brand span { color: var(--kuning); }
        .navbar-hijau .nav-link { color: #fff; }
        .navbar-hijau .nav-link:hover, .navbar-hijau .nav-link.active { color: var(--kuning); }
        .hero { background: linear-gradient(135deg, var(--hijau), var(--hijau-tua)); color: #fff; }
        .btn-kuning { background-color: var(--kuning); color: #3a2f00; font-weight: 600; border: 0; }
        .btn-kuning:hover { background-color: #d9a900; color: #3a2f00; }
        .btn-hijau { background-color: var(--hijau); color: #fff; }
        .btn-hijau:hover { background-color: var(--hijau-tua); color: #fff; }
        .badge-kuning { background-color: var(--kuning); color: #3a2f00; }
        .harga { color: var(--hijau); font-weight: 700; }
        .card-produk { transition: transform .2s; }
        .card-produk:hover { transform: translateY(-5px); }
        .card-produk img { background: #e8f5ec; aspect-ratio: 1 / 1; object-fit: cover; }
        footer { background: var(--hijau-tua); border-top: 5px solid var(--kuning); color: #fff; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-hijau">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="{{ route('produk.index') }}">🌿 HijauKuning<span>Store</span></a>
        <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('produk.index') ? 'active' : '' }}" href="{{ route('produk.index') }}">Daftar Produk</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

<footer class="text-center py-3 mt-5">
    <small>&copy; {{ date('Y') }} HijauKuning Store - UTS Rekayasa Web</small>
</footer>

<script src="{{ asset('Bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
EOT);

tulis('resources/views/produk/index.blade.php', <<<'EOT'
@extends('layouts.app')
@section('title', 'Daftar Produk')

@section('content')
<section class="hero text-center py-5">
    <div class="container py-4">
        <h1 class="fw-bold">Tampil Segar dengan Gaya Hijau &amp; Kuning</h1>
        <p class="mb-0">Koleksi baju kasual berkualitas dengan harga bersahabat.</p>
    </div>
</section>

<div class="container mt-5">
    <h2 class="fw-bold mb-4" style="border-left: 6px solid var(--kuning); padding-left: 12px;">Daftar Produk</h2>

    <div class="row g-4">
        @foreach ($produk as $p)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card card-produk h-100 shadow-sm border-0">
                    <img src="{{ asset('assets/images/' . $p->image) }}" class="card-img-top" alt="{{ $p->name }}">
                    <div class="card-body d-flex flex-column">
                        <span class="badge badge-kuning align-self-start mb-2">{{ $p->category }}</span>
                        <h6 class="card-title">{{ $p->name }}</h6>
                        <p class="harga mt-auto mb-2">Rp {{ number_format($p->price, 0, ',', '.') }}</p>
                        <a href="{{ route('produk.show', $p->id) }}" class="btn btn-hijau btn-sm">Lihat Detail</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
EOT);

tulis('resources/views/produk/show.blade.php', <<<'EOT'
@extends('layouts.app')
@section('title', $produk->name)

@section('content')
<div class="container mt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('produk.index') }}">Produk</a></li>
            <li class="breadcrumb-item active">{{ $produk->name }}</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm">
        <div class="row g-0">
            <div class="col-md-5">
                <img src="{{ asset('assets/images/' . $produk->image) }}" class="img-fluid rounded-start w-100" alt="{{ $produk->name }}">
            </div>
            <div class="col-md-7">
                <div class="card-body p-4">
                    <span class="badge badge-kuning mb-2">{{ $produk->category }}</span>
                    <h2 class="fw-bold">{{ $produk->name }}</h2>
                    <p class="harga fs-3">Rp {{ number_format($produk->price, 0, ',', '.') }}</p>
                    <p>{{ $produk->description }}</p>
                    <a href="{{ route('produk.index') }}" class="btn btn-kuning">&larr; Kembali ke Daftar</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
EOT);

/* ---------- 8. Gambar (SVG placeholder) ---------- */
$kemeja = 'M60 30 L85 20 Q100 40 115 20 L140 30 L175 60 L155 85 L140 75 L140 170 L60 170 L60 75 L45 85 L25 60 Z';
$celana = 'M65 25 L135 25 L145 175 L108 175 L100 85 L92 175 L55 175 Z';
$gambar = [
    ['kaos-hijau',     'shirt',  '#1b7a3d', '#e8f5ec', 'Kaos Polos Hijau'],
    ['kemeja-kuning',  'shirt',  '#f9c80e', '#fff8dc', 'Kemeja Kuning'],
    ['hoodie-olive',   'jacket', '#6b7d2a', '#eef3d9', 'Hoodie Olive'],
    ['celana-chino',   'pants',  '#d8c8a0', '#f7f1e3', 'Celana Chino'],
    ['kaos-kuning',    'shirt',  '#f9c80e', '#fff8dc', 'Kaos Oversize'],
    ['jaket-bomber',   'jacket', '#0f5228', '#e8f5ec', 'Jaket Bomber'],
    ['kemeja-flannel', 'shirt',  '#2f8f4e', '#e8f5ec', 'Kemeja Flannel'],
    ['celana-jogger',  'pants',  '#f9c80e', '#fff8dc', 'Celana Jogger'],
];
foreach ($gambar as [$nama, $jenis, $warna, $latar, $label]) {
    $path  = $jenis === 'pants' ? $celana : $kemeja;
    $extra = $jenis === 'jacket' ? "<line x1='100' y1='35' x2='100' y2='170' stroke='#ffffff' stroke-width='3' opacity='.7'/>" : '';
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 220' width='600' height='660'>"
         . "<rect width='200' height='220' fill='$latar'/>"
         . "<path d='$path' fill='$warna' stroke='#00000030' stroke-width='2'/>"
         . $extra
         . "<text x='100' y='205' font-family='Arial, sans-serif' font-size='13' font-weight='bold' text-anchor='middle' fill='#0f5228'>$label</text>"
         . "</svg>";
    tulis("public/assets/images/$nama.svg", $svg);
}

/* ---------- 9. Migrate + seed ---------- */
echo "\nMenjalankan migrate + seed...\n";
passthru('"' . PHP_BINARY . '" artisan migrate:fresh --seed --force');

echo "\nSELESAI. Pasang Bootstrap ke public/Bootstrap, lalu jalankan: php artisan serve\n";
