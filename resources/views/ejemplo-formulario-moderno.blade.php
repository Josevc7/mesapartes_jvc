<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplo - Formulario Moderno</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Modern Forms CSS -->
    <link href="{{ asset('css/modern-forms.css') }}" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
    </style>
</head>
<body>

<div class="modern-form-container">
    <div class="modern-form-card">

        <!-- ENCABEZADO -->
        <div class="modern-form-header">
            <div class="modern-form-header-content">
                <div class="modern-form-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="modern-form-title">
                    <h1>Registrar Nuevo Expediente</h1>
                    <p>Complete el formulario con los datos del trámite - Todos los campos marcados con * son obligatorios</p>
                </div>
            </div>
        </div>

        <!-- CUERPO DEL FORMULARIO -->
        <div class="modern-form-body">

            <!-- ALERTA DE ÉXITO (EJEMPLO) -->
            <div class="alert-modern alert-modern-success">
                <div class="alert-modern-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h6 class="mb-1 fw-bold">¡Formulario guardado exitosamente!</h6>
                    <p class="mb-0">Su expediente ha sido registrado con el código: <strong>EXP-2026-00123</strong></p>
                </div>
            </div>

            <!-- ALERTA DE ERROR (EJEMPLO) -->
            <div class="alert-modern alert-modern-danger">
                <div class="alert-modern-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div>
                    <h6 class="mb-1 fw-bold">Error en el formulario</h6>
                    <p class="mb-0">Por favor corrija los errores señalados antes de continuar.</p>
                </div>
            </div>

            <form>

                <!-- SECCIÓN 1: DATOS DEL SOLICITANTE -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="form-section-title">
                            <h3>Datos del Solicitante</h3>
                            <p>Información de la persona o empresa que presenta el trámite</p>
                        </div>
                    </div>

                    <!-- Primera fila: 4 campos -->
                    <div class="form-row-4cols">
                        <!-- Tipo de Persona -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-tag text-primary"></i>
                                Tipo de Persona
                                <span class="form-label-required">*</span>
                            </label>
                            <select class="form-select" id="tipo_persona" required>
                                <option value="">Seleccione...</option>
                                <option value="NATURAL">Persona Natural</option>
                                <option value="JURIDICA">Persona Jurídica</option>
                            </select>
                        </div>

                        <!-- Tipo de Documento -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-id-card text-warning"></i>
                                Tipo de Documento
                                <span class="form-label-required">*</span>
                            </label>
                            <select class="form-select" id="tipo_documento" required>
                                <option value="">Seleccione...</option>
                                <option value="DNI">DNI</option>
                                <option value="CE">Carnet Extranjería</option>
                                <option value="RUC">RUC</option>
                                <option value="PASAPORTE">Pasaporte</option>
                            </select>
                        </div>

                        <!-- Número de Documento - CON VALIDACIÓN -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-hashtag text-info"></i>
                                Número Documento
                                <span class="form-label-required">*</span>
                            </label>
                            <input type="text"
                                   class="form-control is-valid"
                                   id="numero_documento"
                                   placeholder="Ej: 12345678"
                                   value="12345678"
                                   required>
                            <div class="valid-feedback">
                                Documento válido
                            </div>
                        </div>

                        <!-- Nombres - EJEMPLO CON ERROR -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-signature text-success"></i>
                                Nombres
                                <span class="form-label-required">*</span>
                            </label>
                            <input type="text"
                                   class="form-control is-invalid"
                                   placeholder="Ingrese nombres"
                                   value="">
                            <div class="invalid-feedback">
                                Campo obligatorio
                            </div>
                        </div>
                    </div>

                    <!-- Segunda fila: 4 campos -->
                    <div class="form-row-4cols">
                        <!-- Apellido Paterno -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-signature text-success"></i>
                                Apellido Paterno
                                <span class="form-label-required">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Apellido paterno">
                        </div>

                        <!-- Apellido Materno -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-signature text-success"></i>
                                Apellido Materno
                                <span class="form-label-optional">(Opcional)</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Apellido materno">
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-envelope text-danger"></i>
                                Correo Electrónico
                                <span class="form-label-optional">(Opcional)</span>
                            </label>
                            <input type="email"
                                   class="form-control"
                                   placeholder="ejemplo@correo.com">
                        </div>

                        <!-- Teléfono -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-phone text-secondary"></i>
                                Teléfono
                                <span class="form-label-optional">(Opcional)</span>
                            </label>
                            <input type="tel"
                                   class="form-control"
                                   placeholder="999 888 777">
                        </div>
                    </div>

                    <!-- Tercera fila: Dirección ocupa todo el ancho -->
                    <div class="form-row-4cols">
                        <!-- Dirección -->
                        <div class="form-group form-col-span-4">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt text-danger"></i>
                                Dirección
                                <span class="form-label-optional">(Opcional)</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Calle, Av., Jr., etc.">
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i>
                                Ingrese su dirección completa para notificaciones
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: DATOS DEL TRÁMITE -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="form-section-title">
                            <h3>Datos del Trámite</h3>
                            <p>Especifique el tipo de trámite y detalles del documento</p>
                        </div>
                    </div>

                    <!-- Primera fila: 3 campos -->
                    <div class="form-row-4cols">
                        <!-- Tipo de Trámite - Ocupa 2 columnas -->
                        <div class="form-group form-col-span-2">
                            <label class="form-label">
                                <i class="fas fa-tasks text-primary"></i>
                                Tipo de Trámite
                                <span class="form-label-required">*</span>
                            </label>
                            <select class="form-select" required>
                                <option value="">Seleccione el tipo de trámite...</option>
                                <option value="1">Licencia de Conducir</option>
                                <option value="2">Certificado de Inspección Técnica Vehicular</option>
                                <option value="3">Permiso de Operación</option>
                                <option value="4">Autorización de Ruta</option>
                                <option value="5">Consulta General</option>
                            </select>
                        </div>

                        <!-- Prioridad -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-exclamation-circle text-warning"></i>
                                Prioridad
                            </label>
                            <select class="form-select">
                                <option value="normal" selected>Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>

                        <!-- Fecha Plazo -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar text-info"></i>
                                Fecha Plazo
                            </label>
                            <input type="date" class="form-control">
                        </div>
                    </div>

                    <!-- Segunda fila: Asunto ocupa todo el ancho -->
                    <div class="form-row-4cols">
                        <div class="form-group form-col-span-4">
                            <label class="form-label">
                                <i class="fas fa-align-left text-info"></i>
                                Asunto del Trámite
                                <span class="form-label-required">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Breve descripción del motivo de su trámite"
                                   maxlength="200"
                                   required>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i>
                                Máximo 200 caracteres
                            </div>
                        </div>
                    </div>

                    <!-- Tercera fila: Descripción ocupa todo el ancho -->
                    <div class="form-row-4cols">
                        <div class="form-group form-col-span-4">
                            <label class="form-label">
                                <i class="fas fa-comment-alt text-secondary"></i>
                                Descripción / Observaciones
                                <span class="form-label-optional">(Opcional)</span>
                            </label>
                            <textarea class="form-control"
                                      rows="4"
                                      placeholder="Detalle información adicional sobre su trámite..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: DOCUMENTOS ADJUNTOS -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-icon">
                            <i class="fas fa-paperclip"></i>
                        </div>
                        <div class="form-section-title">
                            <h3>Documentos Adjuntos</h3>
                            <p>Adjunte los documentos requeridos en formato PDF</p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Documento Principal -->
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                    Documento Principal (PDF)
                                    <span class="form-label-required">*</span>
                                </label>
                                <input type="file"
                                       class="form-control"
                                       accept=".pdf"
                                       required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Tamaño máximo: 10MB | Formato: PDF únicamente
                                </div>
                            </div>
                        </div>

                        <!-- Documentos Adicionales -->
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-files text-primary"></i>
                                    Documentos Adicionales
                                    <span class="form-label-optional">(Opcional)</span>
                                </label>
                                <input type="file"
                                       class="form-control"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       multiple>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Puede adjuntar múltiples archivos | Formatos: PDF, JPG, PNG
                                </div>
                            </div>
                        </div>

                        <!-- Checklist de Documentos -->
                        <div class="col-12">
                            <label class="form-label mb-3">
                                <i class="fas fa-check-square text-success"></i>
                                Verificación de Documentos Requeridos
                                <span class="form-label-required">*</span>
                            </label>

                            <div class="form-check-modern">
                                <input type="checkbox" id="doc1" value="dni">
                                <label for="doc1">
                                    <strong>DNI o Documento de Identidad</strong>
                                    <small class="d-block text-muted">Copia legible del documento de identidad</small>
                                </label>
                            </div>

                            <div class="form-check-modern">
                                <input type="checkbox" id="doc2" value="fut">
                                <label for="doc2">
                                    <strong>Formato Único de Trámite (FUT)</strong>
                                    <small class="d-block text-muted">Formulario oficial debidamente llenado</small>
                                </label>
                            </div>

                            <div class="form-check-modern">
                                <input type="checkbox" id="doc3" value="pago">
                                <label for="doc3">
                                    <strong>Comprobante de Pago</strong>
                                    <small class="d-block text-muted">Voucher o recibo de pago de derecho de trámite</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 4: TÉRMINOS Y CONDICIONES -->
                <div class="form-section">
                    <div class="alert-modern alert-modern-info">
                        <div class="alert-modern-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <h6 class="mb-2 fw-bold">Información Importante</h6>
                            <ul class="mb-0 ps-3">
                                <li>Todos los datos proporcionados son de carácter confidencial</li>
                                <li>Recibirá un código de seguimiento al finalizar el registro</li>
                                <li>El tiempo de respuesta varía según el tipo de trámite</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-check-modern bg-light">
                        <input type="checkbox" id="terminos" required>
                        <label for="terminos">
                            <strong>Acepto los términos y condiciones</strong>
                            <small class="d-block text-muted">
                                Declaro que la información proporcionada es verídica y autorizo el tratamiento de mis datos personales
                            </small>
                        </label>
                    </div>
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="border-top pt-4 mt-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button type="button" class="btn-modern btn-modern-secondary btn-modern-lg">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn-modern btn-modern-primary btn-modern-lg">
                                <i class="fas fa-paper-plane"></i>
                                Enviar Expediente
                                <span class="spinner-modern d-none"></span>
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script de validación de documentos -->
<script src="{{ asset('js/validacion-documentos.js') }}"></script>

<!-- Script de demostración -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simular envío del formulario
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');
    const spinner = submitBtn.querySelector('.spinner-modern');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Mostrar spinner
        spinner.classList.remove('d-none');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-modern"></span> Enviando...';

        // Simular proceso de envío
        setTimeout(function() {
            spinner.classList.add('d-none');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Expediente';

            alert('¡Formulario enviado con éxito! (Demo)');
        }, 2000);
    });
});
</script>

</body>
</html>
