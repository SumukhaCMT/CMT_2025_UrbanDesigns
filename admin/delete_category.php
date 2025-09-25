<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$id = $_GET['id'] ?? '';
if (ctype_digit($id)) {
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
}

header('Location: categories.php?success=cat_deleted');
exit;
