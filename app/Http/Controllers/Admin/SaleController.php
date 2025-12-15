<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Events\PurchaseOutStock;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'sales';
        if($request->ajax()){
                    $sales = Sale::with(['product', 'product.purchase'])->latest();
                    return DataTables::of($sales)
                        ->filter(function ($query) use ($request) {
                            if ($request->has('search') && isset($request->search['value']) && $request->search['value'] !== '') {
                                $keyword = trim($request->search['value']);

                                $query->where(function($q) use ($keyword) {
                                    // If the keyword is numeric, match quantity exactly or cast to text for partial matches
                                    if (is_numeric($keyword)) {
                                        // If the user searches a number, match quantity exactly only
                                        $q->where('quantity', intval($keyword));
                                    } else {
                                        // Non-numeric: try partial match on quantity as text as well
                                        $q->whereRaw("CAST(quantity AS TEXT) ILIKE ?", ["%{$keyword}%"]);

                                        // Destination partial match
                                        $q->orWhere('destination', 'ilike', "%{$keyword}%");

                                        // Search in related purchase.product (product name) - partial
                                        $q->orWhereHas('product.purchase', function($q2) use ($keyword) {
                                            $q2->where('product', 'ilike', "%{$keyword}%");
                                        });

                                        // Search in product.lote
                                        $q->orWhereHas('product', function($q3) use ($keyword) {
                                            $q3->where('lote', 'ilike', "%{$keyword}%");
                                        });
                                    }
                                });
                            }
                        })
                        ->addIndexColumn()
                        ->addColumn('product',function($sale){
                            $image = '';
                            if(!empty($sale->product)){
                                $image = null;
                                if(!empty($sale->product->purchase) && !empty($sale->product->purchase->image)){
                                    $image = '<span class="avatar avatar-sm mr-2">
                                    <img class="avatar-img" src="'.asset("storage/purchases/".$sale->product->purchase->image).'" alt="image">
                                    </span>';
                                }
                                // Prefer the product_name stored on the Product model; fallback to the purchase.product label
                                $label = $sale->product->product_name ?? optional($sale->product->purchase)->product ?? '';
                                return $label . ' ' . $image;
                            }
                        })
                    ->addColumn('destination', function($sale){
                        return isset($sale->destination) ? $sale->destination : '';
                    })
                    ->addColumn('price', function($sale){
                        if(!empty($sale->product) && isset($sale->product->price)){
                            $v = $sale->product->price;
                            return (floor($v) == $v) ? (int)$v : rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');
                        }
                        return '';
                    })
                    ->addColumn('total_price',function($sale){                   
                        // format: no trailing .00, keep decimals when needed
                        $v = $sale->total_price;
                        return (floor($v) == $v) ? (int)$v : rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');
                    })
                    ->addColumn('date',function($row){
                        return date_format(date_create($row->created_at),'d M, Y');
                    })
                    ->addColumn('action', function ($row) {
                        $editbtn = '<a href="'.route("sales.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                        $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('sales.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                        if (!auth()->user()->hasPermissionTo('edit-sale')) {
                            $editbtn = '';
                        }
                        if (!auth()->user()->hasPermissionTo('destroy-sale')) {
                            $deletebtn = '';
                        }
                        $btn = $editbtn.' '.$deletebtn;
                        return $btn;
                    })
                    ->rawColumns(['product','action'])
                    ->make(true);

        }
        $products = Product::get();
        return view('admin.sales.index',compact(
            'title','products',
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create sales';
        // Compute products that have non-expired stock > 0
        $today = Carbon::now();
        $available = [];
        try{
            $purchases = Purchase::where(function($q) use ($today){
                $q->whereNull('expiry_date')->orWhereDate('expiry_date','>', $today);
            })->get();
            $sums = [];
            foreach($purchases as $pu){
                if(empty($pu->product)) continue;
                $key = Str::ascii(mb_strtolower(trim($pu->product)));
                if(!isset($sums[$key])) $sums[$key] = 0;
                $sums[$key] += (int) $pu->quantity;
            }

            $allProducts = Product::with('purchase')->get();
            foreach($allProducts as $p){
                $labels = [];
                if(!empty($p->product_name)) $labels[] = trim($p->product_name);
                if(!empty($p->purchase) && !empty($p->purchase->product)) $labels[] = trim($p->purchase->product);
                foreach($labels as $lab){
                    $k = Str::ascii(mb_strtolower($lab));
                    if(isset($sums[$k]) && $sums[$k] > 0){
                        $available[] = $p;
                        break;
                    }
                }
            }
        }catch(\Exception $e){
            $available = Product::with('purchase')->get();
        }

        $products = collect($available);
        return view('admin.sales.create',compact(
            'title','products'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'product'=>'required',
            'quantity'=>'required|integer|min:1',
            'destination' => 'required|string|max:255'
        ]);
        $sold_product = Product::find($request->product);
        $toSell = (int)$request->quantity;
        $notification = '';

        // Compute total available across non-expired purchases
        $today = \Illuminate\Support\Carbon::now();
        $purchases = Purchase::where(function($q) use ($today){
            $q->whereNull('expiry_date')->orWhereDate('expiry_date','>', $today);
        })->get()->filter(function($pu){
            return !empty($pu->product);
        });

        // Build map of normalized purchase label -> purchase objects
        $availableTotal = 0;
        $label = $sold_product->product_name ?? optional($sold_product->purchase)->product ?? null;
        if(!$label){
            $notification = notify("Producto no encontrado o no tiene etiqueta.", 'danger');
            return redirect()->back()->with($notification);
        }

        $normLabel = \Illuminate\Support\Str::ascii(mb_strtolower(trim($label)));
        $candidatePurchases = [];
        foreach($purchases as $pu){
            $pNorm = \Illuminate\Support\Str::ascii(mb_strtolower(trim($pu->product)));
            if($pNorm === $normLabel){
                $remaining = max(0, (int)$pu->quantity - (int)($pu->sold ?? 0));
                if($remaining > 0){
                    $candidatePurchases[] = $pu;
                    $availableTotal += $remaining;
                }
            }
        }

        if($toSell > $availableTotal){
            $notification = notify("No hay suficientes existencias para completar la salida.", 'danger');
            return redirect()->back()->with($notification);
        }

        // allocate the sale to purchases FIFO by entry_date (or id)
        $remain = $toSell;
        $allocations = [];
        usort($candidatePurchases, function($a,$b){
            return strtotime($a->entry_date) <=> strtotime($b->entry_date);
        });
        foreach($candidatePurchases as $pu){
            if($remain <= 0) break;
            $available = max(0, (int)$pu->quantity - (int)($pu->sold ?? 0));
            if($available <= 0) continue;
            $take = min($available, $remain);
            // increment sold
            $pu->sold = ((int)$pu->sold) + $take;
            $pu->save();
            $allocations[] = ['purchase_id' => $pu->id, 'quantity' => $take];
            $remain -= $take;
        }

        // create sale
        $total_price = (float) $toSell * (float) $sold_product->price;
        $sale = Sale::create([
            'product_id' => $request->product,
            'quantity' => $toSell,
            'total_price' => $total_price,
            'destination' => $request->destination ?? null,
        ]);

        // persist allocations
        foreach($allocations as $a){
            \Illuminate\Support\Facades\DB::table('sale_purchase_allocations')->insert([
                'sale_id' => $sale->id,
                'purchase_id' => $a['purchase_id'],
                'quantity' => $a['quantity'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $notification = notify("El Producto Ha Salido.");

        // Check low stock and notify only after a successful sale
        $remainingTotal = max(0, $availableTotal - $toSell);
        if ($remainingTotal <= 1 && $remainingTotal != 0) {
            // find a purchase batch with low remaining stock to include in notification
            $low = Purchase::whereRaw('(quantity - COALESCE(sold,0)) <= 1')->whereRaw('(quantity - COALESCE(sold,0)) > 0')->first();
            if($low){
                event(new PurchaseOutStock($low));
                $notification = notify("¡El producto se está agotando!", 'warning');
            }
        }

        return redirect()->route('sales.index')->with($notification);
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        $title = 'edit sale';
        // Similar logic as create: only show products that have non-expired stock
        $today = Carbon::now();
        $available = [];
        try{
            $purchases = Purchase::where(function($q) use ($today){
                $q->whereNull('expiry_date')->orWhereDate('expiry_date','>', $today);
            })->get();
            $sums = [];
            foreach($purchases as $pu){
                if(empty($pu->product)) continue;
                $key = Str::ascii(mb_strtolower(trim($pu->product)));
                if(!isset($sums[$key])) $sums[$key] = 0;
                $sums[$key] += (int) $pu->quantity;
            }

            $allProducts = Product::with('purchase')->get();
            foreach($allProducts as $p){
                $labels = [];
                if(!empty($p->product_name)) $labels[] = trim($p->product_name);
                if(!empty($p->purchase) && !empty($p->purchase->product)) $labels[] = trim($p->purchase->product);
                foreach($labels as $lab){
                    $k = Str::ascii(mb_strtolower($lab));
                    if(isset($sums[$k]) && $sums[$k] > 0){
                        $available[] = $p;
                        break;
                    }
                }
            }
        }catch(\Exception $e){
            $available = Product::with('purchase')->get();
        }

        $products = collect($available);
        return view('admin.sales.edit',compact(
            'title','sale','products'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        $this->validate($request,[
            'product'=>'required',
            'quantity'=>'required|integer|min:1',
            'destination' => 'required|string|max:255'
        ]);
        $newProduct = Product::find($request->product);
        $oldProduct = $sale->product;

        $notification = '';

        try {
            $updated = DB::transaction(function() use ($sale, $oldProduct, $newProduct, $request, &$notification) {
                $oldQty = (int) ($sale->quantity ?? 0);
                $newQty = (int) $request->quantity;

                // Revert previous allocations (if any)
                $oldAllocs = \Illuminate\Support\Facades\DB::table('sale_purchase_allocations')->where('sale_id', $sale->id)->get();
                foreach($oldAllocs as $oa){
                    $pu = Purchase::find($oa->purchase_id);
                    if($pu){
                        $pu->sold = max(0, ((int)$pu->sold) - (int)$oa->quantity);
                        $pu->save();
                    }
                }
                \Illuminate\Support\Facades\DB::table('sale_purchase_allocations')->where('sale_id', $sale->id)->delete();

                // Now attempt to allocate new quantity similarly to store()
                $toSell = $newQty;

                $today = \Illuminate\Support\Carbon::now();
                $purchases = Purchase::where(function($q) use ($today){
                    $q->whereNull('expiry_date')->orWhereDate('expiry_date','>', $today);
                })->get()->filter(function($pu){
                    return !empty($pu->product);
                });

                $label = $newProduct->product_name ?? optional($newProduct->purchase)->product ?? null;
                if(!$label){
                    throw new \Exception('Producto no encontrado o no tiene etiqueta.');
                }
                $normLabel = \Illuminate\Support\Str::ascii(mb_strtolower(trim($label)));
                $candidatePurchases = [];
                $availableTotal = 0;
                foreach($purchases as $pu){
                    $pNorm = \Illuminate\Support\Str::ascii(mb_strtolower(trim($pu->product)));
                    if($pNorm === $normLabel){
                        $remaining = max(0, (int)$pu->quantity - (int)($pu->sold ?? 0));
                        if($remaining > 0){
                            $candidatePurchases[] = $pu;
                            $availableTotal += $remaining;
                        }
                    }
                }

                if($toSell > $availableTotal){
                    throw new \Exception('Cantidad insuficiente en stock para la actualización.');
                }

                // allocate
                $remain = $toSell;
                $allocations = [];
                usort($candidatePurchases, function($a,$b){
                    return strtotime($a->entry_date) <=> strtotime($b->entry_date);
                });
                foreach($candidatePurchases as $pu){
                    if($remain <= 0) break;
                    $available = max(0, (int)$pu->quantity - (int)($pu->sold ?? 0));
                    if($available <= 0) continue;
                    $take = min($available, $remain);
                    $pu->sold = ((int)$pu->sold) + $take;
                    $pu->save();
                    $allocations[] = ['purchase_id' => $pu->id, 'quantity' => $take];
                    $remain -= $take;
                }

                // Update sale
                $total_price = (float) $newQty * (float) $newProduct->price;
                $sale->update([
                    'product_id' => $newProduct->id,
                    'quantity' => $newQty,
                    'total_price' => $total_price,
                    'destination' => $request->destination ?? null,
                ]);

                // persist allocations
                foreach($allocations as $a){
                    \Illuminate\Support\Facades\DB::table('sale_purchase_allocations')->insert([
                        'sale_id' => $sale->id,
                        'purchase_id' => $a['purchase_id'],
                        'quantity' => $a['quantity'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Check low stock similar to store()
                $remainingTotal = 0;
                $today = \Illuminate\Support\Carbon::now();
                $purchases = Purchase::where(function($q) use ($today){
                    $q->whereNull('expiry_date')->orWhereDate('expiry_date','>', $today);
                })->get();
                $label = $newProduct->product_name ?? optional($newProduct->purchase)->product ?? null;
                if($label){
                    $normLabel = \Illuminate\Support\Str::ascii(mb_strtolower(trim($label)));
                    foreach($purchases as $pu){
                        $pNorm = \Illuminate\Support\Str::ascii(mb_strtolower(trim($pu->product)));
                        if($pNorm === $normLabel){
                            $remainingTotal += max(0, (int)$pu->quantity - (int)($pu->sold ?? 0));
                        }
                    }
                }
                if ($remainingTotal <= 1 && $remainingTotal != 0) {
                    $low = Purchase::whereRaw('(quantity - COALESCE(sold,0)) <= 1')->whereRaw('(quantity - COALESCE(sold,0)) > 0')->first();
                    if($low){
                        event(new PurchaseOutStock($low));
                        $notification = notify("¡El producto se está agotando!");
                    }
                }
                $notification = $notification ?: notify("El producto ha sido actualizado.");

                return true;
            });

        } catch (\Exception $e) {
            $notification = notify("No hay suficientes existencias para completar la salida.", 'danger');
            return redirect()->back()->with($notification);
        }

        return redirect()->route('sales.index')->with($notification);
    }

    /**
     * Generate sales reports index
     *
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request){
        $title = 'reportes de Salidas';
        return view('admin.sales.reports',compact(
            'title'
        ));
    }

    /**
     * Generate sales report form post
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request){
        $this->validate($request,[
            'from_date' => 'required',
            'to_date' => 'required',
        ]);
        $title = 'Reportes de Salidas';
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $sales = Sale::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->get();
        return view('admin.sales.reports',compact(
            'sales','title','from_date','to_date'
        ));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $sale = Sale::with('product')->findOrFail($request->id);

        $deleted = DB::transaction(function() use ($sale) {
            // Revert allocations if present
            $allocs = \Illuminate\Support\Facades\DB::table('sale_purchase_allocations')->where('sale_id', $sale->id)->get();
            foreach($allocs as $a){
                try{
                    $pu = Purchase::find($a->purchase_id);
                    if($pu){
                        $pu->sold = max(0, ((int)$pu->sold) - (int)$a->quantity);
                        $pu->save();
                    }
                }catch(\Exception $e){
                    continue;
                }
            }
            // delete allocation rows
            \Illuminate\Support\Facades\DB::table('sale_purchase_allocations')->where('sale_id', $sale->id)->delete();

            return $sale->delete();
        });

        return response()->json(['success' => (bool) $deleted]);
    }
}
