<?php
/**
 * Laravel root redirect for Laragon (http://localhost/Zaga)
 * Forwards all requests to the public/ directory.
 */
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
