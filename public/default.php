<?php
// Overwrite Hostinger default placeholder page and load React SPA index.html
if (file_exists(__DIR__ . '/index.html')) {
    include __DIR__ . '/index.html';
} else {
    header("Location: /");
}
exit();
?>
