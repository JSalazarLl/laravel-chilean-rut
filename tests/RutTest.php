<?php

declare(strict_types=1);

namespace JSalazarLl\Rut\Tests;

use InvalidArgumentException;
use JSalazarLl\Rut\Data\RutData;
use JSalazarLl\Rut\Rules\ValidRut;
use JSalazarLl\Rut\Rut;
use PHPUnit\Framework\TestCase;

final class RutTest extends TestCase
{
    public function test_it_validates_valid_ruts(): void
    {
        $this->assertTrue(Rut::isValid('12.345.678-5'));
        $this->assertTrue(Rut::isValid('12345678-5'));
        $this->assertTrue(Rut::isValid('123456785'));
        $this->assertTrue(Rut::isValid('1-9'));
        $this->assertTrue(Rut::isValid('6-k'));
    }

    public function test_it_rejects_invalid_ruts(): void
    {
        $this->assertFalse(Rut::isValid('12.345.678-6'));
        $this->assertFalse(Rut::isValid('abc'));
        $this->assertFalse(Rut::isValid('abc12.345.678-5'));
        $this->assertFalse(Rut::isValid('12.345.678-5abc'));
        $this->assertFalse(Rut::isValid('123.456.789-2'));
        $this->assertFalse(Rut::isValid(null));
    }

    public function test_it_formats_ruts(): void
    {
        $this->assertSame('12.345.678-5', Rut::format('123456785'));
        $this->assertSame('12.345.678-5', Rut::mask('12345678-5'));
    }

    public function test_it_normalizes_ruts_for_database(): void
    {
        $this->assertSame('12345678-5', Rut::formatForDatabase('12.345.678-5'));
        $this->assertSame('12345678-5', Rut::normalize('12.345.678-5'));
    }

    public function test_it_normalizes_only_valid_ruts(): void
    {
        $this->assertSame('12345678-5', Rut::normalizeIfValid('12.345.678-5'));
        $this->assertNull(Rut::normalizeIfValid('12.345.678-6'));
        $this->assertNull(Rut::normalizeIfValid(null));
    }

    public function test_it_cleans_ruts(): void
    {
        $this->assertSame('123456785', Rut::clean('12.345.678-5'));
        $this->assertSame('123456785', Rut::unmask('12.345.678-5'));
    }

    public function test_it_splits_ruts(): void
    {
        $data = Rut::split('12.345.678-5');

        $this->assertInstanceOf(RutData::class, $data);
        $this->assertSame('12345678', $data->digits);
        $this->assertSame('5', $data->dv);
    }

    public function test_it_calculates_dv(): void
    {
        $this->assertSame('5', Rut::calculateDv('12345678'));
        $this->assertSame('9', Rut::calculateDv('1'));
        $this->assertSame('K', Rut::calculateDv('6'));
    }

    public function test_it_generates_a_valid_fake_rut(): void
    {
        $rut = Rut::fake();

        $this->assertIsString($rut);
        $this->assertTrue(Rut::isValid($rut));
        $this->assertTrue(Rut::isFormatted($rut));
    }

    public function test_it_generates_multiple_valid_fake_ruts(): void
    {
        $ruts = Rut::fake(10);

        $this->assertCount(10, $ruts);

        foreach ($ruts as $rut) {
            $this->assertTrue(Rut::isValid($rut));
            $this->assertTrue(Rut::isFormatted($rut));
        }
    }

    public function test_it_generates_fake_ruts_in_different_formats(): void
    {
        $formatted = Rut::fake(format: 'formatted');
        $database = Rut::fake(format: 'database');
        $clean = Rut::fake(format: 'clean');

        $this->assertTrue(Rut::isFormatted($formatted));
        $this->assertTrue(Rut::isDatabaseFormat($database));
        $this->assertMatchesRegularExpression('/^\d{2,9}$/', $clean);

        $this->assertTrue(Rut::isValid($formatted));
        $this->assertTrue(Rut::isValid($database));
        $this->assertTrue(Rut::isValid($clean));
    }

    public function test_it_generates_multiple_fake_ruts_in_the_requested_format(): void
    {
        $ruts = Rut::fake(10, 'database');

        $this->assertCount(10, $ruts);

        foreach ($ruts as $rut) {
            $this->assertTrue(Rut::isValid($rut));
            $this->assertTrue(Rut::isDatabaseFormat($rut));
        }
    }

    public function test_faker_is_an_alias_for_fake(): void
    {
        $rut = Rut::faker(format: 'clean');

        $this->assertIsString($rut);
        $this->assertTrue(Rut::isValid($rut));
        $this->assertMatchesRegularExpression('/^\d{2,9}$/', $rut);
    }

    public function test_it_limits_fake_rut_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Rut::fake(51);
    }

    public function test_it_rejects_unknown_fake_formats(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Rut::fake(format: 'xml');
    }

    public function test_it_returns_array_representation(): void
    {
        $this->assertSame([
            'digits' => '12345678',
            'dv' => '5',
            'clean' => '123456785',
            'database' => '12345678-5',
            'formatted' => '12.345.678-5',
            'is_valid' => true,
        ], Rut::toArray('12.345.678-5'));
    }

    public function test_it_detects_known_formats(): void
    {
        $this->assertTrue(Rut::isFormatted('12.345.678-5'));
        $this->assertFalse(Rut::isFormatted('12345678-5'));

        $this->assertTrue(Rut::isDatabaseFormat('12345678-5'));
        $this->assertFalse(Rut::isDatabaseFormat('12.345.678-5'));
    }

    public function test_it_throws_for_invalid_parseable_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Rut::parse('abc');
    }

    public function test_validation_rule_accepts_valid_ruts(): void
    {
        $failed = false;

        (new ValidRut)->validate('rut', '12.345.678-5', function () use (&$failed): void {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_validation_rule_rejects_invalid_values(): void
    {
        $message = null;

        (new ValidRut)->validate('rut', '12.345.678-6', function (string $failMessage) use (&$message): void {
            $message = $failMessage;
        });

        $this->assertSame('El campo :attribute no es un RUT valido.', $message);
    }
}
