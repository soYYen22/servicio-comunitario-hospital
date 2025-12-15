@extends('admin.layouts.app')

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Editar Salida</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Panel Principal</a></li>
		<li class="breadcrumb-item active">Editar Salida</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
                <!-- Editar Venta -->
                <form method="POST" action="{{route('sales.update',$sale)}}">
					@csrf
					@method("PUT")
					<div class="row form-row">
						<div class="col-12">
							<div class="form-group">
								<label>Producto <span class="text-danger">*</span></label>
								@php
									$productLabel = $sale->product->product_name ?? optional($sale->product->purchase)->product ?? '';
									$productId = $sale->product->id ?? '';
								@endphp
								<input type="text" class="form-control" value="{{ $productLabel }}" disabled>
								<input type="hidden" name="product" value="{{ $productId }}">
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label>Cantidad</label>
								<input type="number" class="form-control edit_quantity" value="{{$sale->quantity ?? '1'}}" name="quantity">
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label>Destino <span class="text-danger">*</span></label>
								<input type="text" class="form-control edit_destination" value="{{$sale->destination ?? ''}}" name="destination" required maxlength="255">
							</div>
						</div>
					</div>
					<button type="submit" class="btn btn-primary btn-block">Guardar Cambios</button>
				</form>
                <!--/ Editar Venta -->
			</div>
		</div>
	</div>			
</div>
@endsection	

@push('page-js')
    
@endpush
