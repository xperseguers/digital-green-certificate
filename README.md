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
```

```json
{
  1: "DE",
  4: 1643356073,
  6: 1622316073,
  -260: {
    1: {
      v: [
        {
          ci: "URN:UVCI:01DE/IZ12345A/5CWLU12RNOB9RXSEOP6FG8#W",
          co: "DE",
          dn: 2,
          dt: "2021-05-29",
          is: "Robert Koch-Institut",
          ma: "ORG-100031184",
          mp: "EU/1/20/1507",
          sd: 2,
          tg: "840539006",
          vp: "1119349007"
        }
      ],
      dob: "1964-08-12",
      nam: {
        fn: "Mustermann",
        gn: "Erika",
        fnt: "MUSTERMANN",
        gnt: "ERIKA"
      },
      ver: "1.0.0"
    }
  }
}
```

You may then prettify the data so that they are more human-readable:

```php
// Basically this returns the exact same data you read in the official Swiss app "Covid Cert"
$humanReadableData = $decoder->prettify($data);
```

```json
{
  issuer: "DE",
  issuingDate: "2021-05-29T21:21:13+02:00",
  expiringDate: "2022-01-28T08:47:53+01:00",
  certificates: {
    vaccination: {
      person: {
        familyName: "Mustermann",
        givenName: "Erika",
        familyNameTransliterated: "MUSTERMANN",
        givenNameTransliterated: "ERIKA",
        dateOfBirth: "1964-08-12"
      },
      info: {
        singleDoses: 2,
        diseaseOrAgentTargeted: "COVID-19",
        vaccineType: "SARS-CoV2 mRNA vaccine",
        product: "COVID-19 Vaccine Moderna",
        manufacturer: "Moderna Biotech Spain S.L.",
        date: "2021-05-29",
        country: "DE",
        issuer: "Robert Koch-Institut",
        id: "URN:UVCI:01DE/IZ12345A/5CWLU12RNOB9RXSEOP6FG8#W"
      }
    }
  }
}
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
