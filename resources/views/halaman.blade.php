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