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
    protected $totalSettlement = 0;
    protected $totalPending = 0;
    protected $totalFailed = 0;

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

        // Hitung total berdasarkan status
        $this->totalSettlement = $data->where('status', 'settlement')->sum('total');
        $this->totalPending    = $data->where('status', 'pending')->sum('total');
        $this->totalFailed     = $data->where('status', 'failed')->sum('total');

        // Map data transaksi
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

        // Tambahkan baris total berdasarkan status
        $mapped->push(['', '', 'Total Settlement (Sukses):', $this->totalSettlement, '', '']);
        $mapped->push(['', '', 'Total Pending (Belum Bayar):', $this->totalPending, '', '']);
        $mapped->push(['', '', 'Total Failed (Gagal):', $this->totalFailed, '', '']);

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
                // Bold heading
                $event->sheet->getStyle('A1:F1')->getFont()->setBold(true);

                $highestRow = $event->sheet->getHighestRow();

                // Format angka kolom D (Total)
                $event->sheet->getStyle("D2:D$highestRow")->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Bold baris total (3 baris terakhir)
                for ($i = $highestRow - 2; $i <= $highestRow; $i++) {
                    $event->sheet->getStyle("A{$i}:F{$i}")->getFont()->setBold(true);
                }
            },
        ];
    }
}
