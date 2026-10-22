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