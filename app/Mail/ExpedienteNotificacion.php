<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Expediente;

class ExpedienteNotificacion extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $expediente;
    public $tipo;
    public $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(Expediente $expediente, string $tipo, string $pdfPath = null)
    {
        $this->expediente = $expediente;
        $this->tipo = $tipo;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'registro' => 'Expediente Registrado - ' . $this->expediente->codigo_expediente,
            'derivacion' => 'Expediente Derivado - ' . $this->expediente->codigo_expediente,
            'recepcion' => 'Expediente Recibido - ' . $this->expediente->codigo_expediente,
            'resolucion' => 'Expediente Resuelto - ' . $this->expediente->codigo_expediente,
            'archivo' => 'Expediente Archivado - ' . $this->expediente->codigo_expediente,
        ];

        return new Envelope(
            subject: $subjects[$this->tipo] ?? 'Notificación de Expediente',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.expediente-notificacion',
            with: [
                'expediente' => $this->expediente,
                'tipo' => $this->tipo
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->pdfPath && file_exists(storage_path('app/' . $this->pdfPath))) {
            $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromPath(storage_path('app/' . $this->pdfPath))
                ->as('acuse-recibo-' . $this->expediente->codigo_expediente . '.pdf')
                ->withMime('application/pdf');
        }
        
        return $attachments;
    }
}