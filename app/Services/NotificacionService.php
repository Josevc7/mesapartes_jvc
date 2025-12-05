<?php

namespace App\Services;

use App\Models\Expediente;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    public function notificarNuevoExpediente(Expediente $expediente)
    {
        try {
            // Notificar a Mesa de Partes
            $mesaPartes = User::where('rol_id', 2)->where('activo', true)->get();
            foreach ($mesaPartes as $usuario) {
                Mail::send('emails.nuevo-expediente', compact('expediente'), function($message) use ($usuario, $expediente) {
                    $message->to($usuario->email)
                           ->subject('Nuevo Expediente Registrado: ' . $expediente->codigo_expediente);
                });
            }
            
            // Notificar al ciudadano
            if ($expediente->ciudadano) {
                Mail::send('emails.confirmacion-registro', compact('expediente'), function($message) use ($expediente) {
                    $message->to($expediente->ciudadano->email)
                           ->subject('Expediente Registrado: ' . $expediente->codigo_expediente);
                });
            }
        } catch (\Exception $e) {
            Log::error('Error enviando notificación nuevo expediente: ' . $e->getMessage());
        }
    }

    public function notificarDerivacion(Expediente $expediente)
    {
        try {
            if ($expediente->funcionarioAsignado) {
                Mail::send('emails.expediente-derivado', compact('expediente'), function($message) use ($expediente) {
                    $message->to($expediente->funcionarioAsignado->email)
                           ->subject('Expediente Derivado: ' . $expediente->codigo_expediente);
                });
            }
        } catch (\Exception $e) {
            Log::error('Error enviando notificación derivación: ' . $e->getMessage());
        }
    }

    public function notificarResolucion(Expediente $expediente)
    {
        try {
            if ($expediente->ciudadano) {
                Mail::send('emails.expediente-resuelto', compact('expediente'), function($message) use ($expediente) {
                    $message->to($expediente->ciudadano->email)
                           ->subject('Expediente Resuelto: ' . $expediente->codigo_expediente);
                });
            }
        } catch (\Exception $e) {
            Log::error('Error enviando notificación resolución: ' . $e->getMessage());
        }
    }

    public function notificarVencimiento(Expediente $expediente)
    {
        try {
            // Notificar al funcionario asignado
            if ($expediente->funcionarioAsignado) {
                Mail::send('emails.expediente-vencido', compact('expediente'), function($message) use ($expediente) {
                    $message->to($expediente->funcionarioAsignado->email)
                           ->subject('URGENTE: Expediente Vencido - ' . $expediente->codigo_expediente);
                });
            }

            // Notificar al jefe de área
            if ($expediente->area) {
                $jefeArea = User::where('rol_id', 3)->where('area_id', $expediente->area_id)->first();
                if ($jefeArea) {
                    Mail::send('emails.expediente-vencido', compact('expediente'), function($message) use ($jefeArea, $expediente) {
                        $message->to($jefeArea->email)
                               ->subject('ALERTA: Expediente Vencido en su Área - ' . $expediente->codigo_expediente);
                    });
                }
            }
        } catch (\Exception $e) {
            Log::error('Error enviando notificación vencimiento: ' . $e->getMessage());
        }
    }
}