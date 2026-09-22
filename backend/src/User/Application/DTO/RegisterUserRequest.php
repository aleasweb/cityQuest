<?php

declare(strict_types=1);

namespace App\User\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterUserRequest
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Invalid email format')]
    #[Assert\Length(max: 255, maxMessage: 'Email must be at most {{ limit }} characters')]
    public string $email;

    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(
        min: 8,
        max: 255,
        minMessage: 'Password must be at least {{ limit }} characters',
        maxMessage: 'Password must be at most {{ limit }} characters'
    )]
    public string $password;

    #[Assert\NotBlank(message: 'Username is required')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Username must be at least {{ limit }} characters',
        maxMessage: 'Username must be at most {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]+$/',
        message: 'Username can only contain letters, numbers and underscores'
    )]
    public string $username;

    public function __construct(string $email, string $password, string $username)
    {
        $this->email = $email;
        $this->password = $password;
        $this->username = $username;
    }
}
