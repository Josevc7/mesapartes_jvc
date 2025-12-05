<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notificación de Expediente</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
        .expediente-info { background: white; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mesa de Partes Digital</h1>
            <h2>
                @switch($tipo)
                    @case('registro')
                        Expediente Registrado
                        @break
                    @case('derivacion')
                        Expediente Derivado
                        @break
                    @case('recepcion')
                        Expediente Recibido
                        @break
                    @case('resolucion')
                        Expediente Resuelto
                        @break
                    @case('archivo')
                        Expediente Archivado
                        @break
                    @default
                        Notificación de Expediente
                @endswitch
            </h2>
        </div>

        <div class="content">
            <p>Estimado/a <strong>{{ $expediente->ciudadano->name }}</strong>,</p>

            @switch($tipo)
                @case('registro')
                    <p>Su expediente ha sido registrado exitosamente en nuestro sistema.</p>
                    @break
                @case('derivacion')
                    <p>Su expediente ha sido derivado al área correspondiente para su atención.</p>
                    @break
                @case('recepcion')
                    <p>Su expediente ha sido recibido por el funcionario asignado y está en proceso de atención.</p>
                    @break
                @case('resolucion')
                    <p>Su expediente ha sido resuelto. Puede consultar los documentos de respuesta en el sistema.</p>
                    @break
                @case('archivo')
                    <p>Su expediente ha sido archivado. El trámite ha sido completado.</p>
                    @break
            @endswitch

            <div class="expediente-info">
                <h3>Información del Expediente</h3>
                <p><strong>Código:</strong> {{ $expediente->codigo_expediente }}</p>
                <p><strong>Asunto:</strong> {{ $expediente->asunto }}</p>
                <p><strong>Tipo de Trámite:</strong> {{ $expediente->tipoTramite->nombre }}</p>
                <p><strong>Fecha de Registro:</strong> {{ $expediente->fecha_registro }}</p>
                <p><strong>Estado Actual:</strong> 
                    <span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 3px;">
                        {{ ucfirst(str_replace('_', ' ', $expediente->estado)) }}
                    </span>
                </p>
            </div>

            @if($tipo == 'registro')
            <p><strong>Importante:</strong> Guarde el código de expediente <strong>{{ $expediente->codigo_expediente }}</strong> para realizar el seguimiento de su trámite.</p>
            @endif

            <p>Puede consultar el estado de su expediente en cualquier momento usando el siguiente enlace:</p>
            
            <div style="text-align: center; margin: 20px 0;">
                <a href="{{ url('/consulta/' . $expediente->codigo_expediente) }}" class="btn">
                    Consultar Expediente
                </a>
            </div>

            @if($tipo == 'resolucion')
            <p><strong>Nota:</strong> Los documentos de respuesta están disponibles para descarga en el sistema de seguimiento.</p>
            @endif
        </div>

        <div class="footer">
            <p>Este es un mensaje automático, por favor no responda a este correo.</p>
            <p>Mesa de Partes Digital - {{ config('app.name') }}</p>
            <p>Para consultas adicionales, acérquese a nuestras oficinas o llame al teléfono de atención.</p>
        </div>
    </div>
</body>
</html>