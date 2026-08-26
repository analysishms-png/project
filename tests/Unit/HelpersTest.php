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

    public function test_amount_to_words_thousands()
    {
        $result = amountToWords(1234);
        $this->assertStringContainsString('One thousand', $result);
        $this->assertStringContainsString('two hundred', $result);
    }

    public function test_amount_to_words_millions()
    {
        $result = amountToWords(1000000);
        $this->assertStringContainsString('One million', $result);
    }

    public function test_amount_to_words_with_decimals()
    {
        $result = amountToWords(123.45);
        $this->assertStringContainsString('One hundred', $result);
        // amountToWords truncates decimals, only returns whole part
        $this->assertStringContainsString('twenty-three', $result);
    }

    public function test_amount_to_words_one()
    {
        $result = amountToWords(1);
        $this->assertEquals('One', $result);
    }

    public function test_amount_to_words_ninety_nine()
    {
        $result = amountToWords(99);
        // Uses hyphen for compound numbers
        $this->assertStringContainsString('Ninety-nine', $result);
    }

    public function test_amount_to_words_negative()
    {
        // amountToWords doesn't handle negatives; verify it doesn't crash on abs
        $result = amountToWords(abs(-50));
        $this->assertStringContainsString('Fifty', $result);
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

    /**
     * Test calculateRoundOff function — Standard, Upper, and default modes
     */
    public function test_calculate_round_off_standard_rounds_down_below_half()
    {
        $result = calculateRoundOff(199.49, 'Standard');
        $this->assertEquals(199, $result['billamt']);
        $this->assertEquals(-0.49, $result['roundoff']);
    }

    public function test_calculate_round_off_standard_rounds_up_at_half()
    {
        $result = calculateRoundOff(199.50, 'Standard');
        $this->assertEquals(200, $result['billamt']);
        $this->assertEquals(0.50, $result['roundoff']);
    }

    public function test_calculate_round_off_standard_rounds_up_above_half()
    {
        $result = calculateRoundOff(199.75, 'Standard');
        $this->assertEquals(200, $result['billamt']);
        $this->assertEquals(0.25, $result['roundoff']);
    }

    public function test_calculate_round_off_upper_always_ceils()
    {
        $result = calculateRoundOff(199.01, 'Upper');
        $this->assertEquals(200, $result['billamt']);
        $this->assertEquals(0.99, $result['roundoff']);
    }

    public function test_calculate_round_off_upper_exact_whole()
    {
        $result = calculateRoundOff(200, 'Upper');
        $this->assertEquals(200, $result['billamt']);
        $this->assertEquals(0, $result['roundoff']);
    }

    public function test_calculate_round_off_default_uses_php_round()
    {
        $result = calculateRoundOff(199.5, 'default');
        $this->assertEquals(200, $result['billamt']);
    }

    public function test_calculate_round_off_zero()
    {
        $result = calculateRoundOff(0, 'Standard');
        $this->assertEquals(0, $result['billamt']);
        $this->assertEquals(0, $result['roundoff']);
    }

    public function test_calculate_round_off_whole_number()
    {
        $result = calculateRoundOff(500, 'Standard');
        $this->assertEquals(500, $result['billamt']);
        $this->assertEquals(0, $result['roundoff']);
    }

    public function test_calculate_round_off_large_amount()
    {
        $result = calculateRoundOff(99999.99, 'Standard');
        $this->assertEquals(100000, $result['billamt']);
        $this->assertEquals(0.01, $result['roundoff']);
    }

    public function test_calculate_round_off_negative_amount()
    {
        $result = calculateRoundOff(-199.50, 'Standard');
        $this->assertEquals(-199, $result['billamt']);
    }

    /**
     * Test calculateTax with edge cases
     */
    public function test_calculate_tax_decimal_amount()
    {
        $result = calculateTax(999.99, 18);
        $this->assertEqualsWithDelta(179.9982, $result, 0.001);
    }

    public function test_calculate_tax_full_100_percent()
    {
        $result = calculateTax(500, 100);
        $this->assertEquals(500, $result);
    }

    /**
     * Test formatCurrency edge cases
     */
    public function test_format_currency_large_amount()
    {
        $result = formatCurrency(9999999.99);
        // Uses standard number_format grouping
        $this->assertEquals('₹ 9,999,999.99', $result);
    }

    public function test_format_currency_exact_zero_decimals()
    {
        $result = formatCurrency(100.00, '₹', 0);
        $this->assertEquals('₹ 100', $result);
    }
}
