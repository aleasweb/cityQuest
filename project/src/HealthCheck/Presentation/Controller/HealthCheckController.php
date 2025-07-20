<?php

namespace App\HealthCheck\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class HealthCheckController extends AbstractController
{
    #[Route('/health-check', name: 'health_check', methods: Request::METHOD_GET)]
    public function __invoke(): JsonResponse
    {
        $message = 'work it';
        
        return $this->json([
            'message' => $message,
        ]);
    }
} 