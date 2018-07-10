<?php
/** @var $modx modX */
/** @var $pdo PDO */
require '_initialize.php';

$items = $modx->getIterator('appMailQueue');
/** @var appMailQueue $item */
foreach ($items as $item) {
    if ($item->send()) {
        $item->remove();
    }
}