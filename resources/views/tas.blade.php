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