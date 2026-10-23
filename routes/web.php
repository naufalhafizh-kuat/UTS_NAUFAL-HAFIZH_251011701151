<?php

use App\Http\Controllers\HalamanController;
use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProdukController::class, 'beranda'])->name('beranda');
Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
Route::get('/produk/{id}', [ProdukController::class, 'show'])->name('produk.show');
Route::get('/halaman/{slug}', [HalamanController::class, 'tampil'])->name('halaman');