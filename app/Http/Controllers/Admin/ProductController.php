<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $title = 'products';
        if ($request->ajax()) {
            $products = Product::latest();

            // Precompute non-expired purchase sums grouped by normalized product label
            $today = Carbon::now();
            $nonExpiredSums = [];
            try{
                $purchases = \App\Models\Purchase::where(function($q) use ($today){
                    $q->whereNull('expiry_date')->orWhereDate('expiry_date','>', $today);
                })->get();
                foreach($purchases as $pu){
                    if(empty($pu->product)) continue;
                    $key = mb_strtolower(trim($pu->product));
                    if(!isset($nonExpiredSums[$key])) $nonExpiredSums[$key] = 0;
                    // Subtract sold to reflect available stock (do not mutate purchase.quantity)
                    $available = (int)$pu->quantity - (int)($pu->sold ?? 0);
                    $nonExpiredSums[$key] += max(0, $available);
                }
            }catch(\Exception $e){
                $nonExpiredSums = [];
            }

            return DataTables::of($products)
                ->addColumn('product',function($product){
                    $imageHtml = '';
                    if(!empty($product->purchase) && !empty($product->purchase->image)){
                        $imageHtml = '<span class="avatar avatar-sm mr-2">'
                            . '<img class="avatar-img" src="'.asset("storage/purchases/".$product->purchase->image).'" alt="image">'
                            . '</span>';
                    }
                    if(!empty($product->product_name)){
                        return $product->product_name . ' ' . $imageHtml;
                    }
                    if(!empty($product->purchase)){
                        return $product->purchase->product . ' ' . $imageHtml;
                    }
                    return null;
                })
                ->addColumn('category',function($product){
                    if(!empty($product->category) && !empty($product->category->name)){
                        return $product->category->name;
                    }
                    if(!empty($product->purchase) && !empty($product->purchase->category)){
                        return $product->purchase->category->name;
                    }
                    return null;
                })
                ->addColumn('price',function($product){
                    $v = $product->price;
                    return (floor($v) == $v) ? (int)$v : rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');
                })
                ->addColumn('quantity',function($product) use ($nonExpiredSums){
                    try{
                        $labels = [];
                        if(!empty($product->product_name)) $labels[] = trim($product->product_name);
                        if(!empty($product->purchase) && !empty($product->purchase->product)) $labels[] = trim($product->purchase->product);
                        if(empty($labels)) return 0;
                        foreach($labels as $lab){
                            $key = mb_strtolower($lab);
                            if(isset($nonExpiredSums[$key])){
                                return (int) $nonExpiredSums[$key];
                            }
                        }
                        return 0;
                    }catch(\Exception $e){
                        return 0;
                    }
                })
                ->addColumn('expiry_date',function($product){
                    if(!empty($product->purchase)){
                        return date_format(date_create($product->purchase->expiry_date),'d M, Y');
                    }
                })
                    ->addColumn('entry_date',function($product){
                        if(!empty($product->purchase)){
                            return $product->purchase->entry_date ? date_format(date_create($product->purchase->entry_date),'d M, Y') : '';
                        }
                    })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("products.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('products.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-product')) {
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

        return view('admin.products.index',compact('title'));
    }

    public function create()
    {
        $title = 'add product';
        $purchases = Purchase::get();
        $categories = \App\Models\Category::get();
        return view('admin.products.create',compact(
            'title','purchases','categories'
        ));
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'product_name'=>'required|max:200',
            'description'=>'nullable|max:255',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        Product::create([
            'purchase_id'=>null,
            'product_name'=>$request->product_name,
            'lote' => $request->lote ?? '',
            'description'=>$request->description,
            'category_id' => $request->category_id ?? null,
        ]);

        $notification = notify("Se ha añadido el producto.");
        return redirect()->route('products.index')->with($notification);
    }

    public function edit(Product $product)
    {
        $title = 'edit product';
        $purchases = Purchase::get();
        $categories = \App\Models\Category::get();
        return view('admin.products.edit',compact(
            'title','product','purchases','categories'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $this->validate($request,[
            'product_name'=>'required|max:200',
            'description'=>'nullable|max:255',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Capture old labels to update existing purchases that referenced this product by name
        $oldLabels = [];
        if(!empty($product->product_name)) $oldLabels[] = trim($product->product_name);
        if(!empty($product->purchase) && !empty($product->purchase->product)) $oldLabels[] = trim($product->purchase->product);
        $oldLabels = array_unique(array_filter($oldLabels));

        $product->update([
            'purchase_id'=>null,
            'product_name'=>$request->product_name,
            'lote' => $request->lote ?? $product->lote ?? '',
            'description'=>$request->description,
            'category_id' => $request->category_id ?? $product->category_id ?? null,
        ]);

        // Propagate name change to purchases that stored the old label
        try{
            $newName = trim($product->product_name);
            $normalizedOlds = array_map(function($l){ return \Illuminate\Support\Str::ascii(mb_strtolower(trim($l))); }, $oldLabels);
            foreach(\App\Models\Purchase::whereNotNull('product')->cursor() as $pu){
                try{
                    $pNorm = \Illuminate\Support\Str::ascii(mb_strtolower(trim((string)$pu->product)));
                    if(in_array($pNorm, $normalizedOlds, true)){
                        $pu->product = $newName;
                        $pu->save();
                    }
                }catch(\Exception $e){
                    continue;
                }
            }
        }catch(\Exception $e){
            // ignore propagation errors
        }

        $notification = notify('El producto ha sido actualizado.');
        return redirect()->route('products.index')->with($notification);
    }

    public function expired(Request $request){
        $title = "expired Products";
        if($request->ajax()){
            try{
                $today = Carbon::now();
            $expiredPurchases = \App\Models\Purchase::with('category','purchaseProduct')
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date','<=',$today);

            // If the migrations with hidden_from_expired exist, exclude hidden ones.
            try{
                if(\Illuminate\Support\Facades\Schema::hasColumn('purchases','hidden_from_expired')){
                    $expiredPurchases = $expiredPurchases->where(function($q){
                        $q->where('hidden_from_expired', false)->orWhereNull('hidden_from_expired');
                    });
                }
            }catch(\Exception $e){
                // If check fails, continue without filtering
            }

            $expiredPurchases = $expiredPurchases->get();

            return DataTables::of($expiredPurchases)
                ->addColumn('product', function($purchase){
                    $imageHtml = '';
                    if(!empty($purchase->image)){
                        $imageHtml = '<span class="avatar avatar-sm mr-2">'
                            . '<img class="avatar-img" src="'.asset("storage/purchases/".$purchase->image).'" alt="image">'
                            . '</span>';
                    }
                    return ($purchase->product ?: optional($purchase->purchaseProduct)->product) . ' ' . $imageHtml;
                })
                ->addColumn('category', function($purchase){
                    if(!empty($purchase->category)) return $purchase->category->name;
                    return optional($purchase->purchaseProduct->category)->name;
                })
                ->addColumn('quantity', function($purchase){
                    return (int) $purchase->quantity;
                })
                ->addColumn('action', function ($purchase) {
                    // Use a non-destructive hide action for the expired view if route exists
                    $route = \Illuminate\Support\Facades\Route::has('purchases.hideExpired') ? route('purchases.hideExpired', $purchase->id) : route('purchases.destroy', $purchase->id);
                    $deletebtn = '<a data-id="'.$purchase->id.'" data-route="'.$route.'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    return $deletebtn;
                })
                ->rawColumns(['product','action'])
                ->make(true);
            }catch(\Exception $e){
                // Return JSON error (DataTables will show an ajax error)
                return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
            }
        }

        return view('admin.products.expired',compact('title'));
    }

    public function outstock(Request $request){
        $title = "outstocked Products";
        if($request->ajax()){
            $today = Carbon::now();

            // Build map of non-expired available quantities per normalized product label
            $nonExpiredAvailable = [];
            try{
                $purchases = \App\Models\Purchase::where(function($q) use ($today){
                    $q->whereNull('expiry_date')->orWhereDate('expiry_date','>', $today);
                })->get();
                foreach($purchases as $pu){
                    if(empty($pu->product)) continue;
                    $key = mb_strtolower(trim($pu->product));
                    $available = (int)$pu->quantity - (int)($pu->sold ?? 0);
                    $available = max(0, $available);
                    if(!isset($nonExpiredAvailable[$key])) $nonExpiredAvailable[$key] = 0;
                    $nonExpiredAvailable[$key] += $available;
                }
            }catch(\Exception $e){
                $nonExpiredAvailable = [];
            }

            // Load all products and filter to those with no available (<=0) non-expired stock
            $products = Product::get()->filter(function($product) use ($nonExpiredAvailable){
                try{
                    $labels = [];
                    if(!empty($product->product_name)) $labels[] = trim($product->product_name);
                    if(!empty($product->purchase) && !empty($product->purchase->product)) $labels[] = trim($product->purchase->product);
                    if(empty($labels)) return true; // no label/purchase -> treat as out of stock
                    foreach($labels as $lab){
                        $key = mb_strtolower($lab);
                        if(isset($nonExpiredAvailable[$key]) && (int)$nonExpiredAvailable[$key] > 0){
                            return false; // has available stock
                        }
                    }
                    return true; // none of label variants has available stock
                }catch(\Exception $e){
                    return true;
                }
            })->values();

            return DataTables::of($products)
                ->addColumn('product',function($product){
                    $imageHtml = '';
                    if(!empty($product->purchase) && !empty($product->purchase->image)){
                        $imageHtml = '<span class="avatar avatar-sm mr-2">'
                            . '<img class="avatar-img" src="'.asset("storage/purchases/".$product->purchase->image).'" alt="image">'
                            . '</span>';
                    }
                    if(!empty($product->product_name)){
                        return $product->product_name . ' ' . $imageHtml;
                    }
                    if(!empty($product->purchase)){
                        return $product->purchase->product . ' ' . $imageHtml;
                    }
                    return null;
                })
                ->addColumn('category',function($product){
                    if(!empty($product->category) && !empty($product->category->name)){
                        return $product->category->name;
                    }
                    if(!empty($product->purchase) && !empty($product->purchase->category)){
                        return $product->purchase->category->name;
                    }
                    return null;
                })
                ->addColumn('quantity',function($product) use ($nonExpiredAvailable){
                    try{
                        $labels = [];
                        if(!empty($product->product_name)) $labels[] = trim($product->product_name);
                        if(!empty($product->purchase) && !empty($product->purchase->product)) $labels[] = trim($product->purchase->product);
                        $sum = 0;
                        foreach($labels as $lab){
                            $key = mb_strtolower($lab);
                            $sum += isset($nonExpiredAvailable[$key]) ? (int)$nonExpiredAvailable[$key] : 0;
                        }
                        return (int) $sum;
                    }catch(\Exception $e){
                        return 0;
                    }
                })
                ->rawColumns(['product'])
                ->make(true);
        }

        return view('admin.products.outstock',compact('title'));
    }

    public function destroy(Request $request)
    {
        $product = Product::findOrFail($request->id);
        try{
            $labels = [];
            if(!empty($product->product_name)) $labels[] = trim($product->product_name);
            if(!empty($product->purchase) && !empty($product->purchase->product)) $labels[] = trim($product->purchase->product);
            $labels = array_unique(array_filter($labels));
            // Normalize labels to ASCII lowercase (strip accents) for robust matching
            $normalizedLabels = array_map(function($l){
                return Str::ascii(mb_strtolower(trim($l)));
            }, $labels);

            // Iterate purchases with a product value and compare normalized values in PHP
            $query = \App\Models\Purchase::whereNotNull('product');
            foreach($query->cursor() as $pu){
                try{
                    $pNorm = Str::ascii(mb_strtolower(trim((string)$pu->product)));
                    if(in_array($pNorm, $normalizedLabels, true)){
                        $pu->product = null;
                        $pu->category_id = null;
                        $pu->save();
                    }
                }catch(\Exception $e){
                    // skip problematic row
                    continue;
                }
            }
        }catch(\Exception $e){
            // ignore and proceed to delete product
        }

        return $product->delete();
    }
}
