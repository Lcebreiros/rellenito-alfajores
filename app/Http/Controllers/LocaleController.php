<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Cambia el idioma de la sesión del usuario y persiste la preferencia en cookie.
     *
     * La cookie dura 1 año y es httpOnly + SameSite=Lax para seguridad.
     * El middleware SetLocale la lee en el siguiente request y la sincroniza a sesión.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, SetLocale::SUPPORTED_LOCALES, true), 404);

        session(['locale' => $locale]);

        return redirect()
            ->back(fallback: route('dashboard'))
            ->withCookie(
                cookie()->forever(name: 'locale', value: $locale, secure: $request->secure(), sameSite: 'Lax')
            );
    }
}
