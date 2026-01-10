<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use Throwable;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;
use App\Actions\User\ActiveOauthProviderAction;
use App\Actions\User\HandleOauthCallbackAction;
use App\Exceptions\OAuthAccountLinkingException;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

final class OauthController extends Controller
{
    public function __construct(
        private readonly HandleOauthCallbackAction $handleOauthCallbackAction,
    ) {}

    public function redirect(string $provider): SymfonyRedirectResponse
    {
        abort_unless($this->isValidProvider($provider), 404);

        $driver = Socialite::driver($provider);

        // Para Google OAuth, precisamos solicitar os escopos necessários
        // para obter nome, email e foto de perfil
        if ($provider === 'google') {
            /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
            $driver->scopes([
                'openid',
                'profile',
                'email',
            ])
            ->with(['access_type' => 'offline', 'prompt' => 'consent']);
        }

        return $driver->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless($this->isValidProvider($provider), 404);

        try {
            /** @var SocialiteUser $socialiteUser */
            $socialiteUser = Socialite::driver($provider)->user();
            $authenticatedUser = Auth::user();
            $user = $this->handleOauthCallbackAction->handle($provider, $socialiteUser, $authenticatedUser);
        } catch (InvalidStateException) {
            return Redirect::intended(Auth::check() ? route('profile.show') : route('login'))
                ->with('error', 'A requisição expirou. Por favor, tente novamente.');
        } catch (OAuthAccountLinkingException $oauthAccountLinkingException) {
            return Redirect::intended(Auth::check() ? route('profile.show') : route('login'))->with('error', $oauthAccountLinkingException->getMessage());
        } catch (Throwable $throwable) {
            report($throwable);

            return Redirect::intended(Auth::check() ? route('profile.show') : route('login'))
                ->with('error', 'Ocorreu um erro durante a autenticação. Por favor, tente novamente.');
        }

        if (Auth::guest()) {
            Auth::login($user, true);

            $fallback = (string) config('fortify.home', '/dopa');
            $intended = session('url.intended');
            $path = is_string($intended) ? (parse_url($intended, PHP_URL_PATH) ?: '') : '';
            if ($path !== '' && str_starts_with($path, '/api/')) {
                session()->forget('url.intended');
                return Redirect::to($fallback);
            }

            return Redirect::intended($fallback);
        }

        return Redirect::intended(route('profile.show'))->with('success', "Sua conta {$provider} foi vinculada com sucesso.");
    }

    public function destroy(string $provider): RedirectResponse
    {
        abort_unless($this->isValidProvider($provider), 404);

        $user = Auth::user();

        $user?->oauthConnections()->where('provider', $provider)->delete();
        session()->flash('success', "Sua conta {$provider} foi desvinculada com sucesso.");

        return Redirect::route('profile.show');
    }

    private function isValidProvider(string $provider): bool
    {
        $activeProviders = (new ActiveOauthProviderAction)->handle();

        return collect($activeProviders)->contains('slug', $provider);
    }
}
