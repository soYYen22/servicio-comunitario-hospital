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
									<label>Producto <span class="text-danger">*</span></label>
									<select class="select2 form-select form-control" name="product"> 
                                        @foreach ($purchases as $purchase)
                                            @if(!empty($product->purchase))
                                            <option {{($product->purchase->id == $purchase->id) ? 'selected': ''}} value="{{$purchase->id}}">{{$purchase->product}}</option>
                                            @endif
                                        @endforeach
									</select>
								</div>
							</div>
						</div>
					</div>
					
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Lote <span class="text-danger">*</span></label>

									@php
										$displayLote = old('lote') ?? $product->lote ?? '';
									@endphp

									<input class="form-control" type="text" name="lote" value="{{ $displayLote }}">
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
