<?php

// Configuración PHPStan para Laravel
// Este archivo suprime falsos positivos conocidos en Laravel

return [
    // Ignorar errores de tipos dinámicos en controllers
    'dynallyCallableClasses' => [
        'App\\Http\\Controllers\\*',
        'App\\Models\\*',
    ],
];
