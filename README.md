# MemcachedManager

<?php
include 'MemcachedManager.php';
$manager = new MemcachedManager;
$items = $manager->getItems();
print_r($items);
?>
