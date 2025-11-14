@extends('admin.layouts.plain')

@section('content')
<h1>Iniciar sesión</h1>
<p class="account-subtitle">Acceso a nuestro panel</p>

@if (session('login_error'))
    <x-alerts.danger :error="session('login_error')" />
@endif

<!-- Form -->
<form action="{{ route('login') }}" method="post">
    @csrf
    <div class="form-group">
        <input class="form-control" name="email" type="text" placeholder="Correo electrónico">
    </div>
    <div class="form-group">
        <input class="form-control" name="password" type="password" placeholder="Contraseña">
    </div>
    <div class="form-group">
        <button class="btn btn-primary btn-block" type="submit">Iniciar sesión</button>
    </div>
</form>
<!-- /Form -->

<div class="text-center forgotpass">
    <a href="{{ route('password.request') }}"></a>
</div>

<div class="text-center dont-have">
     <a href="{{ route('register') }}"></a>
</div>
@endsection
