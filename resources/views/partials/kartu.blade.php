@php
    $fav  = in_array($p->id, session('favorit', []));
    $baru = $p->id >= 9;
@endphp
<div class="kartu-produk">
    <div class="gambar-wrap">
        @if ($baru) <span class="badge-baru">BARU</span> @endif

        <form action="{{ route('favorit.toggle', $p->id) }}" method="POST" class="btn-fav">
            @csrf
            <button type="submit" title="{{ $fav ? 'Hapus dari favorit' : 'Simpan ke favorit' }}">
                <svg viewBox="0 0 24 24" class="{{ $fav ? 'aktif' : '' }}"><path d="M12 21s-8-5.5-8-11a4.5 4.5 0 0 1 8-2.5A4.5 4.5 0 0 1 20 10c0 5.5-8 11-8 11z"/></svg>
            </button>
        </form>

        <a href="{{ route('produk.show', $p->id) }}" class="gambar">
            <img src="{{ $p->gambar_url }}" alt="{{ $p->name }}" loading="lazy">
        </a>
        <a href="{{ route('produk.show', $p->id) }}" class="kartu-cta">Lihat Detail</a>
    </div>
    <div class="info">
        <div class="kat">{{ $p->category }}</div>
        <a href="{{ route('produk.show', $p->id) }}" class="nama">{{ $p->name }}</a>
        <div class="harga">Rp {{ number_format($p->price, 0, ',', '.') }}</div>
    </div>
</div>