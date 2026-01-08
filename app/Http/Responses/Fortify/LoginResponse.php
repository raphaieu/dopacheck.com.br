<?php

declare(strict_types=1);

namespace App\Http\Responses\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

final class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        $fallback = (string) config('fortify.home', '/dopa');
        $intended = session('url.intended');

        if (is_string($intended) && $intended !== '') {
            $path = parse_url($intended, PHP_URL_PATH) ?: '';
            if (str_starts_with($path, '/api/')) {
                session()->forget('url.intended');
                return redirect()->to($fallback);
            }
        }

        return redirect()->intended($fallback);
    }
}

