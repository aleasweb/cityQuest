<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

class InvalidCredentialsException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Invalid email or password');
    }
}
