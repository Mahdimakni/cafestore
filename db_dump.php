<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'cafestore', 3307);
if ($conn->connect_error) die("Conn failed");
$res = $conn->query("DESCRIBE produits");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
