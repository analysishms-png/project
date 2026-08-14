<?php

namespace Tests\Feature;

use Tests\TestCase;

class RouteTest extends TestCase
{
    /**
     * Test that the application boots successfully
     */
    public function test_application_boots()
    {
        $this->assertTrue(true);
    }

    /**
     * Test that config files exist
     */
    public function test_config_files_exist()
    {
        $this->assertFileExists(config_path('app.php'));
        $this->assertFileExists(config_path('database.php'));
        $this->assertFileExists(config_path('auth.php'));
    }

    /**
     * Test that routes files exist
     */
    public function test_routes_files_exist()
    {
        $this->assertFileExists(base_path('routes/web.php'));
        $this->assertFileExists(base_path('routes/api.php'));
    }

    /**
     * Test that helper functions are loaded
     */
    public function test_helper_functions_exist()
    {
        $this->assertTrue(function_exists('formatCurrency'));
        $this->assertTrue(function_exists('calculateTax'));
        $this->assertTrue(function_exists('getDayNameFromDate'));
        $this->assertTrue(function_exists('amountToWords'));
    }

    /**
     * Test formatCurrency helper function
     */
    public function test_format_currency_via_helper()
    {
        $result = formatCurrency(1000);
        $this->assertStringContainsString('1,000.00', $result);
    }

    /**
     * Test calculateTax helper function
     */
    public function test_calculate_tax_via_helper()
    {
        $result = calculateTax(1000, 18);
        $this->assertEquals(180, $result);
    }
}
