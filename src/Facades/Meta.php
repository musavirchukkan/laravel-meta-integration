<?php

namespace MusavirChukkan\MetaIntegration\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getConnectionUrl(array $state = [], array $additionalPermissions = [])
 * @method static array handleCallback()
 * @method static array getPages(string $token)
 * 
 * @see \MusavirChukkan\MetaIntegration\Meta
 */
class Meta extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'meta';
    }
}