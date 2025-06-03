<?php
session_start();
header('Content-Type: application/json');
include('../db.php');

$sql = "SELECT * FROM found_items ORDER BY date_found DESC";
$result = $con->query($sql);

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);
?>
