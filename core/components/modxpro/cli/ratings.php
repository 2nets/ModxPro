<?php
/** @var $modx modX */
/** @var $pdo PDO */
require '_initialize.php';

$c = $modx->newQuery('comAuthor', ['User.active' => true]);
$c->innerJoin('modUser', 'User');
$authors = $modx->getIterator('comAuthor', $c);
/** @var comAuthor $author */
foreach ($authors as $author) {
    $author->rating(true);
}