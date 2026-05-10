<?php
$conn = new PDO('mysql:host=127.0.0.1;port=3307;dbname=cafestore', 'root', '');
$res = $conn->query("DESCRIBE produits");
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
