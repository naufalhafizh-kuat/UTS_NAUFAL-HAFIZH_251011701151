<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HalamanController extends Controller
{
    private function daftar(): array
    {
        $nama = config('app.name');

        $customerService = [
            'judul' => 'Customer Service',
            'isi'   => [
                'Ada pertanyaan tentang pesanan, ukuran, atau pengembalian? Tim kami siap membantu setiap hari pukul 09.00 - 21.00 WIB.',
                'Kirim pesanmu lewat formulir di bawah ini dan kami akan membalas lewat email.',
            ],
            'form'  => true,
        ];

        return [
            'customer-service' => $customerService,
            'kontak'           => array_merge($customerService, ['judul' => 'Kontak']),
            'lokasi-toko'      => [
                'judul'  => 'Lokasi Toko',
                'isi'    => ['Kunjungi toko ' . $nama . ' terdekat. Jam buka setiap hari 10.00 - 22.00 WIB.'],
                'daftar' => [
                    $nama . ' Jakarta - Mall Kota Kasablanka, Lt. 2, Jakarta Selatan',
                    $nama . ' Tangerang Selatan - Pamulang Square, Lt. 1, Tangerang Selatan',
                    $nama . ' Bandung - Paris Van Java, Lt. 1, Bandung',
                    $nama . ' Surabaya - Tunjungan Plaza, Lt. 3, Surabaya',
                ],
            ],
            'unduh-aplikasi'   => [
                'judul'  => 'Unduh Aplikasi',
                'isi'    => ['Aplikasi ' . $nama . ' akan segera hadir di App Store dan Google Play. Sementara itu, kamu bisa berbelanja langsung lewat website ini.'],
            ],
            'tentang'          => [
                'judul' => 'Tentang ' . $nama,
                'isi'   => [
                    $nama . ' adalah toko fashion online dengan koleksi baju dan celana bergaya simpel dan monokrom untuk keseharian.',
                    'Kami percaya pakaian yang baik tidak harus mahal: bahan nyaman, potongan rapi, dan mudah dipadukan.',
                ],
            ],
            'karir'            => [
                'judul' => 'Berkarir',
                'isi'   => ['Saat ini belum ada lowongan terbuka. Pantau terus halaman ini untuk informasi terbaru dari tim ' . $nama . '.'],
            ],
            'keberlanjutan'    => [
                'judul' => 'Keberlanjutan',
                'isi'   => [
                    'Kami berkomitmen memakai kemasan ramah lingkungan dan memilih bahan yang lebih bertanggung jawab secara bertahap.',
                ],
            ],
            'pers'             => [
                'judul' => 'Pers',
                'isi'   => ['Untuk kebutuhan media dan kerja sama, hubungi kami lewat halaman Kontak.'],
            ],
            'kebijakan-privasi' => [
                'judul' => 'Kebijakan Privasi',
                'isi'   => [
                    'Kami hanya menyimpan data yang diperlukan untuk memproses pesanan dan akunmu, dan tidak membagikannya kepada pihak ketiga tanpa persetujuanmu.',
                    'Kamu dapat meminta penghapusan datamu kapan saja lewat halaman Kontak.',
                ],
            ],
        ];
    }

    public function tampil($slug)
    {
        $halaman = $this->daftar()[$slug] ?? abort(404);
        return view('halaman', compact('halaman'));
    }

    public function kirimKontak(Request $request)
    {
        $request->validate([
            'nama'  => 'required|string|max:100',
            'email' => 'required|email',
            'pesan' => 'required|string|min:10|max:1000',
        ], [
            'nama.required'  => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'pesan.required' => 'Pesan wajib diisi.',
            'pesan.min'      => 'Pesan minimal 10 karakter.',
        ]);

        return back()->with('pesan', 'Terima kasih, pesanmu sudah terkirim. Kami akan membalas lewat email.');
    }
}