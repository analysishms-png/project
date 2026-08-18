<?php

namespace Tests\Unit;

use Tests\TestCase;

class HelpersTest extends TestCase
{

    /**
     * Test formatCurrency function
     */
    public function test_format_currency_with_default_values()
    {
        $result = formatCurrency(1234567.89);
        $this->assertEquals('₹ 1,234,567.89', $result);
    }

    public function test_format_currency_with_custom_currency()
    {
        $result = formatCurrency(1000, '$');
        $this->assertEquals('$ 1,000.00', $result);
    }

    public function test_format_currency_with_zero_decimals()
    {
        $result = formatCurrency(500, '₹', 0);
        $this->assertEquals('₹ 500', $result);
    }

    public function test_format_currency_with_zero_amount()
    {
        $result = formatCurrency(0);
        $this->assertEquals('₹ 0.00', $result);
    }

    public function test_format_currency_with_negative_amount()
    {
        $result = formatCurrency(-1234.56);
        $this->assertEquals('₹ -1,234.56', $result);
    }

    /**
     * Test calculateTax function
     */
    public function test_calculate_tax()
    {
        $result = calculateTax(1000, 18);
        $this->assertEquals(180, $result);
    }

    public function test_calculate_tax_with_zero_percent()
    {
        $result = calculateTax(1000, 0);
        $this->assertEquals(0, $result);
    }

    public function test_calculate_tax_with_zero_amount()
    {
        $result = calculateTax(0, 18);
        $this->assertEquals(0, $result);
    }

    /**
     * Test getDayNameFromDate function
     */
    public function test_get_day_name_from_date()
    {
        $result = getDayNameFromDate('2026-08-07');
        $this->assertEquals('Friday', $result);
    }

    public function test_get_day_name_from_invalid_date()
    {
        $result = getDayNameFromDate('invalid-date');
        $this->assertEquals('', $result);
    }

    public function test_get_day_name_from_null_date()
    {
        $result = getDayNameFromDate(null);
        $this->assertEquals('', $result);
    }

    /**
     * Test amountToWords function
     */
    public function test_amount_to_words_with_whole_number()
    {
        $result = amountToWords(100);
        $this->assertEquals('One hundred', $result);
    }

    public function test_amount_to_words_with_zero()
    {
        $result = amountToWords(0);
        $this->assertEquals('Zero', $result);
    }

    /**
     * Test normalizeMobile function
     */
    public function test_normalize_mobile_with_indian_number()
    {
        $result = normalizeMobile('9876543210');
        $this->assertEquals('9876543210', $result);
    }

    public function test_normalize_mobile_with_country_code()
    {
        $result = normalizeMobile('919876543210');
        $this->assertEquals('9876543210', $result);
    }

    public function test_normalize_mobile_with_leading_zero()
    {
        $result = normalizeMobile('09876543210');
        $this->assertEquals('9876543210', $result);
    }

    /**
     * Test limitText function
     */
    public function test_limit_text_within_limit()
    {
        $result = limitText('Hello World', 100);
        $this->assertEquals('Hello World', $result);
    }

    public function test_limit_text_exceeds_limit()
    {
        $result = limitText('This is a long text that exceeds the limit', 10);
        $this->assertEquals('This is a ', $result);
    }

    /**
     * Test getMonthYearCode function
     */
    public function test_get_month_year_code()
    {
        $result = getMonthYearCode('2026-08-07');
        $this->assertEquals('082026', $result);
    }

    /**
     * Test taxOperatorMatches — TaxStru slab operator semantics
     * (mirrors CronController posting loops + legacy Proc_96_6_1335500)
     */
    public function test_tax_between_matches_within_bounds()
    {
        $this->assertTrue(taxOperatorMatches('Between', 0, 1000, 500));
        $this->assertTrue(taxOperatorMatches('Between', 0, 1000, 0));
        $this->assertTrue(taxOperatorMatches('Between', 0, 1000, 1000));
    }

    public function test_tax_between_rejects_outside_bounds()
    {
        $this->assertFalse(taxOperatorMatches('Between', 0, 1000, -1));
        $this->assertFalse(taxOperatorMatches('Between', 0, 1000, 1001));
        $this->assertFalse(taxOperatorMatches('Between', 0, null, 500));
    }

    public function test_tax_less_equal_is_lower_bound_check()
    {
        // Legacy: Limit <= amount  →  amount >= Limit
        $this->assertTrue(taxOperatorMatches('<=', 1000, null, 1000));
        $this->assertTrue(taxOperatorMatches('<=', 1000, null, 1500));
        $this->assertFalse(taxOperatorMatches('<=', 1000, null, 999));
    }

    public function test_tax_greater_equal_is_upper_bound_check()
    {
        // Legacy: Limit >= amount  →  amount <= Limit
        $this->assertTrue(taxOperatorMatches('>=', 1000, null, 1000));
        $this->assertTrue(taxOperatorMatches('>=', 1000, null, 500));
        $this->assertFalse(taxOperatorMatches('>=', 1000, null, 1500));
    }

    public function test_tax_equality_matches_only_exact()
    {
        $this->assertTrue(taxOperatorMatches('=', 1000, null, 1000));
        $this->assertFalse(taxOperatorMatches('=', 1000, null, 1001));
    }

    public function test_tax_greater_than_matches_above()
    {
        $this->assertTrue(taxOperatorMatches('>', 1000, null, 1001));
        $this->assertFalse(taxOperatorMatches('>', 1000, null, 1000));
    }

    public function test_tax_less_than_matches_below()
    {
        $this->assertTrue(taxOperatorMatches('<', 1000, null, 999));
        $this->assertFalse(taxOperatorMatches('<', 1000, null, 1000));
    }

    public function test_tax_unknown_operator_never_matches()
    {
        $this->assertFalse(taxOperatorMatches('Unknown', 0, null, 500));
        $this->assertFalse(taxOperatorMatches('', 0, null, 500));
    }
}
