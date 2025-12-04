@extends('admin.layouts.app')

<x-assets.datatables />  

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Permisos</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Panel Principal</a></li>
		<li class="breadcrumb-item active">Permisos</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<a href="#add_permission" data-toggle="modal" class="btn btn-primary float-right mt-2">Agregar Permiso</a>
</div>
@endpush

@section('content')

<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="perm-table" class="datatable table table-striped table-bordered table-hover table-center mb-0">
						<thead>
							<tr>
								<th>Nombre</th>
								<th>Fecha de Creación</th>
								<th class="text-center action-btn">Acciones</th>
							</tr>
						</thead>
						<tbody>				
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>			
</div>

<!-- Modal Agregar Permiso -->
<div class="modal fade" id="add_permission" aria-hidden="true" role="dialog">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Agregar Permiso</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="POST" action="{{route('permissions.store')}}">
					@csrf
					<div class="row form-row">
						<div class="col-12">
							<div class="form-group">
								<label>Nombre del Permiso</label>
								<input type="text" name="permission" class="form-control" placeholder="Ejemplo: crear-usuario">
							</div>
						</div>
					</div>
					<button type="submit" class="btn btn-primary btn-block">Guardar</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- /Modal Agregar Permiso -->

<!-- Modal Editar Permiso -->
<div class="modal fade" id="edit_permission" aria-hidden="true" role="dialog">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Editar Permiso</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="post" action="{{route('permissions.update')}}">
					@csrf
					@method("PUT")
					<div class="row form-row">
						<div class="col-12">
							<input type="hidden" name="id" id="edit_id">
							<div class="form-group">
								<label>Nombre del Permiso</label>
								<input type="text" class="form-control perm_name" name="permission">
							</div>
						</div>
					</div>
					<button type="submit" class="btn btn-primary btn-block">Guardar Cambios</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- /Modal Editar Permiso -->

@endsection

@push('page-js')
<script>
$(document).ready(function() {
	var table = $('#perm-table').DataTable({
		processing: true,
		serverSide: true,
		ajax: "{{route('permissions.index')}}",
			columns: [
				{
					data: 'name',
					name: 'name',
					render: function(data, type, row){
						return translatePermissionName(data);
					}
				},
				{data: 'created_at', name: 'created_at'},
				{data: 'action', name: 'action', orderable: false, searchable: false},
			]
	});

		// Traduce visualmente el nombre del permiso sin alterar los datos
		function translatePermissionName(name){
			if(!name) return '';
			name = String(name);
			const exact = {
				'create-user': 'Crear Usuario', 'crear-usuario': 'Crear Usuario',
				'view-user': 'Ver Usuario', 'ver-usuario': 'Ver Usuario',
				'edit-user': 'Editar Usuario', 'editar-usuario': 'Editar Usuario',
				'delete-user': 'Eliminar Usuario', 'eliminar-usuario': 'Eliminar Usuario',
				'create-role': 'Crear Rol', 'edit-role': 'Editar Rol', 'delete-role': 'Eliminar Rol',
			};
			if(exact[name]) return exact[name];
			const verbs = {
				'create':'Crear','crear':'Crear',
				'view':'Ver','ver':'Ver','show':'Ver',
				'edit':'Editar','editar':'Editar',
				'delete':'Eliminar','destroy':'Eliminar','eliminar':'Eliminar',
				'assign':'Asignar','asignar':'Asignar',
				'manage':'Gestionar','gestionar':'Gestionar',
				'list':'Listar','index':'Listar'
			};
			const nouns = {
				'user':'Usuario','usuario':'Usuario',
				'role':'Rol','rol':'Rol',
				'permission':'Permiso','permiso':'Permiso',
				'category':'Categoría','categoría':'Categoría','categoria':'Categoría',
				'product':'Producto','producto':'Producto',
				'supplier':'Proveedor','proveedor':'Proveedor',
				// Mostrar "purchases" (compras) como "Entrada" sólo visualmente
				'purchase':'Entrada','compra':'Entrada','purchases':'Entrada','compras':'Entrada',
				// Mostrar "sales" (ventas) como "Salida" sólo visualmente
				'sale':'Salida','venta':'Salida','sales':'Salida','ventas':'Salida',
				'settings':'Ajustes','setting':'Ajuste'
			};
			// split by non-alphanumeric separators
			const parts = name.split(/[^A-Za-z0-9áéíóúñüÁÉÍÓÚÑÜ]+/).filter(Boolean);
			if(parts.length === 0) return name;
			if(parts.length === 1){
				const single = parts[0].toLowerCase();
				if(nouns[single]) return nouns[single];
				return parts[0].charAt(0).toUpperCase()+parts[0].slice(1);
			}
			const first = parts[0].toLowerCase();
			const verb = verbs[first] || (first.charAt(0).toUpperCase()+first.slice(1));
			// translate the rest using nouns map when possible
			const restParts = parts.slice(1).map(p => {
				const low = p.toLowerCase();
				if(nouns[low]) return nouns[low];
				return p.charAt(0).toUpperCase()+p.slice(1);
			});
			const rest = restParts.join(' ');
			return verb + ' ' + rest;
		}

	// Abrir modal de edición
	$('#perm-table').on('click', '.editbtn', function (){
		$('#edit_permission').modal('show');
		var id = $(this).data('id');
		var permission = $(this).data('name');
		$('#edit_id').val(id);
		$('.perm_name').val(permission);
	});
});
</script>
@endpush
