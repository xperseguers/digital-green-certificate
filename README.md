# Digital Green Certificate (DGC)

*DGC* is a PHP library that decodes Digital Green Certificates. In short, it decodes COVID-19 certificates.

## Install

Install with [composer](https://getcomposer.org/).

```bash
$ composer require causal/dgc
```

## Usage

```php
$decoder = new \Causal\DGC\Decoder();

// The string as embedded in the QR code of the certificate (e.g., you can use https://qrafter.com/)
$input = 'HC1:NCFOXNYTSFDHJI89.O%26V$L.BUTRDUV...';

$data = $decoder->decodeFromQR($input);
```

## References

https://ec.europa.eu/health/sites/default/files/ehealth/docs/digital-green-certificates_v3_en.pdf
https://ec.europa.eu/health/sites/default/files/ehealth/docs/digital-green-certificates_dt-specifications_en.pdf

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
