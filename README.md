# Laravel Chilean RUT

Utilidades para validar, formatear y parsear RUT chileno en proyectos Laravel y PHP.

El paquete incluye una clase principal independiente de Laravel y una regla de validacion lista para usar con el validador de Laravel.

Repositorio: [JSalazarLl/laravel-chilean-rut](https://github.com/JSalazarLl/laravel-chilean-rut)

## Requisitos

- PHP 8.2 o superior.
- Composer.
- Laravel 11, 12 o 13 si quieres usar la regla de validacion incluida.

## Instalacion

Instala el paquete con Composer:

```bash
composer require jsalazarll/rut:^1.0
```

Si ya agregaste el paquete previamente, puedes actualizarlo con:

```bash
composer update jsalazarll/rut
```

## Uso rapido

```php
use JSalazarLl\Rut\Rut;

Rut::isValid('12.345.678-5');           // true
Rut::format('123456785');               // "12.345.678-5"
Rut::formatForDatabase('12.345.678-5'); // "12345678-5"
Rut::clean('12.345.678-5');             // "123456785"
```

## Validacion en Laravel

Usa `ValidRut` en un Form Request:

```php
use JSalazarLl\Rut\Rules\ValidRut;

public function rules(): array
{
    return [
        'rut' => ['required', new ValidRut],
    ];
}
```

O directamente con `Validator::make`:

```php
use Illuminate\Support\Facades\Validator;
use JSalazarLl\Rut\Rules\ValidRut;

$validator = Validator::make($request->all(), [
    'rut' => ['required', new ValidRut],
]);
```

La regla acepta RUT en formatos habituales:

```text
12.345.678-5
12345678-5
123456785
```

Si el RUT no es valido, la regla retorna el mensaje:

```text
El campo :attribute no es un RUT valido.
```

## Formatos disponibles

Para el RUT `12.345.678-5`, la libreria puede entregar:

| Metodo                     | Resultado      | Uso sugerido                           |
| -------------------------- | -------------- | -------------------------------------- |
| `Rut::format()`            | `12.345.678-5` | Mostrar en pantalla                    |
| `Rut::formatForDatabase()` | `12345678-5`   | Guardar normalizado                    |
| `Rut::clean()`             | `123456785`    | Obtener solo caracteres significativos |
| `Rut::digits()`            | `12345678`     | Obtener el cuerpo numerico             |
| `Rut::dv()`                | `5`            | Obtener el digito verificador          |

## Referencia de API

Todas las funciones principales se llaman desde:

```php
use JSalazarLl\Rut\Rut;
```

### `Rut::isValid()`

Valida si un RUT tiene estructura parseable y digito verificador correcto.

```php
Rut::isValid('12.345.678-5'); // true
Rut::isValid('12.345.678-6'); // false
Rut::isValid(null);           // false
```

Firma:

```php
Rut::isValid(string|int|null $rut): bool
```

### `Rut::format()`

Entrega el RUT con puntos y guion.

```php
Rut::format('123456785'); // "12.345.678-5"
```

Firma:

```php
Rut::format(string|int $rut): string
```

Alias:

```php
Rut::mask(string|int $rut): string
```

### `Rut::formatForDatabase()`

Entrega el RUT sin puntos y con guion.

```php
Rut::formatForDatabase('12.345.678-5'); // "12345678-5"
```

Firma:

```php
Rut::formatForDatabase(string|int $rut): string
```

Alias:

```php
Rut::normalize(string|int $rut): string
```

### `Rut::clean()`

Entrega el RUT sin puntos ni guion.

```php
Rut::clean('12.345.678-5'); // "123456785"
```

Firma:

```php
Rut::clean(string|int $rut): string
```

Alias:

```php
Rut::unmask(string|int $rut): string
```

### `Rut::split()` y `Rut::parse()`

Retornan un objeto `RutData` con el cuerpo numerico y el digito verificador.

```php
$rut = Rut::parse('12.345.678-5');

$rut->digits;      // "12345678"
$rut->dv;          // "5"
$rut->formatted(); // "12.345.678-5"
$rut->database();  // "12345678-5"
$rut->clean();     // "123456785"
```

Firmas:

```php
Rut::split(string|int $rut): JSalazarLl\Rut\Data\RutData
Rut::parse(string|int $rut): JSalazarLl\Rut\Data\RutData
```

### `Rut::digits()`

Entrega solo el cuerpo numerico del RUT.

```php
Rut::digits('12.345.678-5'); // "12345678"
```

Firma:

```php
Rut::digits(string|int $rut): string
```

### `Rut::dv()`

Entrega solo el digito verificador.

```php
Rut::dv('12.345.678-5'); // "5"
```

Firma:

```php
Rut::dv(string|int $rut): string
```

### `Rut::calculateDv()`

Calcula el digito verificador desde el cuerpo numerico del RUT.

```php
Rut::calculateDv('12345678'); // "5"
Rut::calculateDv('6');        // "K"
```

Firma:

```php
Rut::calculateDv(string|int $digits): string
```

### `Rut::toArray()`

Entrega una representacion completa del RUT.

```php
Rut::toArray('12.345.678-5');
```

Resultado:

```php
[
    'digits' => '12345678',
    'dv' => '5',
    'clean' => '123456785',
    'database' => '12345678-5',
    'formatted' => '12.345.678-5',
    'is_valid' => true,
]
```

Firma:

```php
/**
 * @return array{
 *     digits: string,
 *     dv: string,
 *     clean: string,
 *     database: string,
 *     formatted: string,
 *     is_valid: bool
 * }
 */
Rut::toArray(string|int $rut): array
```

### `Rut::isFormatted()`

Indica si el RUT viene con formato visual chileno: puntos y guion.

```php
Rut::isFormatted('12.345.678-5'); // true
Rut::isFormatted('12345678-5');   // false
```

Firma:

```php
Rut::isFormatted(string|int $rut): bool
```

Esta funcion solo revisa el formato. No valida el digito verificador.

### `Rut::isDatabaseFormat()`

Indica si el RUT viene en el formato recomendado para base de datos: sin puntos y con guion.

```php
Rut::isDatabaseFormat('12345678-5');   // true
Rut::isDatabaseFormat('12.345.678-5'); // false
```

Firma:

```php
Rut::isDatabaseFormat(string|int $rut): bool
```

Esta funcion solo revisa el formato. No valida el digito verificador.

### `Rut::throwIfInvalid()`

Lanza `InvalidArgumentException` si el RUT no es valido.

```php
Rut::throwIfInvalid('12.345.678-5'); // no lanza excepcion
Rut::throwIfInvalid('12.345.678-6'); // lanza InvalidArgumentException
```

Firma:

```php
Rut::throwIfInvalid(string|int|null $rut): void
```

## Manejo de errores

Los metodos que parsean o transforman un RUT lanzan `InvalidArgumentException` cuando el valor recibido no tiene una estructura compatible con un RUT chileno.

```php
use InvalidArgumentException;
use JSalazarLl\Rut\Rut;

try {
    $rut = Rut::format($input);
} catch (InvalidArgumentException) {
    // Manejar el valor invalido.
}
```

Si solo necesitas saber si el valor es valido, usa `Rut::isValid()`, que retorna `false` en lugar de lanzar una excepcion.

## Objeto `RutData`

`Rut::split()` y `Rut::parse()` retornan una instancia de `JSalazarLl\Rut\Data\RutData`.

```php
use JSalazarLl\Rut\Rut;

$rut = Rut::parse('12.345.678-5');

$rut->digits;      // "12345678"
$rut->dv;          // "5"
$rut->clean();     // "123456785"
$rut->database();  // "12345678-5"
$rut->formatted(); // "12.345.678-5"
$rut->toArray();   // array con digits, dv, clean, database y formatted
```

## Licencia

MIT.
