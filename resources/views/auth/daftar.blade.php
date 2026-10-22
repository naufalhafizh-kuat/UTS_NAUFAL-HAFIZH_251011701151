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