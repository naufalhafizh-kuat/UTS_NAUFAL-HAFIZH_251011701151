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