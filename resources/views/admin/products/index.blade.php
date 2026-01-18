@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
	
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Productos</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Panel</a></li>
		<li class="breadcrumb-item active">Productos</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<a href="{{route('products.create')}}" class="btn btn-primary float-right mt-2">Agregar producto</a>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Productos -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="product-table" class="datatable table table-hover table-center mb-0">
						<thead>
							<tr>
								<th>Nombre del medicamento</th>
								<th>Categoría</th>
								<th>Cantidad</th>
								<th class="action-btn">Acción</th>
							</tr>
						</thead>
						<tbody>

														 
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- /Productos -->
		
	</div>
</div>

@endsection

@push('page-js')
<script>
    $(document).ready(function() {
		var table = $('#product-table').DataTable({
			processing: true,
			serverSide: true,
			ajax: "{{route('products.index')}}",
			columns: [
				{data: 'product', name: 'product'},
				{data: 'category', name: 'category'},
				{data: 'quantity', name: 'quantity'},
				{data: 'action', name: 'action', orderable: false, searchable: false},
			]
		});
        
    });
</script> 
@endpush
