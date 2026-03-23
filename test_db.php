<?php
require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/bootstrap/app.php';
use Illuminate\Support\Facades\DB;

try {
    DB::connection()->getPdo();
    echo "DB Connected successfully!\n";
} catch (\Exception $e) {
    echo "DB Connection Failed: " . $e->getMessage() . "\n";
}
