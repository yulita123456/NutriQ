<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\LogAktivitas;
use Carbon\Carbon;
use DateTime;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DashboardRekapExport;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan'); // bisa null

        // Statistik utama
        $productCount = Product::count();
        $userCount = User::count();
        $totalIncome = Transaction::where('status', 'settlement')->sum('total');

        // Data untuk grafik penjualan
        $salesLabels = [];
        $salesValues = [];

        if ($bulan) {
            $lastDay = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
            for ($tanggal = 1; $tanggal <= $lastDay; $tanggal++) {
                $salesLabels[] = str_pad($tanggal, 2, '0', STR_PAD_LEFT);
                $salesValues[] = Transaction::whereYear('created_at', $tahun)
                    ->whereMonth('created_at', $bulan)
                    ->whereDay('created_at', $tanggal)
                    ->where('status', 'settlement')
                    ->sum('total');
            }
        } else {
            for ($b = 1; $b <= 12; $b++) {
                $salesLabels[] = DateTime::createFromFormat('!m', $b)->format('M');
                $salesValues[] = Transaction::whereYear('created_at', $tahun)
                    ->whereMonth('created_at', $b)
                    ->where('status', 'settlement')
                    ->sum('total');
            }
        }

        // Ambil 5 log aktivitas terbaru untuk preview di dashboard
        $latestLogs = LogAktivitas::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Passing semua data ke view
        return view('admin.dashboard', compact(
            'productCount', 'userCount', 'totalIncome',
            'salesLabels', 'salesValues', 'tahun', 'bulan', 'latestLogs'
        ));
    }

    public function exportExcel(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan');

        return Excel::download(
            new \App\Exports\DashboardSalesExport($tahun, $bulan),
            "penjualan_{$tahun}" . ($bulan ? "_{$bulan}" : "") . ".xlsx"
        );
    }

    public function exportRekap(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan');

        $namaFile = $bulan
            ? "rekap_pemasukan_{$tahun}_{$bulan}.xlsx"
            : "rekap_pemasukan_{$tahun}.xlsx";

        return Excel::download(
            new DashboardRekapExport($tahun, $bulan),
            $namaFile
        );
    }
}
