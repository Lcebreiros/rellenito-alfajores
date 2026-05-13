<x-mail::message>
# Hola, {{ $trialRequest->name }}

Gracias por tu interés en **{{ config('app.name') }}**.

Lamentablemente, en esta oportunidad no pudimos aprobar tu solicitud de acceso.

@if($trialRequest->notes)
<x-mail::panel>
{{ $trialRequest->notes }}
</x-mail::panel>
@endif

Si creés que hubo un error o querés más información, podés responder este email y te ayudamos.

Saludos,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
