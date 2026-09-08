<?php

declare(strict_types=1);

use App\Support\AdminGuard;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

AdminGuard::proteger();

header(
    'Location: /public/admin/dashboard.php'
);

exit;