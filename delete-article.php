<?php

$pdo = require_once './database/database.php';
$statement = $pdo->prepare('DELETE FROM article WHERE id=:id');
$articles = [];

$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$idArticle = $_GET['id'] ?? '';

if($idArticle) {
    $statement->bindValue(':id', $idArticle);
    $statement->execute();
}

header('Location: /');