@extends('admin.layouts.app')


@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Registrar Salida</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Panel</a></li>
		<li class="breadcrumb-item active">Registrar Salida</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
                <!-- Registrar venta -->
                <form method="POST" action="{{route('sales.store')}}">
					@csrf
					<div class="row form-row">
						<div class="col-12">
							<div class="form-group">
								<label>Producto <span class="text-danger">*</span></label>
								<select class="select2 form-select form-control" name="product"> 
									@foreach ($products as $product)
										@if (!empty($product->purchase))
											@if (!($product->purchase->quantity <= 0))
                                                <option disabled selected>Seleccionar producto</option>
												<option value="{{$product->id}}">{{$product->purchase->product}}</option>
											@endif
										@endif
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label>Cantidad</label>
								<input type="number" value="1" class="form-control" name="quantity">
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label>Destino <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="destination" required maxlength="255">
							</div>
						</div>
					</div>
					<button type="submit" class="btn btn-primary btn-block">Guardar cambios</button>
				</form>
                <!-- /Registrar venta -->
			</div>
		</div>
	</div>			
</div>
@endsection	


@push('page-js')
    
@endpush
