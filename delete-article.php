<?php

$filename = __DIR__.'/data/articles.json';
$articles = [];

$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$idArticle = $_GET['id'] ?? '';

if(!$idArticle) {
    header('Location: /');
} else {
    if(file_exists($filename)) {
        // on recupere les articles depuis articles.json
        $articles = json_decode(file_get_contents($filename), true) ?? [];

        // on recupere l'index correspondant à l'article ayant l'id sur lequel on est
        $articleIndex = array_search($idArticle, array_column($articles, 'id'));

        // on supprime l'article
        array_splice($articles, $articleIndex, 1);

        // on ecrase les données dans articles.json pour sauvegarder les nouvelles
        file_put_contents($filename, json_encode($articles));
        
        header('Location: /');
    }
}