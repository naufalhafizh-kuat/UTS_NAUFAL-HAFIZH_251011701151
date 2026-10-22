<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ecommerce extends Model
{
    protected $table = 'ecommerce';
    public $timestamps = false;

    protected $fillable = ['name', 'description', 'price', 'image', 'category'];

    // Pakai foto asli (mis. produk-1.jpg) jika ada di public/assets/images,
    // kalau belum ada otomatis pakai placeholder produk-1.svg
    public function getGambarUrlAttribute(): string
    {
        $file = $this->image;
        if (!file_exists(public_path('assets/images/' . $file))) {
            $file = pathinfo($file, PATHINFO_FILENAME) . '.svg';
        }
        return asset('assets/images/' . $file);
    }
}