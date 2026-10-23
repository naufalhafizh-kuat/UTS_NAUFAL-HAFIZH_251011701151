@php $baru = $p->id >= 9; @endphp
<div class="kartu-produk">
    <div class="gambar-wrap">
        @if ($baru) <span class="badge-baru">BARU</span> @endif
        <a href="{{ route('produk.show', $p->id) }}" class="gambar">
            <img src="{{ $p->gambar_url }}" alt="{{ $p->name }}" loading="lazy">
        </a>
    </div>
    <div class="info">
        <div class="kat">{{ $p->category }}</div>
        <a href="{{ route('produk.show', $p->id) }}" class="nama">{{ $p->name }}</a>
        <div class="harga">Rp {{ number_format($p->price, 0, ',', '.') }}</div>
        <a href="{{ route('produk.show', $p->id) }}" class="btn btn-mono btn-sm btn-detail w-100 mt-2">Lihat Detail</a>
    </div>
</div>