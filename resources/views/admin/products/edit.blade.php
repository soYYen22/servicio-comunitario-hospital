@extends('admin.layouts.app')

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Editar producto</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Panel</a></li>
		<li class="breadcrumb-item active">Editar producto</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
				
			<!-- Editar producto -->
				<form method="post" enctype="multipart/form-data" id="update_service" action="{{route('products.update',$product)}}">
					@csrf
                    @method("PUT")
					<div class="service-fields mb-3">
						<div class="row">
							
							<div class="col-lg-12">
								<div class="form-group">
									<label>Nombre del Medicamento <span class="text-danger">*</span></label>
									<input class="form-control" type="text" name="product_name" value="{{ old('product_name', $product->product_name ?? optional($product->purchase)->product ?? '') }}" placeholder="Escriba el nombre del medicamento">
								</div>
							</div>
							<div class="col-lg-12">
								<div class="form-group">
									<label>Categoría <span class="text-danger">*</span></label>
									<select class="select2 form-select form-control" name="category_id">
										<option value="">Seleccione una categoría</option>
										@foreach($categories as $category)
											<option value="{{ $category->id }}" {{ (old('category_id', $product->category_id) == $category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
					</div>
					
                    
	
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label>Descripción <span class="text-danger">*</span></label>
									<textarea class="form-control service-desc" name="description">{{ old('description', $product->description) }}</textarea>
								</div>
							</div>
							
						</div>
					</div>					
					
					<div class="submit-section">
						<button class="btn btn-primary submit-btn" type="submit" name="form_submit" value="submit">Guardar</button>
					</div>
				</form>
			<!-- /Editar producto -->
			</div>
		</div>
	</div>			
</div>
@endsection

@push('page-js')
	
@endpush
