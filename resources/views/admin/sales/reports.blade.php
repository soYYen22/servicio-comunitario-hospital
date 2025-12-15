@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Reportes de Salidas</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Panel Principal</a></li>
		<li class="breadcrumb-item active">Generar Reportes de Salidas</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<a href="#generate_report" data-toggle="modal" class="btn btn-primary float-right mt-2">Generar Reporte</a>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">

		{{-- Mostrar rango de fechas si existen --}}
		@if(isset($from_date) && isset($to_date))
			<div class="mb-3" style="text-align: left;">
				<strong>Desde:</strong> {{ \Carbon\Carbon::parse($from_date)->locale('es')->translatedFormat('d F, Y') }}
				&nbsp;&nbsp;
				<strong>Hasta:</strong> {{ \Carbon\Carbon::parse($to_date)->locale('es')->translatedFormat('d F, Y') }}
			</div>
		@endif

		@isset($sales)
            <!--  Reporte de Ventas -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="sales-table" class="datatable table table-hover table-center mb-0">
                            <thead>
								<tr>
									<th>Nombre del Producto</th>
									<th>Cantidad</th>
									<th>Destino</th>
									<th>Fecha</th>
								</tr>
                            </thead>
                            <tbody>
                                @foreach ($sales as $sale)
                                    @if (!(empty($sale->product->purchase)))
                                        <tr>
                                            <td>
                                                {{$sale->product->purchase->product}}
                                                @if (!empty($sale->product->purchase->image))
                                                    <span class="avatar avatar-sm mr-2">
                                                    <img class="avatar-img" src="{{asset("storage/purchases/".$sale->product->purchase->image)}}" alt="imagen">
                                                    </span>
                                                @endif
                                            </td>
											<td>{{$sale->quantity}}</td>
											<td>{{ $sale->destination ?? '' }}</td>
										<td>{{ \Carbon\Carbon::parse($sale->created_at)->locale('es')->translatedFormat('d F, Y') }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- / Reporte de Ventas -->
        @endisset
       
		
	</div>
</div>

<!-- Modal Generar Reporte -->
<div class="modal fade" id="generate_report" aria-hidden="true" role="dialog">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Generar Reporte</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="post" action="{{route('sales.report')}}">
					@csrf
					<div class="row form-row">
						<div class="col-12">
							<div class="row">
								<div class="col-6">
									<div class="form-group">
										<label>Desde</label>
										<input type="date" name="from_date" class="form-control from_date">
									</div>
								</div>
								<div class="col-6">
									<div class="form-group">
										<label>Hasta</label>
										<input type="date" name="to_date" class="form-control to_date">
									</div>
								</div>
							</div>
						</div>
					</div>
					<button type="submit" class="btn btn-primary btn-block submit_report">Generar</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- /Modal Generar Reporte -->
@endsection

@push('page-js')
<script>
    $(document).ready(function(){
		var reportFrom = "{{ isset($from_date) ? \Carbon\Carbon::parse($from_date)->locale('es')->translatedFormat('d F, Y') : '' }}";
		var reportTo = "{{ isset($to_date) ? \Carbon\Carbon::parse($to_date)->locale('es')->translatedFormat('d F, Y') : '' }}";

		$('#sales-table').DataTable({
			dom: 'Bfrtip',		
			buttons: [
				{
					extend: 'collection',
					text: 'Exportar Datos',
					buttons: [

						/** -------------------- PDF -------------------- **/
						{
							extend: 'pdf',
							exportOptions: {
								columns: "thead th:not(.action-btn)"
							},
							customize: function (doc) {
								doc.info = { title: '' };
								// Insertar rango de fechas arriba si existe
								if(reportFrom && reportTo){
									doc.content.unshift({
										text: 'Desde: ' + reportFrom + '   Hasta: ' + reportTo,
										margin: [0, 0, 0, 8],
										fontSize: 11
									});
								}

								// Quitar títulos agregados por DataTables (si los hubiera)
								doc.content = doc.content.filter(function(item) {
									return !(item.style === 'title' || item.style === 'header' || item.fontSize >= 14);
								});
							}
						},

						/** -------------------- EXCEL -------------------- **/
						{
							extend: 'excel',
							exportOptions: {
								columns: "thead th:not(.action-btn)"
							},
							customize: function (xlsx) {
								var sheet = xlsx.xl.worksheets['sheet1.xml'];
								// Insertar rango de fechas en la celda A1 si existe
								if(reportFrom && reportTo){
									// Si existe una celda A1, reemplazar su texto; si no, crearla
									var info = 'Desde: ' + reportFrom + '   Hasta: ' + reportTo;
									var $cell = $('row c[r^="A1"] t', sheet);
									if($cell.length){
										$cell.text(info);
									} else {
										// insertar nueva fila al inicio
										$('sheetData', sheet).prepend('<row r="1"><c r="A1" t="inlineStr"><is><t>'+info+'</t></is></c></row>');
									}
								}
								// Quitar título automático si DataTables lo agrega
								$('row c[r^="A2"]', sheet).each(function () {
									// dejar A2 tal cual (tabla seguirá debajo)
								});
							}
						},

						/** -------------------- CSV -------------------- **/
						{
							extend: 'csv',
							exportOptions: {
								columns: "thead th:not(.action-btn)"
							},
							customize: function (csv) {
								let lines = csv.split("\n");
								// Si la primera línea es un título, se elimina
								if (lines[0].toLowerCase().includes("reporte") ||
									lines[0].toLowerCase().includes("data")) {
									lines.shift();
								}
								if(reportFrom && reportTo){
									lines.unshift('Desde: ' + reportFrom + '   Hasta: ' + reportTo);
								}

								return lines.join("\n");
							}
						},

						/** -------------------- PRINT -------------------- **/
						{
							extend: 'print',
							exportOptions: {
								columns: "thead th:not(.action-btn)"
							},
							customize: function (win) {
								// Eliminar encabezado H1 generado por DataTables
								$(win.document.body).find('h1').remove();

								// Insertar rango de fechas arriba del documento si existe
								if(reportFrom && reportTo){
									$(win.document.body).prepend('<div style="text-align:left; font-weight:600; margin-bottom:8px;">Desde: '+reportFrom+' &nbsp;&nbsp; Hasta: '+reportTo+'</div>');
								}

								// Eliminar textos de gran tamaño
								$(win.document.body).find('*').each(function() {
									if (parseInt($(this).css('font-size')) >= 18) {
										$(this).remove();
									}
								});
							}
						}

					]
				}
			]
		});
    });
</script>
@endpush
