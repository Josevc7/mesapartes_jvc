<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargo - {{ $expediente->codigo_expediente }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #333;
            background: #fff;
        }

        .cargo-container {
            max-width: 500px;
            margin: 10px auto;
            padding: 15px;
            border: 2px solid #333;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #333;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .header p {
            font-size: 8pt;
            color: #555;
        }

        .header .logo {
            max-width: 40px;
            height: auto;
            margin-top: -12px;
            margin-bottom: 3px;
        }

        .titulo-cargo {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 10px 0;
            padding: 6px;
            background: #f5f5f5;
            border: 1px solid #ddd;
        }

        .info-row {
            display: flex;
            margin-bottom: 4px;
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            width: 130px;
            flex-shrink: 0;
            font-size: 8pt;
        }

        .info-value {
            flex: 1;
            font-size: 8pt;
        }

        .section {
            margin-bottom: 12px;
        }

        .section-title {
            font-weight: bold;
            font-size: 9pt;
            background: #e9e9e9;
            padding: 3px 8px;
            margin-bottom: 6px;
            border-left: 3px solid #333;
        }

        .codigo-expediente {
            font-size: 12pt;
            font-weight: bold;
            color: #000;
            background: #f0f0f0;
            padding: 3px 8px;
            display: inline-block;
        }

        .estado-badge {
            display: inline-block;
            padding: 2px 8px;
            font-weight: bold;
            font-size: 8pt;
            border-radius: 3px;
            text-transform: uppercase;
        }

        .estado-derivado { background: #d4edda; color: #155724; }
        .estado-pendiente { background: #fff3cd; color: #856404; }
        .estado-proceso { background: #cce5ff; color: #004085; }
        .estado-finalizado { background: #d1ecf1; color: #0c5460; }

        .disclaimer {
            margin-top: 12px;
            padding: 8px;
            background: #fff8e1;
            border: 1px solid #ffcc02;
            font-size: 8pt;
            text-align: center;
            font-style: italic;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }

        .btn-container {
            text-align: center;
            margin-top: 10px;
            padding: 10px;
        }

        .btn {
            display: inline-block;
            padding: 6px 15px;
            margin: 0 3px;
            font-size: 9pt;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        @media print {
            body {
                background: white;
            }

            .cargo-container {
                border: 2px solid #000;
                margin: 0;
                max-width: 100%;
            }

            .btn-container {
                display: none !important;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="cargo-container">
        <!-- Encabezado -->
        <div class="header">
            <img src="{{ asset('images/logo-drtc.png') }}" alt="Logo DRTC" class="logo">
            <h1>Dirección Regional de Transportes y Comunicaciones de Apurímac</h1>
            <p>Mesa de Partes Digital</p>
        </div>

        <div class="titulo-cargo">CARGO DE RECEPCIÓN</div>

        <!-- Datos del Expediente -->
        <div class="section">
            <div class="section-title">DATOS DEL EXPEDIENTE</div>

            <div class="info-row">
                <span class="info-label">Código de Expediente:</span>
                <span class="info-value codigo-expediente">{{ $expediente->codigo_expediente }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Fecha de Registro:</span>
                <span class="info-value">{{ $expediente->created_at->format('d/m/Y H:i') }}</span>
            </div>

            <!--<div class="info-row">
                <span class="info-label">Canal:</span>
                <span class="info-value">Presencial</span>
            </div>-->

            <div class="info-row">
                <span class="info-label">Registrado por:</span>
                <span class="info-value"> Mesa de Partes</span>
            </div>
        </div>

        <!-- Datos del Remitente -->
        <div class="section">
            <div class="section-title">DATOS DEL REMITENTE</div>
            

            <div class="info-row">
                <span class="info-label">Remitente:</span>
                <span class="info-value">
                    @if($expediente->persona)
                        @if($expediente->persona->tipo_persona == 'NATURAL')
                            {{ $expediente->persona->nombres }} {{ $expediente->persona->apellido_paterno }} {{ $expediente->persona->apellido_materno }}
                        @else
                            {{ $expediente->persona->razon_social }}
                        @endif
                    @else
                        {{ $expediente->remitente ?? 'N/A' }}
                    @endif
                </span>
            </div>
            @if($expediente->persona)
                <div class="info-row">
                    <span class="info-label">
                        {{ $expediente->persona->tipo_documento }}:
                   </span>
                   <span class="info-value">
                       {{ $expediente->persona->numero_documento }}
                   </span>
               </div>
            @endif
        </div>

        <!-- Datos del Documento -->
        <div class="section">
            <div class="section-title">DATOS DEL DOCUMENTO</div>

            <div class="info-row">
                <span class="info-label">Tipo de Documento:</span>
                <span class="info-value">{{ $expediente->tipo_documento_entrante ?? 'Solicitud' }}@if($expediente->numero_documento_entrante) N° {{ $expediente->numero_documento_entrante }}@endif</span>
            </div>

            <div class="info-row">
                <span class="info-label">Asunto:</span>
                <span class="info-value">{{ $expediente->asunto_documento ?? $expediente->asunto }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Folios:</span>
                <span class="info-value">
                    {{ $expediente->folios ?? 1 }} {{ ($expediente->folios ?? 1) == 1 ? 'hoja' : 'hojas' }}
                </span>
            </div>
        </div>

        <!-- Clasificación  y Derivacion -->
        <div class="section">
            <div class="section-title">DERIVADO</div>

           {{-- @if($expediente->tipoTramite)
            <div class="info-row">
                <span class="info-label">Tipo de Trámite:</span>
                <span class="info-value">{{ $expediente->tipoTramite->nombre }}</span>
            </div>
            @endif --}}

            @if($expediente->area)
            <div class="info-row">
                <span class="info-label">Área:</span>
                <span class="info-value">{{ $expediente->area->nombre }}</span>
            </div>
            @endif

           {{-- <div class="info-row">
                <span class="info-label">Estado:</span>
                <span class="info-value">
                    @php
                        $estadoClass = 'estado-pendiente';
                        if($expediente->estado == 'Derivado') $estadoClass = 'estado-derivado';
                        elseif($expediente->estado == 'En Proceso') $estadoClass = 'estado-proceso';
                        elseif($expediente->estado == 'Finalizado') $estadoClass = 'estado-finalizado';
                    @endphp
                    <span class="estado-badge {{ $estadoClass }}">{{ $expediente->estado }}</span>
                </span>
            </div>--}}
        </div>

        <!-- Disclaimer -->
        <div class="disclaimer">
            Este cargo acredita la recepción del documento, no implica conformidad ni aprobación del contenido.
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Mesa de Partes Digital - DRTC Apurímac</p>
        </div>
    </div>

    <!-- Botones (no se imprimen) -->
    <div class="btn-container no-print">
        <button onclick="window.print()" class="btn btn-primary">
            Imprimir Cargo
        </button>
        <!--<a href="{{ route('mesa-partes.index') }}" class="btn btn-secondary">
            Volver a Mesa de Partes
        </a>-->
        <button onclick="window.close()" class="btn btn-secondary">
            Cerrar
        </button>
    </div>
</body>
</html>
