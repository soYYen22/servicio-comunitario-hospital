<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use QCod\AppSettings\Setting\AppSettings;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'purchases';
        if($request->ajax()){
            $purchases = Purchase::with('purchaseProduct','category','supplier')->get();
            return DataTables::of($purchases)
                ->addColumn('product',function($purchase){
                    $image = '';
                    if(!empty($purchase->image)){
                        $image = '<span class="avatar avatar-sm mr-2">
                        <img class="avatar-img" src="'.asset("storage/purchases/".$purchase->image).'" alt="product">
                        </span>';
                    }
                    return $purchase->product.' ' . $image;
                })
                ->addColumn('category',function($purchase){
                    if(!empty($purchase->category)){
                        return $purchase->category->name;
                    }
                })
                // Agregar columna lote igual que en productos
                ->addColumn('lote',function($purchase){
                    // Prefer the lote stored on the purchase itself if present
                    if(!empty($purchase->lote)){
                        return $purchase->lote;
                    }

                    // Then prefer the lote on the linked product (legacy behavior)
                    if(!empty($purchase->purchaseProduct) && !empty($purchase->purchaseProduct->lote)){
                        return $purchase->purchaseProduct->lote;
                    }

                    // Fallback: search products table by product_name or linked purchase product
                    try{
                        $fallback = \App\Models\Product::where('product_name', $purchase->product)
                            ->orWhereHas('purchase', function($q) use ($purchase){
                                $q->where('product', $purchase->product);
                            })->value('lote');
                        return $fallback ?: '';
                    }catch(\Exception $e){
                        return '';
                    }
                })
                ->addColumn('supplier',function($purchase){
                    return $purchase->supplier->name;
                })
                ->addColumn('expiry_date',function($purchase){
                    return date_format(date_create($purchase->expiry_date),'d M, Y');
                })
                    ->addColumn('entry_date',function($purchase){
                        return $purchase->entry_date ? date_format(date_create($purchase->entry_date),'d M, Y') : '';
                    })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("purchases.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('purchases.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-purchase')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    $btn = $editbtn.' '.$deletebtn;
                    return $btn;
                })
                ->rawColumns(['product','action'])
                ->make(true);
        }
        return view('admin.purchases.index',compact(
            'title'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        $products = \App\Models\Product::with('purchase')->get();
        // collect existing lotes so user can pick one when creating a purchase
        $existingLotes = \App\Models\Purchase::whereNotNull('lote')->pluck('lote')->filter()->unique()->values();
        return view('admin.purchases.create',compact(
            'title','categories','suppliers','products','existingLotes'
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
            $minDate = Carbon::now(config('app.timezone'))->subDay()->toDateString();
            $this->validate($request,[
                'product'=>'required|exists:products,id',
                'quantity'=>'required|min:1',
                'expiry_date'=>'required',
                'supplier'=>'required',
                    'lote'=>'nullable|string|max:255',
                'image'=>'file|image|mimes:jpg,jpeg,png,gif',
                'entry_date' => ['required', 'date', 'after_or_equal:'.$minDate],
            ], [
                'entry_date.after_or_equal' => 'La fecha de entrada no puede ser menor a ' . $minDate,
            ]);
        $imageName = null;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
          // Obtener label del producto seleccionado (por id) para guardarlo en la compra
          $productModel = \App\Models\Product::with('purchase')->find($request->product);
          $productLabel = $productModel ? ($productModel->product_name ?? optional($productModel->purchase)->product) : $request->product;

                    $purchase = Purchase::create([
                            'product'=>$productLabel,
                            'category_id'=> $productModel->category_id ?? null,
                            'supplier_id'=>$request->supplier,
                            'cost_price'=>$request->cost_price,
                            'quantity'=>$request->quantity,
                            'expiry_date'=>$request->expiry_date,
                            'entry_date'=>$request->entry_date ?? date('Y-m-d'),
                            'image'=>$imageName,
                            'lote' => $request->lote ?? null,
                ]);

        // Vincular el registro en `products` (si existe) a esta nueva compra para
        // que la columna 'lote' mostrada en la lista de entradas refleje el valor
        // guardado en la tabla `products`.
        try {
            // productModel ya fue obtenido arriba (por id). Si existe, vincularlo.
            if(isset($productModel) && $productModel){
                $productModel->purchase_id = $purchase->id;
                // Do NOT overwrite other products' lote when creating a new purchase.
                // We keep product.lote untouched to avoid changing historical entries.
                $productModel->save();
            }
        } catch (\Exception $e) {
            // No detener el flujo si hay algún problema al vincular el producto.
        }
        $notifications = notify("Se ha añadido la Entrada");
        return redirect()->route('purchases.index')->with($notifications);
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Purchase $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        $title = 'edit purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        $existingLotes = \App\Models\Purchase::whereNotNull('lote')->pluck('lote')->filter()->unique()->values();
        return view('admin.purchases.edit',compact(
            'title','purchase','categories','suppliers','existingLotes'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Purchase $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Purchase $purchase)
    {
            $minDate = Carbon::now(config('app.timezone'))->subDay()->toDateString();
            $this->validate($request,[
                'product'=>'required|max:200',
                'quantity'=>'required|min:1',
                'expiry_date'=>'required',
                'supplier'=>'required',
                'cost_price'=>'nullable|numeric',
                'image'=>'file|image|mimes:jpg,jpeg,png,gif',
                'entry_date' => ['required', 'date', 'after_or_equal:'.$minDate],
            ], [
                'entry_date.after_or_equal' => 'La fecha de entrada no puede ser menor a ' . $minDate,
            ]);
        $imageName = $purchase->image;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
        // Prevent reducing purchase quantity below already sold amount
        $sold = (int) ($purchase->sold ?? 0);
        $newQty = (int) $request->quantity;
        if($newQty < $sold){
            $notifications = notify("No se puede establecer la cantidad a {$newQty} porque ya hay {$sold} salidas asociadas.", 'danger');
            return redirect()->back()->with($notifications);
        }
        $purchase->update([
            'product'=>$request->product,
            'supplier_id'=>$request->supplier,
            'quantity'=>$request->quantity,
            'expiry_date'=>$request->expiry_date,
                'entry_date'=>$request->entry_date,
            'image'=>$imageName,
            'lote' => ($request->filled('lote') ? $request->lote : ($request->filled('lot') ? $request->lot : $purchase->lote)),
        ]);
        $notifications = notify("Entrada Actualizada");
        return redirect()->route('purchases.index')->with($notifications);
    }

    public function reports(){
        $title ='purchase reports';
        return view('admin.purchases.reports',compact('title'));
    }

    public function generateReport(Request $request){
        $this->validate($request,[
            'from_date' => 'required',
            'to_date' => 'required'
        ]);
        $title = 'Reportes De Entradas';
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $purchases = Purchase::with('purchaseProduct','category','supplier')
            ->whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->get();

        // Construir mapa de lotes por etiqueta de producto (product_name o purchase->product)
        $loteByName = [];
        try{
            $allProducts = \App\Models\Product::with('purchase')->get();
            foreach($allProducts as $p){
                $label = $p->product_name ?? optional($p->purchase)->product ?? null;
                if($label && isset($p->lote) && $p->lote !== null){
                    // si hay múltiples, mantener el primero encontrado
                    if(!isset($loteByName[$label])){
                        $loteByName[$label] = $p->lote;
                    }
                }
            }
        }catch(\Exception $e){
            $loteByName = [];
        }

        return view('admin.purchases.reports',compact(
            'purchases','title','from_date','to_date','loteByName'
        ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $purchase = Purchase::findOrFail($request->id);

        // Desvincular productos que referencian esta compra para evitar borrado por cascada
        try{
            \App\Models\Product::where('purchase_id', $purchase->id)->update(['purchase_id' => null]);
        }catch(\Exception $e){
            // no bloquear el borrado si falla la desvinculación
        }

        return $purchase->delete();
    }

    /**
     * Hide purchase from expired listing only (non-destructive).
     */
    public function hideFromExpired(Request $request)
    {
        // Ensure the DB has the column; if not, return informative error so developer can run migrations
        try{
            if(!\Illuminate\Support\Facades\Schema::hasColumn('purchases','hidden_from_expired')){
                return response()->json(['ok' => false, 'message' => 'Migration missing: run php artisan migrate to add hidden_from_expired column.'], 400);
            }
        }catch(\Exception $e){
            return response()->json(['ok' => false, 'message' => 'Schema check failed.'], 500);
        }

        $purchase = Purchase::findOrFail($request->id);
        $purchase->hidden_from_expired = true;
        $purchase->save();
        return response()->json(['ok' => true]);
    }
}
