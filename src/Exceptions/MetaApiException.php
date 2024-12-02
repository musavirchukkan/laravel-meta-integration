<?php

namespace Musavirchukkan\LaravelMetaIntegration\Exceptions;


class MetaApiException extends \Exception 
{
    protected $errorData;

    public function __construct(string $message, array $errorData = [])
    {
        parent::__construct($message);
        $this->errorData = $errorData;
    }

    public function getErrorData(): array
    {
        return $this->errorData;
    }
}