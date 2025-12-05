<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Acuse de Recibo - {{ $expediente->codigo_expediente }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; text-decoration: underline; }
        .content { margin: 20px 0; }
        .expediente-box { border: 2px solid #000; padding: 15px; margin: 20px 0; }
        .field { margin: 8px 0; }
        .field strong { display: inline-block; width: 150px; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; }
        .qr-section { float: right; text-align: center; margin: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">MESA DE PARTES DIGITAL</div>
        <div>{{ config('app.name') }}</div>
        <div class="title">ACUSE DE RECIBO</div>
    </div>

    <div class="content">
        <div class="expediente-box">
            <h3 style="margin-top: 0;">INFORMACIÓN DEL EXPEDIENTE</h3>
            
            <div class="field">
                <strong>Código:</strong> {{ $expediente->codigo_expediente }}
            </div>
            
            <div class="field">
                <strong>Fecha de Registro:</strong> {{ $expediente->fecha_registro }}
            </div>
            
            <div class="field">
                <strong>Solicitante:</strong> {{ $expediente->ciudadano->name }}
            </div>
            
            <div class="field">
                <strong>DNI:</strong> {{ $expediente->ciudadano->dni }}
            </div>
            
            <div class="field">
                <strong>Tipo de Trámite:</strong> {{ $expediente->tipoTramite->nombre }}
            </div>
            
            <div class="field">
                <strong>Asunto:</strong> {{ $expediente->asunto }}
            </div>
            
            <div class="field">
                <strong>Canal:</strong> {{ ucfirst($expediente->canal) }}
            </div>
            
            <div class="field">
                <strong>Estado:</strong> {{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}
            </div>
        </div>

        <div style="margin: 30px 0;">
            <h4>DOCUMENTOS ADJUNTOS:</h4>
            @if($expediente->documentos->count() > 0)
                <ul>
                    @foreach($expediente->documentos as $documento)
                    <li>{{ $documento->nombre }} ({{ ucfirst($documento->tipo) }})</li>
                    @endforeach
                </ul>
            @else
                <p>No se adjuntaron documentos.</p>
            @endif
        </div>

        <div style="margin: 30px 0; padding: 15px; background-color: #f0f0f0;">
            <h4>IMPORTANTE:</h4>
            <ul>
                <li>Conserve este acuse de recibo para el seguimiento de su trámite.</li>
                <li>Puede consultar el estado en: {{ url('/consulta/' . $expediente->codigo_expediente) }}</li>
                <li>El plazo de atención es de {{ $expediente->tipoTramite->plazo_dias }} días hábiles.</li>
                <li>Para consultas adicionales, acérquese a nuestras oficinas.</li>
            </ul>
        </div>
    </div>

    <div class="footer">
        <p>Documento generado automáticamente el {{ $fecha_generacion }}</p>
        <p>Mesa de Partes Digital - {{ config('app.name') }}</p>
        <p>Este documento tiene validez oficial</p>
    </div>
</body>
</html>