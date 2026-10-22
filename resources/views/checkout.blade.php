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