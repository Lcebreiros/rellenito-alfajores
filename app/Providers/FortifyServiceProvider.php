<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateUserWithInvitation;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateUserWithInvitation::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        /**
         * AuthenticateUsing: evitamos login si el usuario está suspendido
         * Lanza una ValidationException con mensaje claro cuando está suspendido.
         */
        Fortify::authenticateUsing(function (Request $request) {
            $email = $request->input(Fortify::username(), 'email');
            $password = $request->password;

            /** @var User|null $user */
            $user = User::where('email', $email)->first();

            // si no existe o la password no coincide, devolvemos null (Fortify seguirá con el flujo de error)
            if (! $user || ! Hash::check($password, $user->password)) {
                return null;
            }

                        // si está suspendido / inactivo lanzamos ValidationException
            if (! $user->is_active) {
                $reason = $user->suspended_reason ?? 'Contactá con soporte para más info.';
                throw ValidationException::withMessages([
                    Fortify::username() => ["Cuenta suspendida: $reason"],
                ]);
            }


            // todo ok: devolvemos el usuario para que Fortify lo loguee
            return $user;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
