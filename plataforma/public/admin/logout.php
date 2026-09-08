<?php

declare(strict_types=1);

use App\Support\AdminSession;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

AdminSession::logout();

header(
    'Location: /public/admin/login.php'
);

exit;