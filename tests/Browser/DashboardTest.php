<?php

use App\Models\System\User;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

test('user can login via browser', function () {
    $user = User::factory()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password123'),
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit('/login')
            ->assertSee('Login')
            ->type('email', 'admin@test.com')
            ->type('password', 'password123')
            ->click('button[type="submit"]')
            ->waitForLocation('/dashboard')
            ->assertPathIs('/dashboard')
            ->assertSee('Dashboard');
    });
})->skip('Browser testing setup required');

test('dashboard displays KPIs', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/dashboard')
            ->assertSee('Total Sales')
            ->assertSee('Total Transactions')
            ->assertSee('Inventory Value')
            ->assertNoJavaScriptErrors();
    });
})->skip('Browser testing setup required');

test('user can create product via browser', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/products/create')
            ->type('product_name', 'Test Product')
            ->type('product_code', 'TEST-001')
            ->select('category_id', 1)
            ->select('unit_id', 1)
            ->type('description', 'Test product description')
            ->click('button[type="submit"]')
            ->waitForLocation('/products')
            ->assertSee('Test Product')
            ->assertSee('Product created successfully');
    });
})->skip('Browser testing setup required');

test('pos transaction flow works', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/pos')
            ->assertSee('POS Terminal')
            ->type('barcode', '1234567890')
            ->pause(500)
            ->assertSee('Product Added')
            ->type('quantity', '2')
            ->click('button:contains("Add to Cart")')
            ->assertSee('Total:')
            ->click('button:contains("Checkout")')
            ->assertSee('Payment Method')
            ->select('payment_method', 'CASH')
            ->type('amount_paid', '100000')
            ->click('button:contains("Complete Sale")')
            ->assertSee('Transaction Completed');
    });
})->skip('Browser testing setup required');
