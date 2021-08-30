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

// Extract issuer, issuing date, expiring date, type of vaccine, manufacturer, etc.
$data = $decoder->decodeFromQR($input);

// Prettify the data so that they are more human-readable (beware: structure may change)
// Basically this returns the exact same data you read in the official Swiss app "Covid Cert"
$humanReadableData = $decoder->prettify($data);
```

## Types of Certificates

Following types of certificates are supported:

- Vaccination certificate
- Test certificate
- Recovery certificate

## Disclaimer

- There is currently NO CHECK of the digital signature (it is not clear at the moment whether
  public keys of the various issuers are available to anyone).
- Human-readable conversion is based on European data mapping on 2021/08/30.
- This library is provided as-is, use it at your own risk!

## References

- https://ec.europa.eu/health/sites/default/files/ehealth/docs/digital-green-certificates_v3_en.pdf
- https://ec.europa.eu/health/sites/default/files/ehealth/docs/digital-green-certificates_dt-specifications_en.pdf
- https://covid-19-diagnostics.jrc.ec.europa.eu/devices/hsc-common-recognition-rat

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
