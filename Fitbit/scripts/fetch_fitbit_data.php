<?php
require_once __DIR__ . '/env.php';

function sqlVal($v) {
    if (is_null($v)) return 'NULL';
    if (is_array($v)) return "'" . addslashes(json_encode($v)) . "'";
    return "'" . addslashes($v) . "'";
}


$tokens = json_decode(file_get_contents(__DIR__ . '/../secure/tokens.json'), true);


$headers = [
    'Authorization: Basic ' . base64_encode("{$_ENV['FITBIT_CLIENT_ID']}:{$_ENV['FITBIT_CLIENT_SECRET']}"),
    'Content-Type: application/x-www-form-urlencoded'
];
$post_data = http_build_query([
    'grant_type'    => 'refresh_token',
    'refresh_token' => $tokens['refresh_token']
]);

$ch = curl_init('https://api.fitbit.com/oauth2/token');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $post_data,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => true
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$access_token = $data['access_token'];
file_put_contents(__DIR__ . '/../secure/tokens.json', json_encode($data, JSON_PRETTY_PRINT));


function fitbitGet($url, $access_token) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Authorization: Bearer $access_token"]
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$date = date('Y-m-d', strtotime('-1 days'));



$summary     = fitbitGet("https://api.fitbit.com/1/user/-/activities/date/{$date}.json", $access_token);
$cardio      = fitbitGet("https://api.fitbit.com/1/user/-/cardioscore/date/{$date}.json", $access_token);
$heart       = fitbitGet("https://api.fitbit.com/1/user/-/activities/heart/date/{$date}/1d.json", $access_token);
$sleep       = fitbitGet("https://api.fitbit.com/1.2/user/-/sleep/date/{$date}.json", $access_token);
$spo2        = fitbitGet("https://api.fitbit.com/1/user/-/spo2/date/{$date}.json", $access_token);
$resp        = fitbitGet("https://api.fitbit.com/1/user/-/br/date/{$date}.json", $access_token);
$temp_core   = fitbitGet("https://api.fitbit.com/1/user/-/temp/core/date/{$date}.json", $access_token);
$temp_skin   = fitbitGet("https://api.fitbit.com/1/user/-/temp/skin/date/{$date}.json", $access_token);
$nutrition   = fitbitGet("https://api.fitbit.com/1/user/-/foods/log/date/{$date}.json", $access_token);
$weight_data = fitbitGet("https://api.fitbit.com/1/user/-/body/log/weight/date/{$date}.json", $access_token);
$irregular   = fitbitGet("https://api.fitbit.com/1/user/-/irregular-heart-rhythm/notifications.json", $access_token);


$profile     = fitbitGet("https://api.fitbit.com/1/user/-/profile.json", $access_token);
$settings    = fitbitGet("https://api.fitbit.com/1/user/-/devices.json", $access_token);
$ecg         = fitbitGet("https://api.fitbit.com/1/user/-/ecg/list.json", $access_token);
$location    = fitbitGet("https://api.fitbit.com/1/user/-/activities.json", $access_token);
$social      = fitbitGet("https://api.fitbit.com/1.1/user/-/friends.json", $access_token);



$steps             = $summary['summary']['steps'] ?? null;
$resting_hr        = $heart['activities-heart'][0]['value']['restingHeartRate'] ?? null;
$cardio_score      = $cardio['cardioScore'][0]['value']['vo2Max'] ?? null;
$sleep_minutes     = $sleep['summary']['totalMinutesAsleep'] ?? null;
$sleep_efficiency  = $sleep['summary']['efficiency'] ?? null;
$spo2_value        = $spo2['spo2'][0]['avg'] ?? null;
$resp_rate         = $resp['br'][0]['value'] ?? null;
$core_temp         = $temp_core['coreTemperature'][0]['value'] ?? null;
$skin_temp         = $temp_skin['skinTemperature'][0]['value'] ?? null;
$weight            = $weight_data['weight'][0]['weight'] ?? null;
$bmi               = $weight_data['weight'][0]['bmi'] ?? null;
$calories          = $nutrition['summary']['calories'] ?? null;
$water             = $nutrition['summary']['water'] ?? null;
$irregular_detected = isset($irregular['notifications']) && count($irregular['notifications']) > 0 ? 1 : 0;


$conn = new mysqli("localhost", "root", "", "fitbit_db");


$conn->query("
CREATE TABLE IF NOT EXISTS fitbit_daily_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    steps INT,
    resting_heart_rate INT,
    cardio_score FLOAT,
    sleep_minutes INT,
    sleep_efficiency TINYINT,
    oxygen_saturation FLOAT,
    respiratory_rate FLOAT,
    core_temperature FLOAT,
    skin_temperature FLOAT,
    weight FLOAT,
    bmi FLOAT,
    calories_eaten INT,
    water_ml INT,
    irregular_rhythm_detected BOOLEAN,
    sync_time DATETIME DEFAULT CURRENT_TIMESTAMP
)
");


$sql = "INSERT INTO fitbit_daily_summary (
    date, steps, resting_heart_rate, cardio_score,
    sleep_minutes, sleep_efficiency,
    oxygen_saturation, respiratory_rate,
    core_temperature, skin_temperature,
    weight, bmi, calories_eaten, water_ml,
    irregular_rhythm_detected
) VALUES (
    '$date', " . sqlVal($steps) . ", " . sqlVal($resting_hr) . ", " . sqlVal($cardio_score) . ",
    " . sqlVal($sleep_minutes) . ", " . sqlVal($sleep_efficiency) . ",
    " . sqlVal($spo2_value) . ", " . sqlVal($resp_rate) . ",
    " . sqlVal($core_temp) . ", " . sqlVal($skin_temp) . ",
    " . sqlVal($weight) . ", " . sqlVal($bmi) . ", " . sqlVal($calories) . ", " . sqlVal($water) . ",
    " . sqlVal($irregular_detected) . "
) ON DUPLICATE KEY UPDATE
    steps = " . sqlVal($steps) . ",
    resting_heart_rate = " . sqlVal($resting_hr) . ",
    cardio_score = " . sqlVal($cardio_score) . ",
    sleep_minutes = " . sqlVal($sleep_minutes) . ",
    sleep_efficiency = " . sqlVal($sleep_efficiency) . ",
    oxygen_saturation = " . sqlVal($spo2_value) . ",
    respiratory_rate = " . sqlVal($resp_rate) . ",
    core_temperature = " . sqlVal($core_temp) . ",
    skin_temperature = " . sqlVal($skin_temp) . ",
    weight = " . sqlVal($weight) . ",
    bmi = " . sqlVal($bmi) . ",
    calories_eaten = " . sqlVal($calories) . ",
    water_ml = " . sqlVal($water) . ",
    irregular_rhythm_detected = " . sqlVal($irregular_detected);

$conn->query($sql);
$conn->close();


function sendSyncEmail($steps, $resting_hr, $sleep_minutes) {
    $to      = "vinayvein@gmail.com";
    $subject = "Fitbit Sync Summary for " . date('Y-m-d');
    $message = "✅ Fitbit sync completed successfully!\n\n" .
               "📊 Steps: $steps\n" .
               "❤️ Resting Heart Rate: $resting_hr\n" .
               "😴 Sleep Duration: $sleep_minutes minutes\n\n" .
               "Time: " . date("Y-m-d H:i:s");
    $headers = "From: vinaypalakurthy7@gmail.com\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";

    mail($to, $subject, $message, $headers);
}

sendSyncEmail($steps, $resting_hr, $sleep_minutes);