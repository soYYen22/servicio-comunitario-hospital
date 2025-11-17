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
                $sales = Sale::with('product')->latest();
                return DataTables::of($sales)
                    ->addIndexColumn()
                    ->addColumn('product',function($sale){
                        $image = '';
                        if(!empty($sale->product)){
                            $image = null;
                            if(!empty($sale->product->purchase->image)){
                                $image = '<span class="avatar avatar-sm mr-2">
                                <img class="avatar-img" src="'.asset("storage/purchases/".$sale->product->purchase->image).'" alt="image">
                                </span>';
                            }
                            return $sale->product->purchase->product. ' ' . $image;
                        }                 
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
        $products = Product::get();
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
            'quantity'=>'required|integer|min:1'
        ]);
        $sold_product = Product::find($request->product);
        
        /**update quantity of
            sold item from
         purchases
        **/
        $purchased_item = Purchase::find($sold_product->purchase->id);
        $new_quantity = ($purchased_item->quantity) - ($request->quantity);
        $notification = '';
        if (!($new_quantity < 0)){

            $purchased_item->update([
                'quantity'=>$new_quantity,
            ]);

            /**
             * calcualting item's total price
            **/
            $total_price = (float) $request->quantity * (float) $sold_product->price;
            Sale::create([
                'product_id'=>$request->product,
                'quantity'=>$request->quantity,
                'total_price'=>$total_price,
            ]);

            $notification = notify("El Producto Ha Salido.");
        } 
        if($new_quantity <=1 && $new_quantity !=0){
            // send notification 
            $product = Purchase::where('quantity', '<=', 1)->first();
            event(new PurchaseOutStock($product));
            // end of notification 
            $notification = notify("¡El producto se está agotando!");
            
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
        $products = Product::get();
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
            'quantity'=>'required|integer|min:1'
        ]);
        $this->validate($request, [
            'product' => 'required',
            'quantity' => 'required|integer|min:1'
        ]);

        $newProduct = Product::find($request->product);
        $oldProduct = $sale->product;

        $notification = '';

        $updated = DB::transaction(function() use ($sale, $oldProduct, $newProduct, $request, &$notification) {
            $oldQty = (int) ($sale->quantity ?? 0);
            $newQty = (int) $request->quantity;

            // If product changed: restore old purchase stock, then deduct from new purchase
            if (!empty($oldProduct) && $oldProduct->purchase) {
                $oldPurchase = $oldProduct->purchase;
                $oldPurchase->update([
                    'quantity' => ($oldPurchase->quantity ?? 0) + $oldQty,
                ]);
            }

            if (empty($newProduct) || empty($newProduct->purchase)) {
                throw new \Exception('Producto o compra asociada no encontrada.');
            }

            $newPurchase = $newProduct->purchase;

            // Compute final quantity after applying the update: start from current purchase quantity
            // Current purchase quantity already reflects previous sales, so add back oldQty only if product didn't change.
            // Simpler and safe approach: set final = current + (oldProduct == newProduct ? oldQty : 0) - newQty
            $finalQuantity = ($newPurchase->quantity ?? 0) - $newQty;

            // If the product is the same as before, we've already restored oldQty above, so finalQuantity is correct.
            // If the product changed, oldQty was restored to oldPurchase, and newPurchase does not include oldQty.
            if ($finalQuantity < 0) {
                throw new \Exception('Cantidad insuficiente en stock para la actualización.');
            }

            $newPurchase->update([
                'quantity' => $finalQuantity,
            ]);

            // Update sale
            $total_price = (float) $newQty * (float) $newProduct->price;
            $sale->update([
                'product_id' => $newProduct->id,
                'quantity' => $newQty,
                'total_price' => $total_price,
            ]);

            if($finalQuantity <= 1 && $finalQuantity != 0){
                $productLow = Purchase::where('quantity', '<=', 1)->first();
                event(new PurchaseOutStock($productLow));
                $notification = notify("¡El producto se está agotando!");
            } else {
                $notification = notify("El producto ha sido actualizado.");
            }

            return true;
        });

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
        $sales = Sale::whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))->get();
        return view('admin.sales.reports',compact(
            'sales','title'
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
        $sale = Sale::with('product.purchase')->findOrFail($request->id);

        $deleted = DB::transaction(function() use ($sale) {
            if (!empty($sale->product) && !empty($sale->product->purchase)) {
                $purchase = $sale->product->purchase;
                $purchase->update([
                    'quantity' => ($purchase->quantity ?? 0) + ($sale->quantity ?? 0),
                ]);
            }

            return $sale->delete();
        });

        return response()->json(['success' => (bool) $deleted]);
    }
}
