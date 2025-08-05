<?php
require_once(__DIR__ . '/../scripts/env.php');

$client_id = $_ENV['FITBIT_CLIENT_ID'];
$redirect_uri = $_ENV['FITBIT_REDIRECT_URI'];

$scope = urlencode($_ENV['FITBIT_SCOPES']);

$auth_url = "https://www.fitbit.com/oauth2/authorize?response_type=code&client_id=$client_id&redirect_uri=$redirect_uri&scope=$scope&prompt=consent&expires_in=604800";


header("Location: $auth_url");
exit;
exit;
?>

