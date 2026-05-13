<x-mail::message>
# ¡Tu cuenta está activa, {{ $user->name }}!

Nos alegra informarte que tu solicitud de acceso a **{{ config('app.name') }}** fue aprobada.

@if($temporaryPassword)
Creamos tu cuenta con las siguientes credenciales temporales:

<x-mail::panel>
**Email:** {{ $user->email }}<br>
**Contraseña temporal:** {{ $temporaryPassword }}
</x-mail::panel>

Por seguridad, te recomendamos cambiar tu contraseña al ingresar por primera vez.
@else
Tu cuenta ya está habilitada. Podés ingresar con el email y la contraseña que registraste.
@endif

<x-mail::button :url="$loginUrl">
Ingresar ahora
</x-mail::button>

Si tenés alguna duda, no dudes en contactarnos.

Saludos,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
