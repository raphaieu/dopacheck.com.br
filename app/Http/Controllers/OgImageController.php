<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

final class OgImageController extends Controller
{
    private const int OG_WIDTH = 1200;
    private const int OG_HEIGHT = 630;

    public function challenge(Request $request, Challenge $challenge): Response
    {
        $cacheKey = 'og:challenge:jpg:' . $challenge->id . ':' . md5((string) ($challenge->image_url ?? ''));

        $jpeg = Cache::remember($cacheKey, now()->addDay(), function () use ($challenge): string {
            $source = $this->normalizeUrl($challenge->image_url);
            return $this->makeOgJpegFromSource($source);
        });

        return response($jpeg, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function user(Request $request, string $username): Response
    {
        $user = User::where('username', $username)->first();
        if (! $user && is_numeric($username)) {
            $user = User::find((int) $username);
        }

        if (! $user) {
            abort(404, 'Usuário não encontrado');
        }

        $preferences = $user->preferences ?? [];
        if (! ($preferences['privacy']['public_profile'] ?? true)) {
            abort(403, 'Perfil privado');
        }

        $avatarUrl = $this->normalizeUrl($user->profile_photo_url);
        $cacheKey = 'og:user:jpg:' . $user->id . ':' . md5((string) $avatarUrl);

        $jpeg = Cache::remember($cacheKey, now()->addDay(), function () use ($avatarUrl): string {
            return $this->makeOgJpegFromSource($avatarUrl);
        });

        return response($jpeg, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function normalizeUrl(?string $url): string
    {
        $appUrl = rtrim((string) config('app.url', 'https://dopacheck.com.br'), '/');

        if (! $url) {
            return $appUrl . '/images/og.png';
        }

        $trimmed = trim($url);
        if ($trimmed === '') {
            return $appUrl . '/images/og.png';
        }

        if (Str::startsWith($trimmed, ['http://', 'https://'])) {
            return $trimmed;
        }

        return $appUrl . '/' . ltrim($trimmed, '/');
    }

    private function makeOgJpegFromSource(string $sourceUrl): string
    {
        $fallback = rtrim((string) config('app.url', 'https://dopacheck.com.br'), '/') . '/images/og.png';

        $bytes = $this->fetchImageBytes($sourceUrl) ?? $this->fetchImageBytes($fallback);
        if (! $bytes) {
            // Último fallback: imagem branca
            $manager = $this->imageManager();
            $canvas = $manager->create(self::OG_WIDTH, self::OG_HEIGHT)->fill('#ffffff');
            return $canvas->encode(new \Intervention\Image\Encoders\JpegEncoder(quality: 85))->toString();
        }

        $manager = $this->imageManager();
        $img = $manager->read($bytes);

        // “Cover” para manter proporção e preencher 1200x630 (crop central).
        $img = $img->cover(self::OG_WIDTH, self::OG_HEIGHT);

        return $img->encode(new \Intervention\Image\Encoders\JpegEncoder(quality: 85))->toString();
    }

    private function fetchImageBytes(string $url): ?string
    {
        try {
            // Se for um arquivo local público (ex.: /images/.. ou /storage/..), tenta ler do disco
            $parsed = parse_url($url);
            $path = $parsed['path'] ?? null;

            if ($path && Str::startsWith($path, ['/images/', '/storage/'])) {
                $local = public_path(ltrim($path, '/'));
                if (is_file($local)) {
                    $bytes = @file_get_contents($local);
                    if (is_string($bytes) && $bytes !== '') {
                        return $bytes;
                    }
                }
            }

            // Senão, busca via HTTP (importante para URLs absolutas / CDN)
            $response = Http::timeout(4)
                ->retry(1, 200)
                ->withHeaders(['User-Agent' => 'DOPA-OG/1.0'])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $body = $response->body();
            return $body !== '' ? $body : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function imageManager(): ImageManager
    {
        return new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
    }
}

