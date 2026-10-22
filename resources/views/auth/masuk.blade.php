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