<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    protected $pdfService;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->pdfService = app(InvoicePdfService::class);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $voucherTypeName = $this->getVoucherTypeName($this->invoice->voucher_type);

        return new Envelope(
            subject: "{$voucherTypeName} {$this->invoice->full_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'voucherTypeName' => $this->getVoucherTypeName($this->invoice->voucher_type),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Generar el PDF si no existe
        if (!$this->invoice->pdf_path || !Storage::disk('public')->exists($this->invoice->pdf_path)) {
            $this->pdfService->generatePdf($this->invoice);
        }

        $pdfPath = Storage::disk('public')->path($this->invoice->pdf_path);

        return [
            Attachment::fromPath($pdfPath)
                ->as("factura-{$this->invoice->full_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }

    /**
     * Obtener nombre del tipo de comprobante
     */
    protected function getVoucherTypeName($voucherType)
    {
        $names = [
            'FC-A' => 'Factura A',
            'FC-B' => 'Factura B',
            'FC-C' => 'Factura C',
            'NC-A' => 'Nota de Crédito A',
            'NC-B' => 'Nota de Crédito B',
            'NC-C' => 'Nota de Crédito C',
            'ND-A' => 'Nota de Débito A',
            'ND-B' => 'Nota de Débito B',
            'ND-C' => 'Nota de Débito C',
        ];

        return $names[$voucherType] ?? 'Comprobante';
    }
}
