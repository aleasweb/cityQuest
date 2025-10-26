<?php

declare(strict_types=1);

namespace App\User\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateProfileRequest
{
    #[Assert\Email(message: 'Invalid email format')]
    #[Assert\Length(max: 255, maxMessage: 'Email must be at most {{ limit }} characters')]
    public ?string $email = null;

    public function __construct(?string $email = null)
    {
        $this->email = $email;
    }
}
