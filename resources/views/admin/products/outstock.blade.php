@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
	
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Agotado</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('products.index')}}">Productos</a></li>
		<li class="breadcrumb-item active">Agotado</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Productos agotados -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="outstock-product" class=" table table-hover table-center mb-0">
						<thead>
							<tr>
								<th>Nombre de marca</th>
								<th>Categoría</th>
								<th>Precio</th>
								<th>Cantidad</th>
								<th>Descuento</th>
								<th>Fecha de vencimiento</th>
								<th class="action-btn">Acción</th>
							</tr>
						</thead>
						<tbody>
							
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- /Productos agotados -->
		
	</div>
</div>

@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        var table = $('#outstock-product').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{route('outstock')}}",
            columns: [
                {data: 'product', name: 'product'},
                {data: 'category', name: 'category'},
                {data: 'price', name: 'price'},
                {data: 'quantity', name: 'quantity'},
                {data: 'discount', name: 'discount'},
				{data: 'expiry_date', name: 'expiry_date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
        
    });
</script> 
@endpush
