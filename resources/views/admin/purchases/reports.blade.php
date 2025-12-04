@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Reportes de Entradas</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Panel</a></li>
		<li class="breadcrumb-item active">Generar reportes de Entradas</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<a href="#generate_report" data-toggle="modal" class="btn btn-primary float-right mt-2">Generar reporte</a>
</div>
@endpush

@section('content')
    {{-- Mostrar rango de fechas si existen --}}
    @if(isset($from_date) && isset($to_date))
        <div class="mb-3" style="text-align: left;">
            <strong>Desde:</strong> {{ \Carbon\Carbon::parse($from_date)->locale('es')->translatedFormat('d F, Y') }}
            &nbsp;&nbsp;
            <strong>Hasta:</strong> {{ \Carbon\Carbon::parse($to_date)->locale('es')->translatedFormat('d F, Y') }}
        </div>
    @endif

    @isset($purchases)
    <div class="row">
        <div class="col-md-12">
            <!-- Reportes de compras -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="purchase-table" class="datatable table table-hover table-center mb-0">
                            <thead>
                                <tr>
                                    <th>Nombre del medicamento</th>
                                    <th>Categoría</th>
                                    <th>Lote</th>
                                    <th>Cantidad</th>
                                    <th>Proveedor</th>
                                    <th>Fecha de vencimiento</th>
                                    <th>Fecha de entrada</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($purchases as $purchase)
                                @if(!empty($purchase->supplier) && !empty($purchase->category))
                                <tr>
                                    <td>
                                        <h2 class="table-avatar">
                                            @if(!empty($purchase->image))
                                            <span class="avatar avatar-sm mr-2">
                                                <img class="avatar-img" src="{{asset('storage/purchases/'.$purchase->image)}}" alt="imagen del producto">
                                            </span>
                                            @endif
                                            {{$purchase->product}}
                                        </h2>
                                    </td>
                                    <td>{{$purchase->category->name}}</td>
                                    <td>
                                        @if(!empty($purchase->purchaseProduct) && isset($purchase->purchaseProduct->lote))
                                            {{ $purchase->purchaseProduct->lote }}
                                        @else
                                        @endif
                                    </td>
                                    <td>{{$purchase->quantity}}</td>
                                    <td>{{$purchase->supplier->name}}</td>
                                    <td>{{ \Carbon\Carbon::parse($purchase->expiry_date)->locale('es')->translatedFormat('d F, Y') }}</td>
                                    <td>{{ $purchase->entry_date ? \Carbon\Carbon::parse($purchase->entry_date)->locale('es')->translatedFormat('d F, Y') : '' }}</td>
                                </tr>
                                @endif
                            @endforeach                         
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Reportes de compras -->
        </div>
    </div>
    @endisset

    <!-- Modal Generar -->
    <div class="modal fade" id="generate_report" aria-hidden="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generar reporte</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{route('purchases.report')}}">
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
    <!-- /Modal Generar -->
@endsection

