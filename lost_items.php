<?php
session_start();
header('Content-Type: application/json');
include('../db.php');

$sql = "SELECT * FROM lost_items ORDER BY date_lost DESC";
$result = $con->query($sql);

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);
?>
