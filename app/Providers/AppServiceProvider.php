<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL; // <<< TAMBAHKAN INI
use App\Models\Product;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Inject variabel $notifikasi ke semua view (layout admin dsb)
        View::composer('*', function ($view) {
            $lowStockProducts = Product::where('stock', '<', 5)->get();
            $notifikasi = [];
            foreach ($lowStockProducts as $p) {
                $notifikasi[] = [
                    'type' => 'low_stock',
                    'pesan' => "Stock produk <b>{$p->nama_produk}</b> tinggal {$p->stock}!",
                    'produk_id' => $p->id,
                ];
            }
            $view->with('notifikasi', $notifikasi);
        });

        // <<< TAMBAHKAN BLOK INI UNTUK MEMAKSA HTTPS >>>
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
        // <<< AKHIR BLOK INI >>>
    }
}
