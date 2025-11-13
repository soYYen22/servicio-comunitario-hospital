<!-- Encabezado -->
<div class="header">
			
	<!-- Logo -->
	<div class="header-left">
		<a href="{{ route('dashboard') }}" class="logo">
			<img src="@if(!empty(AppSettings::get('logo'))) {{ asset('storage/'.AppSettings::get('logo')) }} @else{{ asset('assets/img/logoc.png') }} @endif" alt="Logo">
		</a>
		<a href="{{ route('dashboard') }}" class="logo logo-small">
			<img src="{{ asset('assets/img/logo-small.png') }}" alt="Logo" width="30" height="30">
		</a>
	</div>
	<!-- /Logo -->
	
	<a href="javascript:void(0);" id="toggle_btn">
		<i class="fe fe-text-align-left"></i>
	</a>
	
	<!-- Botón de menú móvil -->
	<a class="mobile_btn" id="mobile_btn">
		<i class="fa fa-bars"></i>
	</a>
	<!-- /Botón de menú móvil -->
	
	<!-- Menú derecho del encabezado -->
	<ul class="nav user-menu">
		<li class="nav-item dropdown">
			<a href="#" data-target="#add_sales" title="realizar una venta" data-toggle="modal" class="dropdown-toggle nav-link">
				<i class="fas fa-clipboard"></i>
			</a>
		</li>
		<!-- Notificaciones -->
		<li class="nav-item dropdown noti-dropdown">
			<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
				<i class="fe fe-bell"></i> <span class="badge badge-pill">{{ auth()->user()->unReadNotifications->count() }}</span>
			</a>
			<div class="dropdown-menu notifications">
				<div class="topnav-dropdown-header">
					<span class="notification-title">Notificaciones</span>
					<a href="{{ route('mark-as-read') }}" class="clear-noti">Marcar todas como leídas</a>
				</div>
				<div class="noti-content">
					<ul class="notification-list">
						@foreach (auth()->user()->unReadNotifications as $notification)
							<li class="notification-message">
								<a href="{{ route('read') }}">
									<div class="media">
										<span class="avatar avatar-sm">
											<img class="avatar-img rounded-circle" alt="Imagen del producto" src="{{ asset('storage/purchases/'.$notification['image']) }}">
										</span>
										<div class="media-body">
											<h6 class="text-danger">Alerta de Stock</h6>
											<p class="noti-details">
												<span class="noti-title">{{ $notification->data['product_name'] }} tiene solo {{ $notification->data['quantity'] }} unidades restantes.</span>
												<span>Por favor actualiza la cantidad de compra.</span>
											</p>
											<p class="noti-time"><span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span></p>
										</div>
									</div>
								</a>
							</li>
						@endforeach						
					</ul>
				</div>
				<div class="topnav-dropdown-footer">
					<a href="#">Ver todas las notificaciones</a>
				</div>
			</div>
		</li>
		<!-- /Notificaciones -->
		
		<!-- Menú de usuario -->
		<li class="nav-item dropdown has-arrow">
			<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
				<span class="user-img">
					<img class="rounded-circle" src="{{ !empty(auth()->user()->avatar) ? asset('storage/users/'.auth()->user()->avatar) : asset('assets/img/avatar.png') }}" width="31" alt="avatar">
				</span>
			</a>
			<div class="dropdown-menu">
				<div class="user-header">
					<div class="avatar avatar-sm">
						<img src="{{ !empty(auth()->user()->avatar) ? asset('storage/users/'.auth()->user()->avatar) : asset('assets/img/avatar.png') }}" alt="Imagen del usuario" class="avatar-img rounded-circle">
					</div>
					<div class="user-text">
						<h6>{{ auth()->user()->name }}</h6>
					</div>
				</div>
				
				<a class="dropdown-item" href="{{ route('profile') }}">Mi Perfil</a>
				@can('view-settings')<a class="dropdown-item" href="{{ route('settings') }}">Configuración</a>@endcan
				
				<a href="javascript:void(0)" class="dropdown-item">
					<form action="{{ route('logout') }}" method="post">
						@csrf
						<button type="submit" class="btn">Cerrar sesión</button>
					</form>
				</a>
			</div>
		</li>
		<!-- /Menú de usuario -->
		
	</ul>
	<!-- /Menú derecho del encabezado -->
	
</div>
<!-- /Encabezado -->
