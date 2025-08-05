<?php
$conn = new mysqli("localhost", "root", "", "fitbit_db");

$sql = "SELECT * FROM fitbit_daily_summary ORDER BY date DESC LIMIT 5";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
$conn->close();
?>
