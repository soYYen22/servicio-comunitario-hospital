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
                    if(!empty($purchase->purchaseProduct)){
                        return $purchase->purchaseProduct->lote;
                    }
                    return '';
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
        return view('admin.purchases.create',compact(
            'title','categories','suppliers'
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
                'product'=>'required|max:200',
                'category'=>'required',
                'quantity'=>'required|min:1',
                'expiry_date'=>'required',
                'supplier'=>'required',
                'lot'=>'nullable|numeric',
                'image'=>'file|image|mimes:jpg,jpeg,png,gif',
                'entry_date' => ['required', 'date', 'after_or_equal:'.date('Y-m-d')],
            ], [
                'entry_date.after_or_equal' => 'La fecha de entrada no puede ser menor a hoy.',
            ]);
        $imageName = null;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
        Purchase::create([
              'product'=>$request->product,
              'category_id'=>$request->category,
              'supplier_id'=>$request->supplier,
              'cost_price'=>$request->cost_price,
              'quantity'=>$request->quantity,
              'expiry_date'=>$request->expiry_date,
              'entry_date'=>$request->entry_date ?? date('Y-m-d'),
              'image'=>$imageName,
        ]);
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
        return view('admin.purchases.edit',compact(
            'title','purchase','categories','suppliers'
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
            $this->validate($request,[
                'product'=>'required|max:200',
                'category'=>'required',
                'quantity'=>'required|min:1',
                'expiry_date'=>'required',
                'supplier'=>'required',
                'cost_price'=>'nullable|numeric',
                'image'=>'file|image|mimes:jpg,jpeg,png,gif',
                'entry_date' => ['required', 'date'],
            ]);
        $imageName = $purchase->image;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
        $purchase->update([
            'product'=>$request->product,
            'category_id'=>$request->category,
            'supplier_id'=>$request->supplier,
            'quantity'=>$request->quantity,
            'expiry_date'=>$request->expiry_date,
                'entry_date'=>$request->entry_date,
            'image'=>$imageName,
        ]);

        // Si existe un Product relacionado, actualiza su campo 'lote' con el valor proporcionado.
        // Acepta tanto el campo 'lote' (vista en español) como 'lot' (compatibilidad previa).
        if($purchase->purchaseProduct){
            $loteValue = null;
            if($request->filled('lote')){
                $loteValue = $request->lote;
            } elseif($request->filled('lot')){
                $loteValue = $request->lot;
            }

            if(!is_null($loteValue)){
                $purchase->purchaseProduct()->update([
                    'lote' => $loteValue,
                ]);
            }
        }
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
        $purchases = Purchase::whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))->get();
        return view('admin.purchases.reports',compact(
            'purchases','title'
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
        return Purchase::findOrFail($request->id)->delete();
    }
}
