<?php

namespace App\Http\Controllers;

class HalamanController extends Controller
{
    private function daftar(): array
    {
        $nama = config('app.name');

        $kontak = [
            'judul'  => 'Customer Service',
            'isi'    => [
                'Ada pertanyaan tentang produk atau ukuran? Tim kami siap membantu setiap hari pukul 09.00 - 21.00 WIB.',
            ],
            'daftar' => [
                'Email: halo@luxcend.test',
                'WhatsApp: 0812-3456-7890',
                'Instagram: @luxcend',
            ],
        ];

        return [
            'customer-service'  => $kontak,
            'kontak'            => array_merge($kontak, ['judul' => 'Kontak']),
            'lokasi-toko'       => [
                'judul'  => 'Lokasi Toko',
                'isi'    => ['Kunjungi toko ' . $nama . ' terdekat. Jam buka setiap hari 10.00 - 22.00 WIB.'],
                'daftar' => [
                    $nama . ' Jakarta - Mall Kota Kasablanka, Lt. 2, Jakarta Selatan',
                    $nama . ' Tangerang Selatan - Pamulang Square, Lt. 1, Tangerang Selatan',
                    $nama . ' Bandung - Paris Van Java, Lt. 1, Bandung',
                    $nama . ' Surabaya - Tunjungan Plaza, Lt. 3, Surabaya',
                ],
            ],
            'unduh-aplikasi'    => [
                'judul' => 'Unduh Aplikasi',
                'isi'   => ['Aplikasi ' . $nama . ' akan segera hadir di App Store dan Google Play.'],
            ],
            'tentang'           => [
                'judul' => 'Tentang ' . $nama,
                'isi'   => [
                    $nama . ' adalah toko fashion online dengan koleksi baju dan celana bergaya simpel untuk keseharian.',
                    'Kami percaya pakaian yang baik tidak harus mahal: bahan nyaman, potongan rapi, dan mudah dipadukan.',
                ],
            ],
            'karir'             => [
                'judul' => 'Berkarir',
                'isi'   => ['Saat ini belum ada lowongan terbuka. Pantau terus halaman ini untuk informasi terbaru.'],
            ],
            'keberlanjutan'     => [
                'judul' => 'Keberlanjutan',
                'isi'   => ['Kami berkomitmen memakai kemasan ramah lingkungan dan memilih bahan yang lebih bertanggung jawab secara bertahap.'],
            ],
            'pers'              => [
                'judul' => 'Pers',
                'isi'   => ['Untuk kebutuhan media dan kerja sama, hubungi kami melalui halaman Kontak.'],
            ],
            'kebijakan-privasi' => [
                'judul' => 'Kebijakan Privasi',
                'isi'   => ['Website ini hanya menampilkan katalog produk dan tidak mengumpulkan data pengunjung.'],
            ],
        ];
    }

    public function tampil($slug)
    {
        $halaman = $this->daftar()[$slug] ?? abort(404);
        return view('halaman', compact('halaman'));
    }
}