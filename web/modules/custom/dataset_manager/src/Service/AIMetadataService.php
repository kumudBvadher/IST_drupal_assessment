<?php

namespace Drupal\dataset_manager\Service;

use GuzzleHttp\ClientInterface;
use Drupal\Core\Site\Settings;

class AIMetadataService {

  protected $httpClient;
  protected $apiKey;

  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
    $this->apiKey = Settings::get('openai_api_key');

  }

  public function generateMetadata(string $columns, string $langcode = 'en') {
    $apiKey =  $this->apiKey;
    $language = ($langcode === 'ar') ? 'Arabic' : 'English';
  $prompt = "Respond in $language only.\n"
    . "Given the following dataset columns: $columns.\n"
    . "Generate a suitable:\n"
    . "- Title\n"
    . "- Short description\n"
    . "- 3 tags as array\n"
    . "- Suggested categories as array\n\n"
    . "Return JSON with keys: title, description, tags, categories.";

    try {
      $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
        'headers' => [
          'Authorization' => "Bearer {$apiKey}",
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'model' => 'gpt-4',
          'messages' => [
            [
              'role' => 'user',
              'content' => $prompt,
            ],
          ],
          'temperature' => 0.7,
          'max_tokens' => 300,
        ],
      ]);

      $result = json_decode($response->getBody()->getContents(), TRUE);

      $content = $result['choices'][0]['message']['content'];

      // Attempt to decode AI output as JSON
      $metadata = json_decode($content, TRUE);

      // Validate format
      if (is_array($metadata) && isset($metadata['title'], $metadata['description'])) {
        return $metadata;
      }
      else {
        // fallback
        return [
          'title' => 'AI Generated Title',
          'description' => 'No structured output returned.',
          'tags' => [],
          'categories' => [],
        ];
      }

    } catch (\Exception $e) {
      \Drupal::logger('ai_dataset')->error('AI API error: @msg', ['@msg' => $e->getMessage()]);
      return [
        'title' => 'AI Metadata Error',
        'description' => 'Unable to retrieve metadata.',
        'tags' => [],
        'categories' => [],
      ];
    }
  }
}

