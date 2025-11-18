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
											<input class="form-control" type="text" name="product" >
											@error('product')
												<span class="text-danger">{{ $message }}</span>
											@enderror
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
											<label>Categoría <span class="text-danger">*</span></label>
											<select class="select2 form-select form-control" name="category"> 
												@foreach ($categories as $category)
													<option value="{{$category->id}}">{{$category->name}}</option>
												@endforeach
											</select>
											@error('category')
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
											<label>Lote <span class="text-danger">*</span></label>
											<input class="form-control" type="text" name="cost_price" value="{{ old('cost_price') }}">
											@error('cost_price')
												<span class="text-danger">{{ $message }}</span>
											@enderror
								</div>
							</div>

							<div class="col-lg-6">
								<div class="form-group">
											<label>Cantidad <span class="text-danger">*</span></label>
											<input class="form-control" type="text" name="quantity">
											@error('quantity')
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
										<input class="form-control" type="date" name="entry_date" required>
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
@endpush
