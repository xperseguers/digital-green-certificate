<?php
declare(strict_types=1);

namespace Causal\DGC;

class Decoder
{
    public function decodeFromQR(string $input): array
    {
        if (strpos($input, 'HC1:') !== 0) {
            throw new \Exception('Only Health Certificate Version 1 is supported');
        }

        $b45Data = substr($input, 4);
        $zlibData = Base45::decode($b45Data);
        $cborData = zlib_decode($zlibData);

        $cborDecoder = new \Firehed\CBOR\Decoder();
        $rawData = json_decode($cborDecoder->decode($cborData), true);

        return $rawData;
    }

    public function prettify(array $rawData): array
    {
        // TODO: Add support for multiple vaccines and multiple types of certificates:
        //       1 = vaccination certificate
        //       2 = test certificate
        //       3 = certificate of recovery

        // 1 = "vaccination certificate"
        $certificate = $rawData[-260][1];

        $data = [
            'issuer' => $rawData[1],
            'issuingDate' => date('c', $rawData[6]),
            'expiringDate' => date('c', $rawData[4]),
            'certificates' => [
                'vaccination' => [
                    'person' => [
                        'familyName' => $certificate['nam']['fn'],
                        'givenName' => $certificate['nam']['gn'],
                        'familyNameTransliterated' => $certificate['nam']['fnt'],
                        'givenNameTransliterated' => $certificate['nam']['gnt'],
                        'dateOfBirth' => $certificate['dob'],
                    ],
                    'vaccine' => [
                        'singleDoses' => $certificate['v'][0]['sd'],
                        'diseaseOrAgentTargeted' => $this->getDiseaseOrAgentTargeted($certificate['v'][0]['tg']),
                        'vaccineType' => $this->getVaccineType($certificate['v'][0]['vp']),
                        'product' => $this->getProduct($certificate['v'][0]['mp']),
                        'manufacturer' => $this->getManufacturer($certificate['v'][0]['ma']),
                        'date' => $certificate['v'][0]['dt'],
                        'country' => $certificate['v'][0]['co'],
                        'issuer' => $certificate['v'][0]['is'],
                        'id' => $certificate['v'][0]['ci'],
                    ],
                ],
            ],
        ];

        return $data;
    }

    protected function getDiseaseOrAgentTargeted(string $code): string
    {
        switch ($code) {
            case '840539006':
                return 'COVID-19';
        }

        return 'Unknown: ' . $code;
    }

    protected function getVaccineType(string $code): string
    {
        switch ($code) {
            case '1119305005':
                return 'SARS-CoV2 antigen vaccine';
            case '1119349007':
                return 'SARS-CoV2 mRNA vaccine';
            case 'J07BX03':
                return 'covid-19 vaccines';
        }

        return 'Unknown: ' . $code;
    }

    protected function getProduct(string $code): string
    {
        switch ($code) {
            case 'EU/1/20/1528':
                return 'Comirnaty';
            case 'EU/1/20/1507':
                return 'COVID-19 Vaccine Moderna';
            case 'EU/1/21/1529':
                return 'Vaxzevria';
            case 'EU/1/20/1525':
                return 'COVID-19 Vaccine Janssen';
            case 'Sputnik-V':
                return 'Sputnik V';
            case 'InactivatedSARS-CoV-2-Vero-Cell':
                return 'Inactivated SARSCoV-2 (Vero Cell)';
            case 'Covaxin':
                return 'Covaxin (also known as BBV152 A, B, C)';
            case 'CVnCoV':
            case 'NVXCoV2373':
            case 'Convidecia':
            case 'EpiVacCorona':
            case 'BBIBP-CorV':
            case 'CoronaVac':
                return $code;
        }

        return 'Unknown: ' . $code;
    }

    protected function getManufacturer(string $code): string
    {
        switch ($code) {
            case 'ORG-100001699':
                return 'AstraZeneca AB';
            case 'ORG-100030215':
                return 'Biontech Manufacturing GmbH';
            case 'ORG-100001417':
                return 'Janssen-Cilag International';
            case 'ORG-100031184':
                return 'Moderna Biotech Spain S.L.';
            case 'ORG-100006270':
                return 'Curevac AG';
            case 'ORG-100013793':
                return 'CanSino Biologics';
            case 'ORG-100020693':
                return 'China Sinopharm International Corp. - Beijing location';
            case 'ORG-100010771':
                return 'Sinopharm Weiqida Europe Pharmaceutical s.r.o. - Prague location';
            case 'ORG-100024420':
                return 'Sinopharm Zhijun (Shenzhen) Pharmaceutical Co. Ltd. - Shenzhen location';
            case 'ORG-100032020':
                return 'Novavax CZ AS';
            case 'GamaleyaResearchInstitute':
                return 'Gamaleya Research Institute';
            case 'VectorInstitute':
                return 'Vector Institute';
            case 'SinovacBiotech':
                return 'Sinovac Biotech';
            case 'BharatBiotech':
                return 'Bharat Biotech';
        }

        return 'Unknown: ' . $code;
    }
}
