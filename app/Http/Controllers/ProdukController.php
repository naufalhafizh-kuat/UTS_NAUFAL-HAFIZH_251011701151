<?php

namespace App\Http\Controllers;

use App\Models\Ecommerce;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function beranda()
    {
        $baru = Ecommerce::orderByDesc('id')->take(8)->get();

        $kategoriList = collect(['Kaos', 'Kemeja', 'Jaket', 'Celana', 'Jeans', 'Knitwear'])
            ->map(fn ($k) => [
                'nama'   => $k,
                'produk' => Ecommerce::where('category', $k)->first(),
                'jumlah' => Ecommerce::where('category', $k)->count(),
            ])
            ->filter(fn ($t) => $t['produk'] !== null);

        return view('beranda', compact('baru', 'kategoriList'));
    }

    public function index(Request $request)
    {
        $query = Ecommerce::query();

        $kategori = $request->query('kategori');
        $cari     = $request->query('q');
        $urut     = $request->query('urut');

        if ($kategori) {
            $query->where('category', $kategori);
        }
        if ($cari) {
            $query->where('name', 'like', '%' . $cari . '%');
        }

        if ($urut === 'termurah') {
            $query->orderBy('price');
        } elseif ($urut === 'termahal') {
            $query->orderByDesc('price');
        } else {
            $query->orderBy('id');
        }

        $produk = $query->get();

        return view('produk.index', compact('produk', 'kategori', 'cari', 'urut'));
    }

    public function show($id)
    {
        $produk = Ecommerce::findOrFail($id);
        $serupa = Ecommerce::where('category', $produk->category)
            ->where('id', '!=', $produk->id)
            ->take(4)
            ->get();

        return view('produk.show', compact('produk', 'serupa'));
    }
}