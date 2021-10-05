![](logo.png)

# VDF Converter

![Tests](https://github.com/exayer/vdf-converter/workflows/Tests/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/EXayer/vdf-converter)](https://packagist.org/packages/exayer/vdf-converter)
![Latest Stable Version](https://img.shields.io/packagist/v/exayer/vdf-converter)

Memory efficient parser for Valve Data Format (*.vdf) written in PHP.
Fully supports [VDF specification](https://developer.valvesoftware.com/wiki/KeyValues), except `#include` macro.

## Requirements

PHP 7 or later. No production dependencies.

## Install

You can install the package via composer:

``` bash
composer require exayer/vdf-converter
```

## Usage

### Convert VDF to generator/array

Let's say we parsing the following VDF:
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
To get data we need to iterate over generator using foreach
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
the duplicated key will be modified - the counter will be concatenated to the end (e.g. 'key__2').


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
//      "distance__2" => "92"
//    ]
//    "mercury__2" => [
//      "distance" => "108"
//    ]
//    "earth" => [
//      "distance" => "149"
//    ]
// ]
```
## Testing

```bash
composer test
```

## Features to implement

* Key Pointer - Parse only the specific part of VDF based on a path build from keys (e.g. 'key1.key2.key3').

## Inspiration

The code is driven by [@halaxa](https://github.com/halaxa) package [json-machine](https://github.com/halaxa/json-machine). Thank you!
		
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.