#!/bin/bash

# Settings System Test Script

echo "🔧 Testing Settings System..."
echo ""

# Test 1: Check if settings exist
echo "✅ Test 1: Checking if settings exist..."
docker exec -it foerdertafel php artisan tinker --execute="
\$settings = App\Models\Setting::current();
if (\$settings) {
    echo '✓ Settings found with ID: ' . \$settings->id . PHP_EOL;
    echo '  - Payment Method: ' . \$settings->default_payment_method . PHP_EOL;
    echo '  - Rental Duration: ' . \$settings->default_rental_duration . ' months' . PHP_EOL;
    echo '  - Max Fields: ' . \$settings->max_fields_per_customer . PHP_EOL;
    echo '  - Email Notifications: ' . (\$settings->email_notifications_enabled ? 'Enabled' : 'Disabled') . PHP_EOL;
} else {
    echo '✗ No settings found!' . PHP_EOL;
}
"

echo ""

# Test 2: Check available payment methods
echo "✅ Test 2: Checking payment methods..."
docker exec -it foerdertafel php artisan tinker --execute="
\$methods = App\Models\Setting::getPaymentMethods();
echo 'Available payment methods:' . PHP_EOL;
foreach (\$methods as \$key => \$label) {
    echo '  - ' . \$key . ': ' . \$label . PHP_EOL;
}
"

echo ""

# Test 3: Test updating settings
echo "✅ Test 3: Testing settings update..."
docker exec -it foerdertafel php artisan tinker --execute="
\$settings = App\Models\Setting::current();
\$settings->update(['max_fields_per_customer' => 15]);
\$updated = App\Models\Setting::current();
echo 'Updated max_fields_per_customer to: ' . \$updated->max_fields_per_customer . PHP_EOL;
// Reset to default
\$settings->update(['max_fields_per_customer' => 10]);
echo 'Reset max_fields_per_customer to: 10' . PHP_EOL;
"

echo ""
echo "✅ All tests completed!"
