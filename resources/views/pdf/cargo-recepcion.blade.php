<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cargo de Recepción - {{ $expediente->codigo_expediente }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #cc5500;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 18px;
            font-weight: bold;
            color: #cc5500;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
        }
        .content {
            margin: 20px 0;
        }
        .field {
            margin: 8px 0;
            display: flex;
        }
        .field-label {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }
        .field-value {
            flex: 1;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 2px;
        }
        .documents-section {
            margin: 20px 0;
            border: 1px solid #ddd;
            padding: 15px;
        }
        .documents-title {
            font-weight: bold;
            margin-bottom: 10px;
            background-color: #f5f5f5;
            padding: 5px;
        }
        .document-item {
            margin: 5px 0;
            padding-left: 20px;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
        }
        .important-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .qr-section {
            float: right;
            text-align: center;
            margin-left: 20px;
        }
        .tracking-info {
            background-color: #e3f2fd;
            border: 1px solid #2196f3;
            padding: 10px;
            margin: 15px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">DIRECCIÓN REGIONAL DE TRANSPORTES Y COMUNICACIONES</div>
        <div class="subtitle">MESA DE PARTES VIRTUAL</div>
        <div class="title">CARGO DE RECEPCIÓN</div>
    </div>

    <!-- QR Code Section -->
    <div class="qr-section">
        <div style="border: 1px solid #ccc; padding: 10px; width: 80px; height: 80px; margin: 0 auto;">
            <div style="text-align: center; line-height: 60px; font-size: 10px;">QR CODE</div>
        </div>
        <div style="font-size: 10px; margin-top: 5px;">Seguimiento Online</div>
    </div>

    <!-- Expediente Info -->
    <div class="content">
        <div class="field">
            <span class="field-label">N° EXPEDIENTE:</span>
            <span class="field-value">{{ $expediente->codigo_expediente }}</span>
        </div>
        
        <div class="field">
            <span class="field-label">FECHA RECEPCIÓN:</span>
            <span class="field-value">{{ $expediente->created_at->format('d/m/Y H:i:s') }}</span>
        </div>
        
        <div class="field">
            <span class="field-label">CANAL:</span>
            <span class="field-value">{{ strtoupper($expediente->canal) }}</span>
        </div>
    </div>

    <!-- Remitente Info -->
    <div class="content">
        <h4 style="margin-bottom: 10px; color: #cc5500;">DATOS DEL REMITENTE</h4>
        
        <div class="field">
            <span class="field-label">NOMBRE/RAZÓN SOCIAL:</span>
            <span class="field-value">{{ $expediente->remitente ?? ($expediente->persona->nombre_completo ?? 'N/A') }}</span>
        </div>
        
        <div class="field">
            <span class="field-label">DOCUMENTO:</span>
            <span class="field-value">
                {{ $expediente->persona->tipo_documento ?? 'DNI' }}: {{ $expediente->dni_remitente ?? ($expediente->persona->numero_documento ?? 'N/A') }}
            </span>
        </div>
        
        @if($expediente->persona && $expediente->persona->telefono)
        <div class="field">
            <span class="field-label">TELÉFONO:</span>
            <span class="field-value">{{ $expediente->persona->telefono }}</span>
        </div>
        @endif
        
        @if($expediente->persona && $expediente->persona->email)
        <div class="field">
            <span class="field-label">EMAIL:</span>
            <span class="field-value">{{ $expediente->persona->email }}</span>
        </div>
        @endif
    </div>

    <!-- Trámite Info -->
    <div class="content">
        <h4 style="margin-bottom: 10px; color: #cc5500;">INFORMACIÓN DEL DOCUMENTO</h4>

        <div class="field">
            <span class="field-label">TIPO DOCUMENTO:</span>
            <span class="field-value">{{ $expediente->tipo_documento_entrante ?? 'N/A' }}@if($expediente->numero_documento_entrante) N° {{ $expediente->numero_documento_entrante }}@endif</span>
        </div>

        <div class="field">
            <span class="field-label">TIPO TRÁMITE:</span>
            <span class="field-value">{{ $expediente->tipoTramite->nombre ?? 'POR CLASIFICAR' }}</span>
        </div>

        <div class="field">
            <span class="field-label">ASUNTO:</span>
            <span class="field-value">{{ $expediente->asunto }}</span>
        </div>

        <div class="field">
            <span class="field-label">FOLIOS:</span>
            <span class="field-value">{{ $expediente->folios ?? 1 }}</span>
        </div>
        
        @if($expediente->observaciones)
        <div class="field">
            <span class="field-label">OBSERVACIONES:</span>
            <span class="field-value">{{ $expediente->observaciones }}</span>
        </div>
        @endif
    </div>

    <!-- Documentos Recibidos -->
    <div class="documents-section">
        <div class="documents-title">DOCUMENTOS RECIBIDOS</div>
        @if($expediente->documentos->count() > 0)
            @foreach($expediente->documentos as $index => $documento)
            <div class="document-item">
                {{ $index + 1 }}. {{ $documento->nombre }} ({{ strtoupper($documento->tipo) }})
            </div>
            @endforeach
        @else
            <div class="document-item">1. Documento Principal (ENTRADA)</div>
        @endif
        
        <!-- Documentos Estándar -->
        <div class="document-item">{{ ($expediente->documentos->count() + 1) }}. Formato Único de Trámite (FUT)</div>
        <div class="document-item">{{ ($expediente->documentos->count() + 2) }}. Documento de Identidad</div>
        <div class="document-item">{{ ($expediente->documentos->count() + 3) }}. Comprobante de Pago</div>
    </div>

    <!-- Tracking Info -->
    <div class="tracking-info">
        <strong>SEGUIMIENTO DE EXPEDIENTE:</strong><br>
        • Web: {{ url('/consulta/' . $expediente->codigo_expediente) }}<br>
        • Código: {{ $expediente->codigo_expediente }}<br>
        • Estado actual: {{ strtoupper($expediente->getEstadoFormateado()) }}
    </div>

    <!-- Important Notes -->
    <div class="important-note">
        <strong>IMPORTANTE:</strong><br>
        • Conserve este cargo como comprobante de presentación de su trámite.<br>
        • Puede consultar el estado de su expediente las 24 horas en nuestra web.<br>
        • Los plazos de atención se cuentan desde la fecha de recepción.<br>
        • Para cualquier consulta, presente este cargo junto con su documento de identidad.
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    <strong>RECIBIDO POR</strong><br>
                    {{ auth()->user()->name }}<br>
                    {{ auth()->user()->role->nombre }}
                </div>
            </div>
            
            <div class="signature-box">
                <div class="signature-line">
                    <strong>ENTREGADO POR</strong><br>
                    {{ $expediente->remitente ?? ($expediente->persona->nombre_completo ?? '') }}<br>
                    {{ $expediente->persona->tipo_documento ?? 'DNI' }}: {{ $expediente->dni_remitente ?? ($expediente->persona->numero_documento ?? '') }}
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px; font-size: 10px; color: #666;">
            Documento generado automáticamente el {{ now()->format('d/m/Y H:i:s') }}<br>
            Sistema de Mesa de Partes Digital - DRTC
        </div>
    </div>
</body>
</html>