@push('page-js')
<script>
    $(document).ready(function(){
        var reportFrom = "{{ isset($from_date) ? \Carbon\Carbon::parse($from_date)->locale('es')->translatedFormat('d F, Y') : '' }}";
        var reportTo = "{{ isset($to_date) ? \Carbon\Carbon::parse($to_date)->locale('es')->translatedFormat('d F, Y') : '' }}";

        $('#purchase-table').DataTable({
            dom: 'Bfrtip',		
            buttons: [
                {
                    extend: 'collection',
                    text: 'Exportar datos',
                    buttons: [

                        /** -------------------- PDF -------------------- **/
                        {
                            extend: 'pdf',
                            exportOptions: {
                                columns: "thead th:not(.action-btn)",
                                format: {
                                    body: function (data, row, column, node) {
                                        return formatDateToSpanish(data, column);
                                    }
                                }
                            },
                            customize: function (doc) {
                                doc.info = { title: '' };
                                if(reportFrom && reportTo){
                                    doc.content.unshift({
                                        text: 'Desde: ' + reportFrom + '   Hasta: ' + reportTo,
                                        margin: [0, 0, 0, 8],
                                        fontSize: 11
                                    });
                                }
                                doc.content = doc.content.filter(function(item) {
                                    return !(item.style === 'title' || item.style === 'header' || item.fontSize >= 14);
                                });
                            }
                        },

                        /** -------------------- EXCEL -------------------- **/
                        {
                            extend: 'excel',
                            exportOptions: {
                                columns: "thead th:not(.action-btn)",
                                format: {
                                    body: function (data, row, column, node) {
                                        return formatDateToSpanish(data, column);
                                    }
                                }
                            },
                            customize: function (xlsx) {
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                if(reportFrom && reportTo){
                                    var info = 'Desde: ' + reportFrom + '   Hasta: ' + reportTo;
                                    var $cell = $('row c[r^="A1"] t', sheet);
                                    if($cell.length){
                                        $cell.text(info);
                                    } else {
                                        $('sheetData', sheet).prepend('<row r="1"><c r="A1" t="inlineStr"><is><t>'+info+'</t></is></c></row>');
                                    }
                                }
                            }
                        },

                        /** -------------------- CSV -------------------- **/
                        {
                            extend: 'csv',
                            exportOptions: {
                                columns: "thead th:not(.action-btn)",
                                format: {
                                    body: function (data, row, column, node) {
                                        return formatDateToSpanish(data, column);
                                    }
                                }
                            },
                            customize: function (csv) {
                                let lines = csv.split("\n");
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
                                columns: "thead th:not(.action-btn)",
                                format: {
                                    body: function (data, row, column, node) {
                                        return formatDateToSpanish(data, column);
                                    }
                                }
                            },
                            customize: function (win) {
                                $(win.document.body).find('h1').remove();
                                if(reportFrom && reportTo){
                                    $(win.document.body).prepend('<div style="text-align:left; font-weight:600; margin-bottom:8px;">Desde: '+reportFrom+' &nbsp;&nbsp; Hasta: '+reportTo+'</div>');
                                }
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
            
            // Función helper para normalizar/convertir fechas a formato español (mes completo)
            function formatDateToSpanish(data, column){
                // Solo procesar las columnas de fecha: índice 5 y 6 (vencimiento, entrada)
                if (column !== 5 && column !== 6) return stripHtml(data);

                var text = stripHtml(data).trim();
                if (!text) return text;

                // Mapas de meses ingleses (corto y largo) a español completo
                var months = {
                    'jan':'enero','january':'enero',
                    'feb':'febrero','february':'febrero',
                    'mar':'marzo','march':'marzo',
                    'apr':'abril','april':'abril',
                    'may':'mayo','may':'mayo',
                    'jun':'junio','june':'junio',
                    'jul':'julio','july':'julio',
                    'aug':'agosto','august':'agosto',
                    'sep':'septiembre','september':'septiembre',
                    'oct':'octubre','october':'octubre',
                    'nov':'noviembre','november':'noviembre',
                    'dec':'diciembre','december':'diciembre'
                };

                // Buscar patrón tipo: 28 Nov, 2025  OR 28 November 2025 OR 2025-11-28
                // Si es ISO (YYYY-MM-DD), convertir fácilmente
                var isoMatch = text.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if(isoMatch){
                    var y = isoMatch[1], m = isoMatch[2], d = isoMatch[3];
                    var monthNames = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
                    return d + ' ' + monthNames[parseInt(m,10)-1] + ', ' + y;
                }

                var parts = text.split(/\s*,?\s+/);
                // Examples: ['28','Nov','2025'] or ['28','November','2025'] or ['28','noviembre,','2025']
                if(parts.length >= 3){
                    var day = parts[0].replace(/[^0-9]/g,'');
                    var monthRaw = parts[1].replace(/[^A-Za-z]/g,'').toLowerCase();
                    var year = parts[2].replace(/[^0-9]/g,'');
                    if(months[monthRaw]){
                        return day + ' ' + months[monthRaw] + ', ' + year;
                    }
                }

                // Si ya está en español o en formato inesperado, devolver el texto original
                return text;
            }

            function stripHtml(html){
                if(!html) return '';
                return $('<div/>').html(html).text();
            }
    });
</script>
@endpush
