<?php
session_start();
require_once __DIR__ . '/env.php';

if (!isset($_GET['code'])) {
    die('No authorization code provided.');
}

$code = $_GET['code'];

$headers = [
    'Authorization: Basic ' . base64_encode("{$_ENV['FITBIT_CLIENT_ID']}:{$_ENV['FITBIT_CLIENT_SECRET']}"),
    'Content-Type: application/x-www-form-urlencoded'
];

$data = http_build_query([
    'client_id'     => $_ENV['FITBIT_CLIENT_ID'],
    'grant_type'    => 'authorization_code',
    'redirect_uri'  => $_ENV['FITBIT_REDIRECT_URI'],
    'code'          => $code
]);

$ch = curl_init('https://api.fitbit.com/oauth2/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => $headers
]);

$response = curl_exec($ch);
curl_close($ch);

$decoded = json_decode($response, true);

if (!isset($decoded['access_token'])) {
    die('Failed to obtain access token. Response: ' . $response);
}

file_put_contents(__DIR__ . '/../secure/tokens.json', json_encode($decoded, JSON_PRETTY_PRINT));


$to = 'vinaypalakurthy7@gmail.com';
$subject = 'Fitbit Tokens Refreshed';
$message = 'New tokens were successfully saved at ' . date('Y-m-d H:i:s');
$headers = 'From: vinayvein@gmail.com';

mail($to, $subject, $message, $headers);


header('Location: http://localhost/Fitbit/public/index.php');
exit;
