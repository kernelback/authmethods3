<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $openWeatherApiKey)
    {
        $this->client = $client;
        $this->apiKey = $openWeatherApiKey;
    }

    public function getWeatherForCity(string $city = 'Paris'): array
    {
        $url = 'https://api.openweathermap.org/data/2.5/weather?q=' . urlencode($city) . '&appid=' . $this->apiKey . '&units=metric&lang=fr';

        $response = $this->client->request('GET', $url);
        return $response->toArray();
    }
}
