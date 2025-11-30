<?php

declare(strict_types=1);

namespace App\City\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cities')]
class CityController extends AbstractController
{
    #[Route('', name: 'api_cities_list', methods: ['GET'])]
    public function getCities(): JsonResponse
    {
        // Получаем статический список городов из конфигурации
        $cities = $this->getParameter('app.cities');
        
        // Преобразуем в формат [{key: "moscow", name: "Москва"}, ...]
        $cityList = [];
        foreach ($cities as $key => $name) {
            $cityList[] = [
                'key' => $key,
                'name' => $name
            ];
        }
        
        // Сортируем по русскому названию
        usort($cityList, fn($a, $b) => strcmp($a['name'], $b['name']));

        return new JsonResponse([
            'data' => $cityList,
            'meta' => [
                'total' => count($cityList),
                'count' => count($cityList)
            ]
        ]);
    }
}
