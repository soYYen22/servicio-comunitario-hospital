@extends('admin.layouts.app')

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Editar Entrada</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Panel</a></li>
		<li class="breadcrumb-item active">Editar Entrada</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
			
			<!-- Editar compra -->
			<form method="post" enctype="multipart/form-data" autocomplete="off" action="{{route('purchases.update',$purchase)}}">
				@csrf
				@method("PUT")
				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Nombre del medicamento <span class="text-danger">*</span></label>
								<input class="form-control" type="text" value="{{$purchase->product}}" name="product">
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Categoría <span class="text-danger">*</span></label>
								<select class="select2 form-select form-control" name="category"> 
									@foreach ($categories as $category)
										<option {{($purchase->category->id == $category->id) ? 'selected': ''}} value="{{$category->id}}">{{$category->name}}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Proveedor <span class="text-danger">*</span></label>
								<select class="select2 form-select form-control" name="supplier"> 
									@foreach ($suppliers as $supplier)
										<option @if($purchase->supplier->id == $supplier->id) selected @endif value="{{$supplier->id}}">{{$supplier->name}}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
				</div>
				
				<div class="service-fields mb-3">
					<div class="row">
							<!-- Precio de costo -->
							<div class="col-lg-6">
								<div class="form-group">
									<label>Lote <span class="text-danger">*</span></label>
									@php
										$lotOld = old('lot');
										$lotFromProduct = isset($purchase->purchaseProduct) && isset($purchase->purchaseProduct->lote) ? $purchase->purchaseProduct->lote : null;
										if($lotOld !== null) {
											$displayLot = $lotOld;
										} elseif($lotFromProduct !== null) {
											$displayLot = $lotFromProduct;
										} else {
											$displayLot = '';
										}
									@endphp
									<input class="form-control" value="{{ $displayLot }}" type="text" name="lot">
								</div>
							</div>

							<div class="col-lg-6">
								<div class="form-group">
									<label>Cantidad <span class="text-danger">*</span></label>
									<input class="form-control" value="{{$purchase->quantity}}" type="text" name="quantity">
								</div>
							</div>
					</div>
				</div>

				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>Fecha de vencimiento <span class="text-danger">*</span></label>
								<input class="form-control" value="{{$purchase->expiry_date}}" type="date" name="expiry_date">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label>Imagen del medicamento</label>
								<input type="file" name="image" value="{{$purchase->image}}" class="form-control">
							</div>
						</div>
					</div>
				</div>
				
				<div class="submit-section">
					<button class="btn btn-primary submit-btn" type="submit">Guardar</button>
				</div>
			</form>
			<!-- /Editar compra -->

			</div>
		</div>
	</div>			
</div>
@endsection	

@push('page-js')
	<!-- Select2 JS -->
	<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
@endpush
