![](logo.png)

# VDF Converter

![Tests](https://github.com/exayer/vdf-converter/workflows/Tests/badge.svg)
![Latest Stable Version](https://img.shields.io/packagist/v/exayer/vdf-converter)

A memory efficient parser for the Valve Data Format (*.vdf) written in PHP.
Fully supports the [VDF specification](https://developer.valvesoftware.com/wiki/KeyValues), except for the `#include` macro.

## Requirements

PHP 7 or later. No production dependencies.

## Install

You can install the package via composer:

``` bash
composer require exayer/vdf-converter
```

## Usage

### Convert VDF to generator/array

Let's say we are parsing the following VDF:
```php
$vdf = '{
    "mercury" {
        "distance" "58"
    }
    "venus" {
        "distance" "108"
    }
    "earth" {
        "distance" "149"
    }
}';
```
It can be parsed in one of these ways (based on input source):
```php
<?php

use EXayer\VdfConverter\VdfConverter;

// $vdf declaration

$planets = VdfConverter::fromString($vdf);

$planets = VdfConverter::fromFile('data://text/plain,' . $vdf); // usually filename here

$tempFile = tmpfile();
fwrite($tempFile, $vdf);
fseek($tempFile, 0);
$planets = VdfConverter::fromStream($tempFile);

$planets = VdfConverter::fromIterable([substr($vdf, 0, -60), substr($vdf, -60)]);
```
To get the data you need to iterate over generator using foreach
```php
foreach ($planets as $name => $data) {
    // #1 iteration: $name === "mercury" $data === ["distance" => "58"]
    // #2 iteration: $name === "venus"   $data === ["distance" => "108"]
    // #3 iteration: $name === "earth"   $data === ["distance" => "149"]
}
```
Or simply convert it to array

```php
$result = iterator_to_array($planets);

//
//  $result = [
//    "mercury" => [
//      "distance" => "58"
//    ]
//    "venus" => [
//      "distance" => "108"
//    ]
//    "earth" => [
//      "distance" => "149"
//    ]
// ]
```

### Duplicate key handling

Some VDFs are known to contain duplicate keys. 
To keep the result structure the same as the VDF and since the parser is generator based (not keeping in memory pairs) 
the duplicated key will be modified - the counter will be concatenated to the end (e.g. `key__[2]`).

```php
$vdf = '{
    "mercury" {
        "distance" "58"
        "velocity" "35"
        "distance" "92"
    }
    "mercury" {
        "distance" "108"
    }
    "earth" {
        "distance" "149"
    }
}';

$result = iterator_to_array(VdfConverter::fromString($vdf));

//
//  $result = [
//    "mercury" => [
//      "distance" => "58"
//      "velocity" => "35"
//      "distance__[2]" => "92"
//    ]
//    "mercury__[2]" => [
//      "distance" => "108"
//    ]
//    "earth" => [
//      "distance" => "149"
//    ]
// ]
```

#### Customizing key format

If you want to customize the formatting key process, you can create your own custom formatter. A formatter is any class that implements `EXayer\VdfConverter\UniqueKey\Formatter`.

This is what that interface looks like.

```php
namespace EXayer\VdfConverter\UniqueKey;

interface Formatter
{
    public function buildKeyName(string $key, int $index): string;
}
```

After creating your formatter, you can specify its class name in the `uniqueKeyFormatter` method of the `EXayer\VdfConverter\VdfConverterConfig` object. The config can be passed as second argument to any `from` builder method. Your formatter will then be used by default for all duplicate key handling calls.

You can also specify a signer for a specific webhook call:

```php
$config = VdfConverterConfig::create()
    ->uniqueKeyFormatter(YourCustomFormatter::class);

$iterator = VdfConverter::fromString($vdf, $config);
```

**Warning**: Do not create formatters that create a key like `__2`, as some VDFs may have keys in this format.

## Testing

```bash
composer test
```

## Features to implement

* Key Pointer - Parse only the specific part of the VDF based on the path built from the keys (e.g. 'key1.key2.key3').

## Inspiration

The code is inspired by [@halaxa](https://github.com/halaxa) package [json-machine](https://github.com/halaxa/json-machine). Thank you!
		
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.