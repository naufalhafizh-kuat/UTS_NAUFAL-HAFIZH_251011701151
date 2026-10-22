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

// Newsletter (form di beranda dan footer)
Route::post('/newsletter', function (\Illuminate\Http\Request $request) {
    $request->validate(['email_newsletter' => 'required|email'], [
        'email_newsletter.required' => 'Email untuk newsletter wajib diisi.',
        'email_newsletter.email'    => 'Format email newsletter tidak valid.',
    ]);
    return back()->with('pesan', 'Terima kasih! Kamu sudah berlangganan newsletter kami.');
})->name('newsletter');