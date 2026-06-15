<?php

declare(strict_types=1);

/*
 * Auto-load API routes dari setiap module: modules/{Module}/Routes/api.php
 * Sama persis dengan pola routes/web.php — tinggal drop file api.php per modul,
 * akan ke-load otomatis.
 */
foreach (glob(base_path('modules/*/Routes/api.php')) ?: [] as $apiRoute) {
    require $apiRoute;
}
