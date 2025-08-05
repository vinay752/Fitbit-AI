<?php
require_once __DIR__ . '/env.php';

require_once __DIR__ . '/vendor/autoload.php';

$yourApiKey = $_ENV['GEMINI_API_KEY'];

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

$client = new Client($yourApiKey);
$response = $client->listModels();

print_r($response->models);
$chat = $client->generativeModel(ModelName::gemini-2.5-flash)->startChat();

$response = $chat->sendMessage(new TextPart('Hello World in PHP'));
print $response->text();

$response = $chat->sendMessage(new TextPart('in Go'));
print $response->text();

?>
