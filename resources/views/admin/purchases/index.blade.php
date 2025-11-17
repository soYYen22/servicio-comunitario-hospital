@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Entradas</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Panel</a></li>
		<li class="breadcrumb-item active">Entradas</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<a href="{{route('purchases.create')}}" class="btn btn-primary float-right mt-2">Agregar nueva</a>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Compras recientes -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="purchase-table" class="datatable table table-hover table-center mb-0">
						<thead>
							<tr>
								<th>Nombre del medicamento</th>
								<th>Categoría</th>
								<th>Proveedor</th>
								<th>Lote</th>
								<!-- Costo de compra eliminado -->
								<th>Cantidad</th>
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
		<!-- /Compras recientes -->
		
	</div>
</div>
@endsection	

@push('page-js')
<script>
    $(document).ready(function() {
        var table = $('#purchase-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{route('purchases.index')}}",
            columns: [
					{data: 'product', name: 'product'},
					{data: 'category', name: 'category'},
					{data: 'supplier', name: 'supplier'},
					{data: 'price', name: 'price'},
					// Costo de compra eliminado
					{data: 'quantity', name: 'quantity'},
					{data: 'expiry_date', name: 'expiry_date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
        
    });
</script> 
@endpush
