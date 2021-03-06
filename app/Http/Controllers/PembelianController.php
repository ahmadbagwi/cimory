<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pembelian;
use App\Stok;
use App\Produk;
use App\Laporan;

class PembelianController extends Controller
{
    public function index($kode_dc)
    {
        // Ambil data pembelian berdasarkan kode_dc dengan relasi dan where
        $pembelian = Laporan::with('dc:dc.id,kode_dc', 'produk:produk.id,nama_produk')
        ->whereHas('dc', function ($query) use ($kode_dc) {
            $query->where('kode_dc', $kode_dc);
        })->get();
        // Gunakan data laporan sebagai tabel pembelian
        // $pembelian= Laporan::with('dc:dc.id,kode_dc', 'produk:produk.id,nama_produk')->where('dc_id', $kode_dc)->get();
        return response()->json($pembelian);
    }

    public function update_stok($dc_id, $tanggal_pembelian, $produk_id, $keterangan, $qty_pembelian)
    {
        // Cari stok terakhir untuk ditambahkan dengan qty_pembelian => Done
        $stok_terakhir = Stok::where('dc_id', $dc_id)->where('produk_id', $produk_id)->orderBy('created_at', 'desc')->first();
        // Jika ditemukan stok terakhir tambahkan dengan qty_pembelian
        if ($stok_terakhir != null){
            $stok_terbaru = $stok_terakhir->qty_stok + $qty_pembelian;
        } else {
        // Jika stok terakhir = null, artinya ini pembelian pertama kali (sistem mulai dipakai)
            $stok_terbaru = $qty_pembelian;
        }
        // Simpan rincian pembelian di tabel stok
        $stok = new Stok([
            'dc_id' => $dc_id,
            'tanggal_stok' => $tanggal_pembelian,
            'keterangan' => "pembelian ".$keterangan." : ".$qty_pembelian,
            'produk_id' => $produk_id,
            'qty_stok' => $stok_terbaru
        ]);
        if ($stok->save()) {
            return true;
        }
    }

    public function update_laporan($dc_id, $tanggal_pembelian, $produk_id, $qty_pembelian)
    {
        // Cari stok terakhir untuk ditambahkan dengan qty_pembelian dan disimpan di tabel laporan
        $stok_terakhir = Stok::where('dc_id', $dc_id)->where('produk_id', $produk_id)->orderBy('created_at', 'desc')->first();
        if ($stok_terakhir != null){
            $stok_terbaru = $stok_terakhir->qty_stok + $qty_pembelian;
        } else {
        // Jika stok terakhir = null, artinya ini pembelian pertama kali (sistem mulai dipakai)
            $stok_terbaru = $qty_pembelian;
        }

        $laporan = new Laporan([
              'dc_id' => $dc_id,
              'tanggal' => $tanggal_pembelian,
              'produk_id' => $produk_id,
              'qty_pembelian' => $qty_pembelian,
              'qty_penjualan' => 0, // Ini akan berubah ketika ada penjualan
              'qty_retur' => 0, // Ini akan berubah ketika ada retur
              'qty_stok' => $stok_terbaru // Ini akan berubah ketika ada penjualan/retur
        ]);
        if ($laporan->save()) {
            return true;
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'dc_id' => ['required'],
            'tanggal_pembelian' => ['required', 'date'],
            'no_invoice' => ['required'],
            'produk_id' => ['required'],
            'qty_pembelian' => ['required']
        ]);

        $pembelian = new Pembelian($request->all());
        if ($pembelian->save()) {
          $this->update_laporan($dc_id = $pembelian['dc_id'], $tanggal_pembelian = $pembelian['tanggal_pembelian'], $produk_id = $pembelian['produk_id'], $qty_pembelian = $pembelian['qty_pembelian']);
          $this->update_stok($dc_id = $pembelian['dc_id'], $tanggal_pembelian = $pembelian['tanggal_pembelian'], $produk_id = $pembelian['produk_id'], $keterangan = $pembelian['no_invoice'], $qty_pembelian = $pembelian['qty_pembelian']);
        }
        return response()->json("Sukses ".$pembelian);
    }

    public function grafik_pembelian($tanggal_pencarian, $produk_id)
    {
        $pembelian = Pembelian::with('dc:dc.id,kode_dc', 'produk:produk.id,nama_produk')->where('tanggal_pembelian', $tanggal_pencarian)->where('produk_id', $produk_id)->get();
        return response()->json($pembelian);
    }
}
