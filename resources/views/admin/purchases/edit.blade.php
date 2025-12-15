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
								<!-- Mostrar el nombre pero no permitir editarlo. Incluir campo oculto para enviar el valor. -->
								<input class="form-control" type="text" value="{{$purchase->product}}" disabled>
								<input type="hidden" name="product" value="{{$purchase->product}}">
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Categoría</label>
								<input class="form-control" type="text" value="{{ optional($purchase->category)->name ?? (optional($purchase->purchaseProduct->category)->name ?? '') }}" disabled>
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
											// Show the purchase's own lote first, fall back to old input or linked product lote
											$displayLote = old('lote') ?? ($purchase->lote ?? (isset($purchase->purchaseProduct) && isset($purchase->purchaseProduct->lote) ? $purchase->purchaseProduct->lote : ''));
										@endphp
										<div class="input-group">
											<div class="input-group-prepend">
												<div class="btn-group">
													<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
														<i class="fa fa-angle-left"></i>
													</button>
													<div class="dropdown-menu">
														@isset($existingLotes)
															@foreach($existingLotes as $lote)
																<a class="dropdown-item lote-item" href="#" data-lote="{{ $lote }}">{{ $lote }}</a>
															@endforeach
														@endisset
														@if(!isset($existingLotes) || count($existingLotes) === 0)
															<span class="dropdown-item disabled">No hay lotes</span>
														@endif
													</div>
												</div>
											</div>
											<input id="lote-input" class="form-control" type="text" name="lote" value="{{ $displayLote }}" placeholder="Escriba o seleccione un lote">
										</div>
										@error('lote')
											<span class="text-danger">{{ $message }}</span>
										@enderror
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
                                <label>Fecha de entrada <span class="text-danger">*</span></label>
								<input class="form-control" value="{{$purchase->entry_date}}" type="date" name="entry_date" required min="{{ \Carbon\Carbon::now(config('app.timezone'))->subDay()->toDateString() }}">
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
	<script>
		$(document).ready(function(){
			// Clicking an existing lote in dropdown sets the lote input value
			$(document).on('click', '.lote-item', function(e){
				e.preventDefault();
				var lote = $(this).data('lote');
				$('#lote-input').val(lote);
			});
		});
	</script>
@endpush
