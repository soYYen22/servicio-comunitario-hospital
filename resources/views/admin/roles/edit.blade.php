@extends('admin.layouts.app')

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Editar Rol</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item active">Panel Principal</li>
	</ul>
</div>
@endpush

@section('content')

<div class="row">
    <div class="col-md-12 col-lg-12">
    
        <div class="card card-table">
            <div class="card-header">
                <h4 class="card-title">Editar Rol</h4>
            </div>
            <div class="card-body">
                <div class="p-5">
                    <form method="POST" action="{{route('roles.update',$role)}}">
                        @csrf
                        @method("PUT")
                        <div class="form-group">
                            <label>Nombre del Rol</label>
                            <input type="text" name="role" value="{{$role->name}}" class="form-control" placeholder="super-admin">
                        </div>
                        <div class="form-group">
                            <label>Seleccionar Permisos</label>
                            <select class="select2 form-select form-control" name="permission[]" multiple="multiple"> 
                                @foreach ($permissions as $permission)
                                    <option {{$role->hasPermissionTo($permission->name) ? 'selected': ''}} value="{{$permission->name}}">
                                        {{$permission->name}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
        
    </div>

</div>

@endsection

@push('page-js')
<script>
document.addEventListener('DOMContentLoaded', function(){
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
            'purchase':'Compra','compra':'Compra',
            'sale':'Venta','venta':'Venta',
            'settings':'Ajustes','setting':'Ajuste'
        };
        const parts = name.split(/[^A-Za-z0-9áéíóúñüÁÉÍÓÚÑÜ]+/).filter(Boolean);
        if(parts.length === 0) return name;
        if(parts.length === 1){
            const single = parts[0].toLowerCase();
            if(nouns[single]) return nouns[single];
            return parts[0].charAt(0).toUpperCase()+parts[0].slice(1);
        }
        const first = parts[0].toLowerCase();
        const verb = verbs[first] || (first.charAt(0).toUpperCase()+first.slice(1));
        const restParts = parts.slice(1).map(p => {
            const low = p.toLowerCase();
            if(nouns[low]) return nouns[low];
            return p.charAt(0).toUpperCase()+p.slice(1);
        });
        const rest = restParts.join(' ');
        return verb + ' ' + rest;
    }

    // Translate permission options visually without changing values
    document.querySelectorAll('select[name="permission[]"] option').forEach(function(opt){
        opt.textContent = translatePermissionName(opt.textContent);
    });
});
</script>
@endpush
