<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Unusualify\Modularous\Services\MessageStage;

class StagingLockedException extends HttpException
{
    public function __construct(string $message, public readonly string $envKey)
    {
        parent::__construct(423, $message);
    }

    public static function table(string $table, string $operation): self
    {
        return new self(
            "Staging: {$operation} on table \"{$table}\" is locked. Unlock it with MODULAROUS_STAGING_WRITABLE_TABLES.",
            'MODULAROUS_STAGING_WRITABLE_TABLES'
        );
    }

    public static function route(string $method, ?string $route): self
    {
        $target = $route ? "route \"{$route}\"" : 'this unnamed route';

        return new self(
            "Staging: {$method} requests to {$target} are locked. Unlock it with MODULAROUS_STAGING_WRITABLE_ROUTES.",
            'MODULAROUS_STAGING_WRITABLE_ROUTES'
        );
    }

    public static function host(string $host): self
    {
        return new self(
            "Staging: HTTP requests to \"{$host}\" are locked. Unlock it with MODULAROUS_STAGING_HTTP_HOSTS.",
            'MODULAROUS_STAGING_HTTP_HOSTS'
        );
    }

    public static function command(string $command): self
    {
        return new self(
            "Staging: the \"{$command}\" command is locked. Unlock it with MODULAROUS_STAGING_COMMANDS.",
            'MODULAROUS_STAGING_COMMANDS'
        );
    }

    public function render(Request $request): ?JsonResponse
    {
        if (! $request->expectsJson()) {
            return null;
        }

        return new JsonResponse([
            'message' => $this->getMessage(),
            'variant' => MessageStage::ERROR,
            'staging' => true,
        ], $this->getStatusCode());
    }
}
