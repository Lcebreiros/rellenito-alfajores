<x-mail::message>
# {{ $voucherTypeName }} {{ $invoice->full_number }}

Estimado/a **{{ $invoice->client_name }}**,

Adjuntamos el comprobante solicitado:

<x-mail::panel>
**Fecha:** {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}<br>
**Comprobante:** {{ $invoice->full_number }}<br>
**Total:** ${{ number_format($invoice->total, 2, ',', '.') }}
</x-mail::panel>

El comprobante se encuentra adjunto en formato PDF.

@if($invoice->cae)
**CAE:** {{ $invoice->cae }}<br>
**Vencimiento CAE:** {{ \Carbon\Carbon::parse($invoice->cae_expiration)->format('d/m/Y') }}
@endif

Muchas gracias por su confianza.

Saludos cordiales,<br>
{{ config('app.name') }}
</x-mail::message>
