<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\User\Application\DTO\RegisterUserRequest;
use App\User\Application\Service\AuthenticationService;
use App\User\Domain\Exception\UserAlreadyExistsException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private AuthenticationService $authenticationService,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST', 'OPTIONS'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            }

            $registerRequest = new RegisterUserRequest(
                email: $data['email'] ?? '',
                password: $data['password'] ?? '',
                username: $data['username'] ?? ''
            );

            $violations = $this->validator->validate($registerRequest);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }

                return $this->json([
                    'error' => 'Validation failed',
                    'violations' => $errors,
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->authenticationService->register($registerRequest);

            return $this->json([
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->getId()->toRfc4122(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                ],
            ], Response::HTTP_CREATED);
        } catch (UserAlreadyExistsException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'An error occurred during registration'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // This endpoint is handled by LexikJWTAuthenticationBundle
        // It should never be reached directly
        throw new \LogicException('This should never be reached');
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // Phase 2: Clear HttpOnly JWT cookie by setting it with expired date
        $response = $this->json(['message' => 'Logged out successfully']);
        
        // Clear jwt_token cookie (HttpOnly, same path as set by lexik_jwt)
        $response->headers->setCookie(
            Cookie::create(
                'jwt_token',
                '', // empty value
                1, // expires at Unix epoch (Jan 1, 1970) - effectively deletes cookie
                '/', // same path as in lexik_jwt config
                null, // domain - null means current domain
                false, // secure - false for development, true for production with HTTPS
                true, // httpOnly - must match lexik_jwt config
                false, // raw
                Cookie::SAMESITE_STRICT // same as lexik_jwt config
            )
        );
        
        return $response;
    }

    /**
     * Get current authenticated user
     * Phase 2: Returns user data without requiring JWT decoding on client
     * 
     * @return JsonResponse User data or 401 Unauthorized
     */
    #[Route('/me', name: 'me', methods: ['GET'])]
    public function getCurrentUser(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(
                ['error' => 'Unauthorized', 'message' => 'Valid JWT token required'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->json([
            'data' => [
                'user' => [
                    'id' => $user->getId()->toRfc4122(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                ],
            ],
        ]);
    }
}
