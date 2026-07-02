<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Inventory\InventoryLedger;
use App\Events\StockLevelChanged;
use App\Models\System\Notification;

echo "Initial notification count: " . Notification::count() . "\n";

$ledger = InventoryLedger::first();
if ($ledger) {
    event(new StockLevelChanged($ledger, 20.0, 4.0));
    echo "Fired event! New count: " . Notification::count() . "\n";
    $latest = Notification::latest()->first();
    if ($latest) {
        echo "Latest title: " . $latest->title . "\n";
        echo "Latest message: " . $latest->message . "\n";
    }
} else {
    echo "No ledger records found.\n";
}
unlink(__FILE__);
