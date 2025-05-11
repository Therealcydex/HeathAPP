<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebPurifyService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

    }


    public function containsBadWords(string $text, array $languages = ['fr', 'ar', 'en']): bool
    {
        $response = $this->httpClient->request(
            'GET',
            'http://api1.webpurify.com/services/rest/?api_key=6df22f342855d1937fa78766acf38e33&method=webpurify.live.check&format=json&text=' . urlencode($text),

        );

        $data = $response->toArray();
        dump($data);
        return $data['rsp']['found'] == '1';

    }



}