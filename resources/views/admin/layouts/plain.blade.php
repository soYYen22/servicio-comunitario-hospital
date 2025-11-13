<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ ucfirst(AppSettings::get('app_name', 'Aplicación')) }} - {{ ucfirst($title ?? '') }}</title>
    <!-- Ícono -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ !empty(AppSettings::get('favicon')) ? asset('storage/'.AppSettings::get('favicon')) : asset('assets/img/favicon.png') }}">
    
    <!-- CSS de Bootstrap -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

    <!-- Iconos Fontawesome -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">

    <!-- CSS Principal -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    
    <!-- CSS de la Página -->
    @stack('page-css')
    
    <!--[if lt IE 9]>
        <script src="assets/js/html5shiv.min.js"></script>
        <script src="assets/js/respond.min.js"></script>
    <![endif]-->
</head>
<body>

    <!-- Contenedor Principal -->
    <div class="main-wrapper login-body">
        <div class="login-wrapper">
            <div class="container">
                <div class="loginbox">
                    <div class="login-left">
                        <img class="img-fluid" src="{{ !empty(AppSettings::get('logo')) ? asset('storage/'.AppSettings::get('logo')) : asset('assets/img/logo.png') }}" alt="Logo">
                    </div>
                    <div class="login-right">
                        <div class="login-right-wrap">
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <x-alerts.danger :error="$error" />
                                @endforeach
                            @endif
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Contenedor Principal -->
    
</body>

<!-- jQuery -->
<script src="{{ asset('assets/js/jquery-3.2.1.min.js') }}"></script>

<!-- Núcleo de Bootstrap -->
<script src="{{ asset('assets/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>

<!-- JS Personalizado -->
<script src="{{ asset('assets/js/script.js') }}"></script>

<!-- JS de la Página -->
@stack('page-js')
</html>
