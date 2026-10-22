<?php

namespace App\Http\Controllers;

use App\Models\Ecommerce;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KeranjangController extends Controller
{
    private const GRATIS_ONGKIR = 499000;
    private const ONGKIR = 20000;
    private const PEMBAYARAN = ['Transfer Bank', 'COD (Bayar di Tempat)', 'E-Wallet'];

    private function ambilItem(): array
    {
        $tas = session('keranjang', []);
        $produk = Ecommerce::whereIn('id', collect($tas)->pluck('id')->all())->get()->keyBy('id');

        $items = [];
        foreach ($tas as $key => $row) {
            if (!isset($produk[$row['id']])) {
                continue;
            }
            $p = $produk[$row['id']];
            $items[] = [
                'key'      => $key,
                'produk'   => $p,
                'ukuran'   => $row['ukuran'],
                'qty'      => $row['qty'],
                'subtotal' => $p->price * $row['qty'],
            ];
        }
        return $items;
    }

    private function hitung(array $items): array
    {
        $subtotal = (int) collect($items)->sum('subtotal');
        $ongkir   = ($subtotal === 0 || $subtotal >= self::GRATIS_ONGKIR) ? 0 : self::ONGKIR;
        return [$subtotal, $ongkir, $subtotal + $ongkir];
    }

    public function index()
    {
        $items = $this->ambilItem();
        [$subtotal, $ongkir, $total] = $this->hitung($items);
        $kurang = max(0, self::GRATIS_ONGKIR - $subtotal);

        return view('tas', compact('items', 'subtotal', 'ongkir', 'total', 'kurang'));
    }

    public function tambah(Request $request, $id)
    {
        $produk = Ecommerce::findOrFail($id);

        $ukuran = $request->input('ukuran', 'M');
        if (!in_array($ukuran, ['S', 'M', 'L', 'XL'])) {
            $ukuran = 'M';
        }

        $tas = session('keranjang', []);
        $key = $produk->id . '-' . $ukuran;

        if (isset($tas[$key])) {
            $tas[$key]['qty'] = min(20, $tas[$key]['qty'] + 1);
        } else {
            $tas[$key] = ['id' => $produk->id, 'ukuran' => $ukuran, 'qty' => 1];
        }
        session(['keranjang' => $tas]);

        return back()
            ->with('pesan', $produk->name . ' (ukuran ' . $ukuran . ') ditambahkan ke tas.')
            ->with('pesan_link', route('tas.index'))
            ->with('pesan_link_label', 'Lihat tas');
    }

    public function ubah(Request $request, $key)
    {
        $tas = session('keranjang', []);
        if (isset($tas[$key])) {
            $qty = $tas[$key]['qty'] + ($request->input('aksi') === 'kurang' ? -1 : 1);
            if ($qty < 1) {
                unset($tas[$key]);
            } else {
                $tas[$key]['qty'] = min(20, $qty);
            }
            session(['keranjang' => $tas]);
        }
        return redirect()->route('tas.index');
    }

    public function hapus($key)
    {
        $tas = session('keranjang', []);
        unset($tas[$key]);
        session(['keranjang' => $tas]);
        return redirect()->route('tas.index')->with('pesan', 'Produk dihapus dari tas.');
    }

    public function kosongkan()
    {
        session()->forget('keranjang');
        return redirect()->route('tas.index')->with('pesan', 'Tas belanja dikosongkan.');
    }

    public function checkout()
    {
        $items = $this->ambilItem();
        if (!$items) {
            return redirect()->route('tas.index')->with('pesan', 'Tas belanjamu masih kosong.');
        }
        [$subtotal, $ongkir, $total] = $this->hitung($items);
        $pembayaran = self::PEMBAYARAN;

        return view('checkout', compact('items', 'subtotal', 'ongkir', 'total', 'pembayaran'));
    }

    public function proses(Request $request)
    {
        $items = $this->ambilItem();
        if (!$items) {
            return redirect()->route('tas.index')->with('pesan', 'Tas belanjamu masih kosong.');
        }

        $data = $request->validate([
            'nama'       => 'required|string|max:100',
            'telepon'    => 'required|string|max:20',
            'alamat'     => 'required|string|max:255',
            'pembayaran' => ['required', Rule::in(self::PEMBAYARAN)],
        ], [
            'nama.required'       => 'Nama penerima wajib diisi.',
            'telepon.required'    => 'Nomor telepon wajib diisi.',
            'alamat.required'     => 'Alamat pengiriman wajib diisi.',
            'pembayaran.required' => 'Pilih metode pembayaran.',
            'pembayaran.in'       => 'Metode pembayaran tidak valid.',
        ]);

        [$subtotal, $ongkir, $total] = $this->hitung($items);

        $pesanan = [
            'kode'     => 'LXC-' . date('ymd') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 5)),
            'tanggal'  => now()->format('d/m/Y H:i'),
            'data'     => $data,
            'items'    => collect($items)->map(fn ($i) => [
                'nama'     => $i['produk']->name,
                'ukuran'   => $i['ukuran'],
                'qty'      => $i['qty'],
                'subtotal' => $i['subtotal'],
            ])->all(),
            'subtotal' => $subtotal,
            'ongkir'   => $ongkir,
            'total'    => $total,
        ];

        session(['pesanan_terakhir' => $pesanan]);
        session()->forget('keranjang');

        return redirect()->route('checkout.sukses');
    }

    public function sukses()
    {
        $pesanan = session('pesanan_terakhir');
        if (!$pesanan) {
            return redirect()->route('beranda');
        }
        return view('sukses', compact('pesanan'));
    }
}