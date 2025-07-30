<?php
namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromCollection, WithHeadings
{
    protected $tahun;
    protected $bulan;
    protected $status;

    public function __construct($tahun = null, $bulan = null, $status = null)
    {
        // Pastikan tahun tidak pernah kosong/null
        $this->tahun = $tahun ?: date('Y');
        $this->bulan = $bulan;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Transaction::with('user')->orderBy('created_at', 'desc');

        // Filter jika bulan/tahun/status diisi
        if ($this->bulan) $query->whereMonth('created_at', $this->bulan);
        if ($this->tahun) $query->whereYear('created_at', $this->tahun);
        if ($this->status) $query->where('status', $this->status);

        // Ambil semua transaksi & format baris Excel-nya
        return $query->get()->map(function ($trx) {
            return [
                'Order ID' => $trx->order_id,
                'User'     => $trx->user->name ?? '-',
                'Email'    => $trx->user->email ?? '-',
                'Total'    => $trx->total,
                'Status'   => $trx->status,
                'Tanggal'  => $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-',
            ];
        });
    }

    public function headings(): array
    {
        return ['Order ID', 'User', 'Email', 'Total', 'Status', 'Tanggal'];
    }
}
