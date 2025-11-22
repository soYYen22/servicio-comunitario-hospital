<!-- Barra Lateral -->
<div class="sidebar" id="sidebar">
	<div class="sidebar-inner slimscroll">
		<div id="sidebar-menu" class="sidebar-menu">
			
			<ul>
				<li class="menu-title"> 
					<span>Principal</span>
				</li>
				<li class="{{ route_is('dashboard') ? 'active' : '' }}"> 
					<a href="{{route('dashboard')}}"><i class="fe fe-home"></i> <span>Panel</span></a>
				</li>
				
				@can('view-category')
				<li class="{{ route_is('categories.*') ? 'active' : '' }}"> 
					<a href="{{route('categories.index')}}"><i class="fe fe-layout"></i> <span>Categorías</span></a>
				</li>
				@endcan
				
				@can('view-products')
				<li class="submenu">
					<a href="#"><i class="fe fe-document"></i> <span> Productos</span> </a>
					<ul style="display: none;">
						<li><a class="{{ route_is(('products.*')) ? 'active' : '' }}" href="{{route('products.index')}}">Productos</a></li>
						@can('create-product')<li><a class="{{ route_is('products.create') ? 'active' : '' }}" href="{{route('products.create')}}">Agregar Producto</a></li>@endcan
						@can('view-outstock-products')<li><a class="{{ route_is('outstock') ? 'active' : '' }}" href="{{route('outstock')}}">Agotado</a></li>@endcan
						@can('view-expired-products')<li><a class="{{ route_is('expired') ? 'active' : '' }}" href="{{route('expired')}}">Vencidos</a></li>@endcan
					</ul>
				</li>
				@endcan
				
				@can('view-purchase')
				<li class="submenu">
					<a href="#"><i class="fe fe-star-o"></i> <span> Entradas</span> </a>
					<ul style="display: none;">
						<li><a class="{{ route_is('purchases.*') ? 'active' : '' }}" href="{{route('purchases.index')}}">Entradas</a></li>
						@can('create-purchase')
						<li><a class="{{ route_is('purchases.create') ? 'active' : '' }}" href="{{route('purchases.create')}}">Agregar Entrada</a></li>
						@endcan
					</ul>
				</li>
				@endcan

				@can('view-sales')
				<li class="submenu">
					<a href="#"><i class="fe fe-activity"></i> <span> Salidas</span> </a>
					<ul style="display: none;">
						<li><a class="{{ route_is('sales.*') ? 'active' : '' }}" href="{{route('sales.index')}}">Salidas</a></li>
						@can('create-sale')
						<li><a class="{{ route_is('sales.create') ? 'active' : '' }}" href="{{route('sales.create')}}">Agregar Salidas</a></li>
						@endcan
					</ul>
				</li>
				@endcan
				
				@can('view-supplier')
				<li class="submenu">
					<a href="#"><i class="fe fe-user"></i> <span> Proveedores</span> </a>
					<ul style="display: none;">
						<li><a class="{{ route_is('suppliers.*') ? 'active' : '' }}" href="{{route('suppliers.index')}}">Proveedores</a></li>
						@can('create-supplier')<li><a class="{{ route_is('suppliers.create') ? 'active' : '' }}" href="{{route('suppliers.create')}}">Agregar Proveedor</a></li>@endcan
					</ul>
				</li>
				@endcan

				@can('view-reports')
				<li class="submenu">
					<a href="#"><i class="fe fe-document"></i> <span> Reportes</span> </a>
					<ul style="display: none;">
						<li><a class="{{ route_is('sales.report') ? 'active' : '' }}" href="{{route('sales.report')}}">Reporte de Salida</a></li>
						<li><a class="{{ route_is('purchases.report') ? 'active' : '' }}" href="{{route('purchases.report')}}">Reporte de Entrada</a></li>
					</ul>
				</li>
				@endcan

				@can('view-access-control')
				<li class="submenu">
					<a href="#"><i class="fe fe-lock"></i> <span> Control de Acceso</span> </a>
					<ul style="display: none;">
						@can('view-permission')
						<li><a class="{{ route_is('permissions.index') ? 'active' : '' }}" href="{{route('permissions.index')}}">Permisos</a></li>
						@endcan
						@can('view-role')
						<li><a class="{{ route_is('roles.*') ? 'active' : '' }}" href="{{route('roles.index')}}">Roles</a></li>
						@endcan
					</ul>
				</li>					
				@endcan

				@can('view-users')
				<li class="{{ route_is('users.*') ? 'active' : '' }}"> 
					<a href="{{route('users.index')}}"><i class="fe fe-users"></i> <span>Usuarios</span></a>
				</li>
				@endcan
				
				<li class="{{ route_is('profile') ? 'active' : '' }}"> 
					<a href="{{route('profile')}}"><i class="fe fe-user-plus"></i> <span>Perfil</span></a>
				</li>
				<li class="{{ route_is('backup.index') ? 'active' : '' }}"> 
					<a href="{{route('backup.index')}}"><i class="material-icons">backup</i> <span>Copias de Seguridad</span></a>
				</li>
				@can('view-settings')
				<li class="{{ route_is('settings') ? 'active' : '' }}"> 
					<a href="{{route('settings')}}">
						<i class="material-icons">settings</i>
						 <span> Configuración</span>
					</a>
				</li>
				@endcan
			</ul>
		</div>
	</div>
</div>
<!-- /Barra Lateral -->
