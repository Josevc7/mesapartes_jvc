/**
 * Validación de Documentos de Identidad - Sistema Mesa de Partes
 * Valida en tiempo real DNI, RUC, CE y PASAPORTE según estándares peruanos
 */

document.addEventListener('DOMContentLoaded', function() {
    const tipoDocumentoSelect = document.getElementById('tipo_documento');
    const numeroDocumentoInput = document.getElementById('numero_documento');

    if (!tipoDocumentoSelect || !numeroDocumentoInput) {
        return; // Si no existen los elementos, salir
    }

    // Configurar validaciones según el tipo de documento
    tipoDocumentoSelect.addEventListener('change', function() {
        configurarValidacion(this.value);
    });

    // Validar en tiempo real mientras se escribe
    numeroDocumentoInput.addEventListener('input', function() {
        validarDocumento(tipoDocumentoSelect.value, this.value);
    });

    // Configurar la validación inicial si ya hay un tipo seleccionado
    if (tipoDocumentoSelect.value) {
        configurarValidacion(tipoDocumentoSelect.value);
    }
});

/**
 * Configura los atributos del input según el tipo de documento
 */
function configurarValidacion(tipoDocumento) {
    const numeroInput = document.getElementById('numero_documento');
    const label = document.querySelector('label[for="numero_documento"]');

    // Limpiar el input
    numeroInput.value = '';
    numeroInput.classList.remove('is-valid', 'is-invalid');

    // Limpiar mensaje de error previo
    const feedbackDiv = numeroInput.nextElementSibling;
    if (feedbackDiv && feedbackDiv.classList.contains('invalid-feedback')) {
        feedbackDiv.remove();
    }

    switch(tipoDocumento) {
        case 'DNI':
            numeroInput.placeholder = 'Ej: 12345678';
            numeroInput.maxLength = 8;
            numeroInput.pattern = '\\d{8}';
            numeroInput.inputMode = 'numeric';
            if (label) label.innerHTML = '<i class="fas fa-id-card text-primary me-2"></i>Número de DNI *';
            break;

        case 'RUC':
            numeroInput.placeholder = 'Ej: 20123456789';
            numeroInput.maxLength = 11;
            numeroInput.pattern = '\\d{11}';
            numeroInput.inputMode = 'numeric';
            if (label) label.innerHTML = '<i class="fas fa-building text-warning me-2"></i>Número de RUC *';
            break;

        case 'CE':
            numeroInput.placeholder = 'Ej: A1234567B';
            numeroInput.maxLength = 12;
            numeroInput.pattern = '[A-Z0-9]{9,12}';
            numeroInput.inputMode = 'text';
            if (label) label.innerHTML = '<i class="fas fa-passport text-info me-2"></i>Carnet de Extranjería *';
            // Convertir a mayúsculas automáticamente
            numeroInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
            break;

        case 'PASAPORTE':
            numeroInput.placeholder = 'Ej: AB123456';
            numeroInput.maxLength = 12;
            numeroInput.pattern = '[A-Z0-9]{7,12}';
            numeroInput.inputMode = 'text';
            if (label) label.innerHTML = '<i class="fas fa-passport text-success me-2"></i>Número de Pasaporte *';
            // Convertir a mayúsculas automáticamente
            numeroInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
            break;
    }
}

/**
 * Valida el número de documento en tiempo real
 */
function validarDocumento(tipoDocumento, numeroDocumento) {
    const numeroInput = document.getElementById('numero_documento');
    let valido = false;
    let mensajeError = '';

    // Si está vacío, no validar aún
    if (!numeroDocumento) {
        numeroInput.classList.remove('is-valid', 'is-invalid');
        return;
    }

    switch(tipoDocumento) {
        case 'DNI':
            // DNI: Exactamente 8 dígitos numéricos
            valido = /^\d{8}$/.test(numeroDocumento);
            mensajeError = 'El DNI debe contener exactamente 8 dígitos numéricos.';
            break;

        case 'RUC':
            // RUC: Exactamente 11 dígitos numéricos
            valido = /^\d{11}$/.test(numeroDocumento);
            mensajeError = 'El RUC debe contener exactamente 11 dígitos numéricos.';
            break;

        case 'CE':
            // CE: 9 o 12 caracteres alfanuméricos
            valido = /^[A-Z0-9]{9}$|^[A-Z0-9]{12}$/.test(numeroDocumento);
            mensajeError = 'El Carnet de Extranjería debe contener 9 o 12 caracteres alfanuméricos.';
            break;

        case 'PASAPORTE':
            // PASAPORTE: 7 a 12 caracteres alfanuméricos
            valido = /^[A-Z0-9]{7,12}$/.test(numeroDocumento);
            mensajeError = 'El Pasaporte debe contener entre 7 y 12 caracteres alfanuméricos.';
            break;
    }

    // Aplicar clases de Bootstrap según validación
    if (valido) {
        numeroInput.classList.remove('is-invalid');
        numeroInput.classList.add('is-valid');

        // Remover mensaje de error si existe
        const feedbackDiv = numeroInput.nextElementSibling;
        if (feedbackDiv && feedbackDiv.classList.contains('invalid-feedback')) {
            feedbackDiv.remove();
        }
    } else {
        numeroInput.classList.remove('is-valid');
        numeroInput.classList.add('is-invalid');

        // Agregar o actualizar mensaje de error
        let feedbackDiv = numeroInput.nextElementSibling;
        if (!feedbackDiv || !feedbackDiv.classList.contains('invalid-feedback')) {
            feedbackDiv = document.createElement('div');
            feedbackDiv.className = 'invalid-feedback';
            numeroInput.parentNode.insertBefore(feedbackDiv, numeroInput.nextSibling);
        }
        feedbackDiv.textContent = mensajeError;
    }
}

/**
 * Prevenir caracteres no permitidos según el tipo de documento
 */
document.addEventListener('DOMContentLoaded', function() {
    const numeroInput = document.getElementById('numero_documento');
    const tipoSelect = document.getElementById('tipo_documento');

    if (!numeroInput || !tipoSelect) return;

    numeroInput.addEventListener('keypress', function(e) {
        const tipoDoc = tipoSelect.value;
        const charCode = e.which || e.keyCode;
        const char = String.fromCharCode(charCode);

        // Para DNI y RUC, solo permitir números
        if ((tipoDoc === 'DNI' || tipoDoc === 'RUC') && !/^\d$/.test(char)) {
            e.preventDefault();
            return false;
        }

        // Para CE y PASAPORTE, permitir letras y números (se convertirán a mayúsculas)
        if ((tipoDoc === 'CE' || tipoDoc === 'PASAPORTE') && !/^[A-Za-z0-9]$/.test(char)) {
            e.preventDefault();
            return false;
        }
    });
});
