@extends('admin.layouts.app')

@push('page-css')
	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Agregar Entradas</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Panel</a></li>
		<li class="breadcrumb-item active">Agregar Entradas</li>
	</ul>
</div>
@endpush


@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
				
				<!-- Agregar medicina -->
				<form method="post" enctype="multipart/form-data" autocomplete="off" action="{{route('purchases.store')}}">
					@csrf
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group">
											<label>Nombre del medicamento <span class="text-danger">*</span></label>
											<select class="select2 form-select form-control" name="product">
												<option value="">Seleccione un producto</option>
												@foreach($products as $p)
													@php
														$label = $p->product_name ?? optional($p->purchase)->product ?? '';
													@endphp
													<option value="{{ $p->id }}">{{ $label }}</option>
												@endforeach
											</select>
											@error('product')
												<span class="text-danger">{{ $message }}</span>
											@enderror
								</div>
							</div>
                            
							<div class="col-lg-4">
								<div class="form-group">
											<label>Proveedor <span class="text-danger">*</span></label>
											<select class="select2 form-select form-control" name="supplier"> 
												@foreach ($suppliers as $supplier)
													<option value="{{$supplier->id}}">{{$supplier->name}}</option>
												@endforeach
											</select>
											@error('supplier')
												<span class="text-danger">{{ $message }}</span>
											@enderror
								</div>
							</div>
						</div>
					</div>
					
					<div class="service-fields mb-3">
						<div class="row">
							<!-- Precio de costo -->


							<div class="col-lg-6">
								<div class="form-group">
											<label>Cantidad <span class="text-danger">*</span></label>
											<input class="form-control" type="text" name="quantity">
											@error('quantity')
												<span class="text-danger">{{ $message }}</span>
											@enderror
								</div>
							</div>

								<div class="col-lg-6">
									<div class="form-group">
										<label>Lote</label>
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
											<input id="lote-input" class="form-control" type="text" name="lote" value="{{ old('lote') }}" placeholder="Escriba o seleccione un lote">
										</div>
										@error('lote')
											<span class="text-danger">{{ $message }}</span>
										@enderror
									</div>
								</div>
						</div>
					</div>

					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
											<label>Fecha de vencimiento <span class="text-danger">*</span></label>
											<input class="form-control" type="date" name="expiry_date">
											@error('expiry_date')
												<span class="text-danger">{{ $message }}</span>
											@enderror
								</div>
							</div>
	                        <div class="col-lg-6">
	                            <div class="form-group">
										<label>Fecha de entrada <span class="text-danger">*</span></label>
										<input class="form-control" type="date" name="entry_date" required min="{{ \Carbon\Carbon::now(config('app.timezone'))->subDay()->toDateString() }}">
										@error('entry_date')
											<span class="text-danger">{{ $message }}</span>
										@enderror
	                            </div>
	                        </div>
							<div class="col-lg-6">
								<div class="form-group">
											<label>Imagen del medicamento</label>
											<input type="file" name="image" class="form-control">
											@error('image')
												<span class="text-danger">{{ $message }}</span>
											@enderror
								</div>
							</div>
						</div>
					</div>
					
					<div class="submit-section">
						<button class="btn btn-primary submit-btn" type="submit">Guardar</button>
					</div>
				</form>
				<!-- /Agregar medicina -->

			</div>
		</div>
	</div>			
</div>
@endsection

@push('page-js')
	<!-- Datetimepicker JS -->
	<script src="{{asset('assets/js/moment.min.js')}}"></script>
	<script src="{{asset('assets/js/bootstrap-datetimepicker.min.js')}}"></script>	
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
