<?php

declare(strict_types=1);

namespace JSalazarLl\Rut\Data;

final class RutData
{
    public function __construct(
        public readonly string $digits,
        public readonly string $dv,
    ) {
    }

    public function clean(): string
    {
        return $this->digits.$this->dv;
    }

    public function database(): string
    {
        return $this->digits.'-'.$this->dv;
    }

    public function formatted(): string
    {
        return number_format((int) $this->digits, 0, '', '.').'-'.$this->dv;
    }

    /**
     * @return array{digits: string, dv: string, clean: string, database: string, formatted: string}
     */
    public function toArray(): array
    {
        return [
            'digits' => $this->digits,
            'dv' => $this->dv,
            'clean' => $this->clean(),
            'database' => $this->database(),
            'formatted' => $this->formatted(),
        ];
    }
}
