<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class DashboardRekapExport implements FromCollection, WithHeadings
{
    protected $tahun;
    protected $bulan;
    protected $status;

    /**
     * @param $tahun int
     * @param $bulan int|null
     * @param $status string|null
     */
    public function __construct($tahun, $bulan = null, $status = null)
    {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
        $this->status = $status ?: 'settlement'; // Default hanya transaksi "lunas"
    }

    public function collection()
    {
        $data = collect();

        if ($this->bulan) {
            // --- Rekap per tanggal ---
            $lastDay = Carbon::createFromDate($this->tahun, $this->bulan)->daysInMonth;

            foreach (range(1, $lastDay) as $tgl) {
                // Query transaksi hari ini
                $trx = Transaction::whereYear('created_at', $this->tahun)
                    ->whereMonth('created_at', $this->bulan)
                    ->whereDay('created_at', $tgl)
                    ->when($this->status, function($q) {
                        $q->where('status', $this->status);
                    });

                $totalTransaksi = $trx->count();
                $totalPemasukan = $trx->sum('total');
                $avgTransaksi = $totalTransaksi ? round($totalPemasukan / $totalTransaksi) : 0;
                $maxTransaksi = $trx->max('total') ?: 0;
                $minTransaksi = $trx->min('total') ?: 0;

                // Produk terlaris hari ini
                $topProduct = TransactionDetail::whereHas('transaction', function ($q) use ($tgl) {
                        $q->whereYear('created_at', $this->tahun)
                          ->whereMonth('created_at', $this->bulan)
                          ->whereDay('created_at', $tgl)
                          ->where('status', $this->status);
                    })
                    ->selectRaw('product_id, SUM(qty) as total_qty')
                    ->groupBy('product_id')
                    ->orderByDesc('total_qty')
                    ->first();

                $topProductName = ($topProduct && $topProduct->product)
                    ? $topProduct->product->nama_produk . ' (' . $topProduct->total_qty . ')'
                    : '-';

                $data->push([
                    'Tanggal' => sprintf('%02d-%02d-%d', $tgl, $this->bulan, $this->tahun),
                    'Total Transaksi' => $totalTransaksi,
                    'Total Pemasukan' => $totalPemasukan,
                    'Rata-rata Transaksi' => $avgTransaksi,
                    'Transaksi Terbesar' => $maxTransaksi,
                    'Transaksi Terkecil' => $minTransaksi,
                    'Produk Terlaris' => $topProductName,
                ]);
            }
        } else {
            // --- Rekap per bulan ---
            foreach (range(1, 12) as $b) {
                $trx = Transaction::whereYear('created_at', $this->tahun)
                    ->whereMonth('created_at', $b)
                    ->when($this->status, function($q) {
                        $q->where('status', $this->status);
                    });

                $totalTransaksi = $trx->count();
                $totalPemasukan = $trx->sum('total');
                $avgTransaksi = $totalTransaksi ? round($totalPemasukan / $totalTransaksi) : 0;
                $maxTransaksi = $trx->max('total') ?: 0;
                $minTransaksi = $trx->min('total') ?: 0;

                // Produk terlaris bulan ini
                $topProduct = TransactionDetail::whereHas('transaction', function ($q) use ($b) {
                        $q->whereYear('created_at', $this->tahun)
                          ->whereMonth('created_at', $b)
                          ->where('status', $this->status);
                    })
                    ->selectRaw('product_id, SUM(qty) as total_qty')
                    ->groupBy('product_id')
                    ->orderByDesc('total_qty')
                    ->first();

                $topProductName = ($topProduct && $topProduct->product)
                    ? $topProduct->product->nama_produk . ' (' . $topProduct->total_qty . ')'
                    : '-';

                $data->push([
                    'Bulan' => \DateTime::createFromFormat('!m', $b)->format('F') . " $this->tahun",
                    'Total Transaksi' => $totalTransaksi,
                    'Total Pemasukan' => $totalPemasukan,
                    'Rata-rata Transaksi' => $avgTransaksi,
                    'Transaksi Terbesar' => $maxTransaksi,
                    'Transaksi Terkecil' => $minTransaksi,
                    'Produk Terlaris' => $topProductName,
                ]);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return $this->bulan
            ? ['Tanggal', 'Total Transaksi', 'Total Pemasukan', 'Rata-rata Transaksi', 'Transaksi Terbesar', 'Transaksi Terkecil', 'Produk Terlaris']
            : ['Bulan', 'Total Transaksi', 'Total Pemasukan', 'Rata-rata Transaksi', 'Transaksi Terbesar', 'Transaksi Terkecil', 'Produk Terlaris'];
    }
}
