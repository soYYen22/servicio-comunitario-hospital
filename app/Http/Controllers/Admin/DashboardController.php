<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(){
        $title = 'dashboard';
        $total_purchases = Purchase::where('expiry_date','!=',Carbon::now())->count();
        $total_product_entries = Purchase::distinct('product')->count('product');
        $total_suppliers = Supplier::count();
        $total_sales = Sale::count();
        
        $pieChart = app()->chartjs
                ->name('pieChart')
                ->type('pie')
                ->size(['width' => 400, 'height' => 200])
                ->labels(['Total Purchases', 'Total Suppliers','Total Sales'])
                ->datasets([
                    [
                        'backgroundColor' => ['#FF6384', '#36A2EB','#7bb13c'],
                        'hoverBackgroundColor' => ['#FF6384', '#36A2EB','#7bb13c'],
                        'data' => [$total_purchases, $total_suppliers,$total_sales]
                    ]
                ])
                ->options([]);
        
        // Mostrar la cantidad de medicamentos distintos que están vencidos.
        // Contamos valores distintos de la columna `product` entre las compras vencidas.
        $total_expired_products = (int) DB::table('purchases')
            ->whereDate('expiry_date', '<=', Carbon::today())
            ->whereNotNull('product')
            ->selectRaw("COALESCE(COUNT(DISTINCT NULLIF(TRIM(product), '')), 0) as total")
            ->value('total');
        $latest_sales = Sale::whereDate('created_at','=',Carbon::now())->get();
        $out_of_stock = Purchase::where('quantity', '<=', 0)->count();
        return view('admin.dashboard',compact(
            'title','pieChart','total_expired_products',
            'latest_sales','out_of_stock','total_product_entries'
        ));
    }
}
