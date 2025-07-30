<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DashboardSalesExport implements FromCollection, WithHeadings
{
    protected $tahun;
    protected $bulan;

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

        return $query->orderBy('created_at')
            ->get()
            ->map(function ($trx) {
                return [
                    'Tanggal' => $trx->created_at->format('d/m/Y'),
                    'Order ID' => $trx->order_id,
                    'User'     => $trx->user->name ?? '-',
                    'Total'    => $trx->total,
                ];
            });
    }

    public function headings(): array
    {
        return ['Tanggal', 'Order ID', 'User', 'Total'];
    }
}
