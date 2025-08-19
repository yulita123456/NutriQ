<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class TransactionsExport implements FromCollection, WithHeadings, WithEvents
{
    protected $tahun;
    protected $bulan;
    protected $status;
    protected $totalSum = 0; // untuk menyimpan total

    public function __construct($tahun = null, $bulan = null, $status = null)
    {
        $this->tahun = $tahun ?: date('Y');
        $this->bulan = $bulan;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Transaction::with('user')->orderBy('created_at', 'desc');

        if ($this->bulan) $query->whereMonth('created_at', $this->bulan);
        if ($this->tahun) $query->whereYear('created_at', $this->tahun);
        if ($this->status) $query->where('status', $this->status);

        $data = $query->get();

        // Hitung total semua transaksi
        $this->totalSum = $data->sum('total');

        // Map data untuk ditampilkan di Excel
        $mapped = $data->map(function ($trx) {
            return [
                $trx->order_id,
                $trx->user->name ?? '-',
                $trx->user->email ?? '-',
                $trx->total,
                $trx->status,
                $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-',
            ];
        });

        // Tambahkan baris kosong + total di bawahnya
        $mapped->push(['', '', 'Total Penjualan:', $this->totalSum, '', '']);

        return $mapped;
    }

    public function headings(): array
    {
        return ['Order ID', 'User', 'Email', 'Total', 'Status', 'Tanggal'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Format heading bold
                $event->sheet->getStyle('A1:F1')->getFont()->setBold(true);

                // Format angka di kolom Total (D)
                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("D2:D$highestRow")->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Bold pada baris total (baris terakhir)
                $event->sheet->getStyle("A$highestRow:F$highestRow")->getFont()->setBold(true);
            },
        ];
    }
}
