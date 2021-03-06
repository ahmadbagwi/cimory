<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Penjualan;
use App\Pembelian;
use App\Stok;
use App\Laporan;

class PenjualanController extends Controller
{
    public function index($kode_dc)
    {
        // Ambil data penjualan berdasarkan kode_dc
        $penjualan = Penjualan::with('dc:dc.id,kode_dc', 'produk:produk.id,nama_produk', 'sales:sales.id,kode_sales')
        ->whereHas('dc', function ($query) use ($kode_dc) {
            $query->where('kode_dc', $kode_dc);
        })->get();

        return response()->json($penjualan);
    }

    public function update_stok($dc_id, $tanggal_penjualan, $kode_transaksi, $produk_id, $qty_penjualan)
    {
        // Cari stok terakhir untuk dikurangi qty_penjualan dan disimpan di tabel stok
        $stok_terakhir = Stok::where('dc_id', $dc_id)->where('produk_id', $produk_id)->orderBy('created_at', 'desc')->first();
        // Stok terakhir seharusnya tidak null karena pembelian pertama kali menghasilkan stok => PembelianController => 67
        if ($stok_terakhir != null) {
          $qty_stok = $stok_terakhir->qty_stok - $qty_penjualan;
          $stok = new Stok([
              'dc_id' => $dc_id,
              'tanggal_stok' => $tanggal_penjualan,
              'keterangan' => "penjualan ".$kode_transaksi." : ".$qty_penjualan,
              'produk_id' => $produk_id,
              'qty_stok' => $qty_stok,
          ]);
          $stok->save();
          return true;
        }
    }

    public function update_laporan($dc_id, $tanggal_penjualan, $produk_id, $qty_penjualan)
    {
        // Cari data laporan yang akan diupdate
        $laporan = Laporan::where('dc_id', $dc_id)->where('produk_id', $produk_id)->orderBy('created_at', 'desc')->first();
        // Jumlahkan semua nilai penjualan berdasarkan dc, tanggal dan produk
        $sum_penjualan = Penjualan::where('dc_id', $dc_id)->where('tanggal_penjualan', $tanggal_penjualan)->where('produk_id', $produk_id)->sum('qty_penjualan');
        // Jika tanggal laporan terakhir/yang ada == tanggal_penjualan, update stok laporan tersebut. Berarti pembelian dan penjualan dihari yang sama
        if ($laporan->tanggal == $tanggal_penjualan) {
          // Bug, $laporan tidak bisa akses didalam if else, sehingga buat variable baru $laporan1 dan $laporan2
          $laporan1 = Laporan::where('dc_id', $dc_id)->where('produk_id', $produk_id)->orderBy('created_at', 'desc')->first();
          $update_laporan = Laporan::where('id', $laporan1->id)->update(array('qty_penjualan' => $sum_penjualan, 'qty_stok' => $laporan1->qty_stok - $qty_penjualan));
        } else {
        // Jika tanggal laporan terakhir != tanggal_penjualan, buat row laporan baru, artinya tidak ada pembelian tapi ada penjualan
          $laporan2 = Laporan::where('dc_id', $dc_id)->where('produk_id', $produk_id)->orderBy('created_at', 'desc')->first();
          $new_laporan = new Laporan([
            'dc_id' => $dc_id,
            'tanggal' => $tanggal_penjualan,
            'produk_id' => $produk_id,
            'qty_pembelian' => 0,
            'qty_penjualan' => $sum_penjualan,
            'qty_retur' =>0,
            'qty_stok' => $laporan2->qty_stok - $sum_penjualan
          ]);
          $new_laporan->save();
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'dc_id' => ['required'],
            'tanggal_penjualan' => ['required', 'date'],
            'kode_transaksi' => ['required'],
            'sales_id' => ['required'],
            'produk_id' => ['required'],
            'qty_penjualan' => ['required'],
            'nilai_penjualan' => ['required']
        ]);

        $penjualan = new Penjualan($request->all());
        if ($penjualan->save()) {
            $this->update_stok($dc_id = $penjualan['dc_id'], $tanggal_penjualan = $penjualan['tanggal_penjualan'], $kode_transaksi = $penjualan['kode_transaksi'], $produk_id = $penjualan['produk_id'], $qty_penjualan = $penjualan['qty_penjualan']);
            $this->update_laporan($dc_id = $penjualan['dc_id'], $tanggal_penjualan = $penjualan['tanggal_penjualan'], $produk_id = $penjualan['produk_id'], $qty_penjualan = $penjualan['qty_penjualan']);
            return response()->json("Sukses ".$penjualan['kode_transaksi']);
        } else {
            return response()->json("Harap periksa input anda, pastikan tanggal penjualan = tanggal pembelian produk tersebut");
        }


    }
}
