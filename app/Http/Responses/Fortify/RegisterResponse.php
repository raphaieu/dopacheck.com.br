<?php

declare(strict_types=1);

namespace App\Http\Responses\Fortify;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

final class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
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

