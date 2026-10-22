<?php

namespace App\Http\Controllers;

use App\Models\Ecommerce;

class FavoritController extends Controller
{
    public function index()
    {
        $ids = session('favorit', []);
        $produk = Ecommerce::whereIn('id', $ids)->get();
        return view('favorit', compact('produk'));
    }

    public function toggle($id)
    {
        $produk = Ecommerce::findOrFail($id);
        $id = (int) $produk->id;

        $favorit = session('favorit', []);
        if (in_array($id, $favorit)) {
            $favorit = array_values(array_diff($favorit, [$id]));
            $pesan = $produk->name . ' dihapus dari favorit.';
        } else {
            $favorit[] = $id;
            $pesan = $produk->name . ' disimpan ke favorit.';
        }
        session(['favorit' => $favorit]);

        return back()->with('pesan', $pesan);
    }
}