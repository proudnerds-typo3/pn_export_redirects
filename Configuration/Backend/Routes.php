<?php

declare(strict_types=1);

use Proudnerds\PnExportRedirects\Controller\ExportController;

return [
    ExportController::ROUTE => [
        'path' => '/redirects/export',
        'target' => ExportController::class . '::exportAction',
    ],
];
