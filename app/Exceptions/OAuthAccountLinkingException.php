<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class OAuthAccountLinkingException extends Exception
{
    public const string EXISTING_CONNECTION_ERROR_MESSAGE = 'Por favor, entre usando seu método de autenticação existente.';

    public static function emailMismatch(string $provider): self
    {
        return new self("O e-mail retornado pelo {$provider} não corresponde ao e-mail da sua conta.");
    }

    public static function existingConnection(): self
    {
        return new self(self::EXISTING_CONNECTION_ERROR_MESSAGE);
    }
}
