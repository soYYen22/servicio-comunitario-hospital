@extends('admin.layouts.app')
@php
    $title = 'configuraciones';
@endphp

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Configuraciones Generales</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Panel de Control</a></li>
		<li class="breadcrumb-item"><a href="javascript:(0)">Configuraciones</a></li>
		<li class="breadcrumb-item active">Configuraciones Generales</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">				
	<div class="col-12">
		@include('app_settings::_settings')	
	</div>
</div>
@endsection
