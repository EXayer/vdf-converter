![](logo.png)

# VDF Converter

[![Total Downloads](https://img.shields.io/packagist/dt/EXayer/vdf-converter)](https://packagist.org/packages/exayer/vdf-converter)

An efficient, fast Parser, for Valve Data Format (*.vdf) written in PHP.
Fully supports [VDF specification](https://developer.valvesoftware.com/wiki/KeyValues), except for the `#include` macro.

## Install

You can install the package via composer:

``` bash
composer require exayer/vdf-converter
```

## Usage

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
    // #1 iteration: $name === "mercury" and $data === ["distance" => "58"]
    // #2 iteration: $name === "venus" and $data === ["distance" => "108"]
    // #3 iteration: $name === "earth" and $data === ["distance" => "149"]
}
```
Or simply convert to array

```php
$result = iterator_to_array($planets);

//
//  $result = [
//    'mercury' => [
//      'distance' => '58'
//    ]
//    'venus' => [
//      "distance' => '108'
//    ]
//    'earth' => [
//      'distance' => "149"
//    ]
// ]
```

## Testing

```bash
composer test
```

## Features to implement

* Duplicate key support - Some VDFs are known to contain duplicate keys. In such case, the key data will be overwritten. 
* Key Pointer - Parse only the specific part of VDF based on a path build from keys (e.g. 'key1.key2.key3').

## Inspiration

The code is driven by [@halaxa](https://github.com/halaxa) package [json-machine](https://github.com/halaxa/json-machine). Thank you!
		
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.