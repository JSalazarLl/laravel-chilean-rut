<?php

declare(strict_types=1);

namespace JSalazarLl\Rut;

use InvalidArgumentException;
use JSalazarLl\Rut\Data\RutData;

final class Rut
{
    public static function isValid(string|int|null $rut): bool
    {
        $data = self::trySplit($rut);

        if ($data === null) {
            return false;
        }

        return $data->dv === self::calculateDv($data->digits);
    }

    public static function format(string|int $rut): string
    {
        return self::parse($rut)->formatted();
    }

    public static function mask(string|int $rut): string
    {
        return self::format($rut);
    }

    public static function formatForDatabase(string|int $rut): string
    {
        return self::parse($rut)->database();
    }

    public static function normalize(string|int $rut): string
    {
        return self::formatForDatabase($rut);
    }

    public static function clean(string|int $rut): string
    {
        return self::parse($rut)->clean();
    }

    public static function unmask(string|int $rut): string
    {
        return self::clean($rut);
    }

    public static function split(string|int $rut): RutData
    {
        return self::parse($rut);
    }

    public static function parse(string|int $rut): RutData
    {
        $data = self::trySplit($rut);

        if ($data === null) {
            throw new InvalidArgumentException('El RUT recibido no tiene un formato valido.');
        }

        return $data;
    }

    public static function digits(string|int $rut): string
    {
        return self::parse($rut)->digits;
    }

    public static function dv(string|int $rut): string
    {
        return self::parse($rut)->dv;
    }

    public static function calculateDv(string|int $digits): string
    {
        $digits = preg_replace('/\D+/', '', (string) $digits) ?? '';

        if ($digits === '') {
            throw new InvalidArgumentException('Los digitos del RUT son requeridos para calcular el DV.');
        }

        $multiplier = 2;
        $sum = 0;

        for ($index = strlen($digits) - 1; $index >= 0; $index--) {
            $sum += ((int) $digits[$index]) * $multiplier;
            $multiplier = $multiplier === 7 ? 2 : $multiplier + 1;
        }

        $result = 11 - ($sum % 11);

        return match ($result) {
            11 => '0',
            10 => 'K',
            default => (string) $result,
        };
    }

    /**
     * @return array{digits: string, dv: string, clean: string, database: string, formatted: string, is_valid: bool}
     */
    public static function toArray(string|int $rut): array
    {
        $data = self::parse($rut);

        return [
            ...$data->toArray(),
            'is_valid' => self::isValid($rut),
        ];
    }

    public static function isFormatted(string|int $rut): bool
    {
        return preg_match('/^\d{1,3}(?:\.\d{3}){1,2}-[\dkK]$/', trim((string) $rut)) === 1;
    }

    public static function isDatabaseFormat(string|int $rut): bool
    {
        return preg_match('/^\d{1,8}-[\dkK]$/', trim((string) $rut)) === 1;
    }

    public static function throwIfInvalid(string|int|null $rut): void
    {
        if (! self::isValid($rut)) {
            throw new InvalidArgumentException('El RUT recibido no es valido.');
        }
    }

    private static function trySplit(string|int|null $rut): ?RutData
    {
        if ($rut === null) {
            return null;
        }

        $clean = strtoupper(trim((string) $rut));

        if (preg_match('/^[\dK.\-\s]+\z/', $clean) !== 1) {
            return null;
        }

        $clean = preg_replace('/[^0-9K]+/', '', $clean) ?? '';

        if (strlen($clean) < 2 || preg_match('/^\d+K?\z/', $clean) !== 1) {
            return null;
        }

        $digits = substr($clean, 0, -1);
        $dv = substr($clean, -1);

        if ($digits === '' || strlen($digits) > 8 || preg_match('/^\d+\z/', $digits) !== 1) {
            return null;
        }

        return new RutData($digits, $dv);
    }
}
