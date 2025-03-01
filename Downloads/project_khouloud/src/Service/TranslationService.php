<?php
namespace App\Service;

use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;

class TranslationService
{
    private $apiKey;
    private $endpoint;
    private $location;
    private $client;

    public function __construct( )
    {
        $this->apiKey = "D0cNDZabNOrXDiDlUFHYUAPE0ndC5zVqPLD6wqPl61qASSzOuDgvJQQJ99BBACrIdLPXJ3w3AAAbACOGKg79";
        $this->endpoint = "https://api.cognitive.microsofttranslator.com";
        $this->location ="southafricanorth";
        $this->client = new Client([
            'verify' => __DIR__ . '/../../config/azer.pem', // Path to the CA certificate bundle
        ]);
    }


    /**
     * Translate multiple texts to the target language.
     *
     * @param array $texts An array of texts to translate.
     * @param string $targetLanguage The target language code (e.g., 'fr', 'ar').
     * @return array An array of translated texts.
     */
    public function translate(array $texts, string $targetLanguage): array
    {
        $response = $this->client->post($this->endpoint . '/translate', [
            'headers' => [
                'Ocp-Apim-Subscription-Key' => $this->apiKey,
                'Ocp-Apim-Subscription-Region' => $this->location,
                'Content-Type' => 'application/json',
                'X-ClientTraceId' => Uuid::uuid4()->toString(),
            ],
            'query' => [
                'api-version' => '3.0',
                'to' => $targetLanguage,
            ],
            'json' => array_map(function ($text) {
                return ['text' => $text];
            }, $texts),
        ]);

        $data = json_decode($response->getBody(), true);

        // Extract translated texts
        $translatedTexts = [];
        foreach ($data as $item) {
            $translatedTexts[] = $item['translations'][0]['text'];
        }

        return $translatedTexts;
    }}