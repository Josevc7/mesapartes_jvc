<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expediente Registrado</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: #007bff; color: white; padding: 20px; text-align: center;">
            <h1>Mesa de Partes Digital</h1>
            <h2>Expediente Registrado Exitosamente</h2>
        </div>
        
        <div style="padding: 20px; background: #f8f9fa;">
            <p>Estimado/a ciudadano/a,</p>
            
            <p>Su expediente ha sido registrado correctamente en nuestro sistema:</p>
            
            <div style="background: white; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0;">
                <strong>Código de Expediente:</strong> {{ $expediente->codigo_expediente }}<br>
                <strong>Asunto:</strong> {{ $expediente->asunto }}<br>
                <strong>Fecha de Registro:</strong> {{ $expediente->created_at->format('d/m/Y H:i') }}<br>
                <strong>Estado:</strong> {{ $expediente->estado }}
            </div>
            
            <p>Puede consultar el estado de su expediente en cualquier momento ingresando a nuestro portal web con el código proporcionado.</p>
            
            <p>Gracias por utilizar nuestros servicios digitales.</p>
            
            <hr>
            <small style="color: #666;">
                Este es un mensaje automático, por favor no responda a este correo.
            </small>
        </div>
    </div>
</body>
</html>