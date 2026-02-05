<?php

declare(strict_types=1);

$routes = [
    'tg-admin' => 'trongate_administrators/login',
    'tg-admin/submit_login' => 'trongate_administrators/submit_login',
];
define('CUSTOM_ROUTES', $routes);
