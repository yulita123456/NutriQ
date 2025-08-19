<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DashboardSalesExport implements FromCollection, WithHeadings, WithEvents
{
    protected $tahun;
    protected $bulan;
    protected $totalSum = 0;

    public function __construct($tahun, $bulan)
    {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
    }

    public function collection()
    {
        $query = Transaction::with('user')
            ->where('status', 'settlement')
            ->whereYear('created_at', $this->tahun);

        if ($this->bulan) {
            $query->whereMonth('created_at', $this->bulan);
        }

        $data = $query->orderBy('created_at')->get();

        // Hitung total
        $this->totalSum = $data->sum('total');

        // Map data transaksi
        $mapped = $data->map(function ($trx) {
            return [
                $trx->created_at->format('d/m/Y'),
                $trx->order_id,
                $trx->user->name ?? '-',
                $trx->total,
            ];
        });

        // Tambahkan baris total di akhir
        $mapped->push([
            '', '', 'Total Penjualan:', $this->totalSum
        ]);

        return $mapped;
    }

    public function headings(): array
    {
        return ['Tanggal', 'Order ID', 'User', 'Total'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $highestRow = $event->sheet->getHighestRow();

                // Bold untuk heading
                $event->sheet->getStyle('A1:D1')->getFont()->setBold(true);

                // Format angka (rupiah) kolom total
                $event->sheet->getStyle("D2:D$highestRow")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Bold untuk baris terakhir (total)
                $event->sheet->getStyle("A$highestRow:D$highestRow")->getFont()->setBold(true);
            }
        ];
    }
}
