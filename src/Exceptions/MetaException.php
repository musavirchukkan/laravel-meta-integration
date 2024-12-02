<?php

namespace MusavirChukkan\MetaIntegration\Exceptions;

use Exception;

class MetaException extends Exception
{
    protected $metadata;

    public function __construct($message = "", $code = 0, $metadata = [])
    {
        parent::__construct($message, $code);
        $this->metadata = $metadata;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }
}