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
        $certificate = $rawData[-260][1];

        $data = [
            'issuer' => $rawData[1],
            'issuingDate' => date('c', $rawData[6]),
            'expiringDate' => date('c', $rawData[4]),
            'certificates' => [],
        ];

        $person = [
            'familyName' => $certificate['nam']['fn'],
            'givenName' => $certificate['nam']['gn'],
            'familyNameTransliterated' => $certificate['nam']['fnt'],
            'givenNameTransliterated' => $certificate['nam']['gnt'],
            'dateOfBirth' => $certificate['dob'],
        ];

        if (array_key_exists('v', $certificate)) {
            $data['certificates']['vaccination'] = [
                'person' => $person,
                'info' => [
                    'singleDoses' => $certificate['v'][0]['sd'],
                    'diseaseOrAgentTargeted' => $this->getDiseaseOrAgentTargeted($certificate['v'][0]['tg'] ?? ''),
                    'vaccineType' => $this->getVaccineType($certificate['v'][0]['vp'] ?? ''),
                    'product' => $this->getProduct($certificate['v'][0]['mp'] ?? ''),
                    'manufacturer' => $this->getManufacturer($certificate['v'][0]['ma'] ?? ''),
                    'date' => $certificate['v'][0]['dt'] ?? null,
                    'country' => $certificate['v'][0]['co'],
                    'issuer' => $certificate['v'][0]['is'],
                    'id' => $certificate['v'][0]['ci'],
                ],
            ];
        } elseif (array_key_exists('t', $certificate)) {
            $data['certificates']['recovery'] = [
                'person' => $person,
                'info' => [
                    'diseaseOrAgentTargeted' => $this->getDiseaseOrAgentTargeted($certificate['t'][0]['tg'] ?? ''),
                    'type' => $this->getType($certificate['t'][0]['tt'] ?? null),
                    'name' => $certificate['t'][0]['nm'] ?? null,
                    'device' => $this->getDevice($certificate['t'][0]['ma'] ?? null),
                    'result' => $certificate['t'][0]['tr'] ?? null,
                    'centre' => $certificate['t'][0]['tc'] ?? null,
                    'date' => $certificate['t'][0]['sc'] ?? null,
                    'country' => $certificate['t'][0]['co'],
                    'issuer' => $certificate['t'][0]['is'],
                    'id' => $certificate['t'][0]['ci'],
                ],
            ];
        } elseif (array_key_exists('r', $certificate)) {
            $data['certificates']['recovery'] = [
                'person' => $person,
                'info' => [
                    'validFrom' => $certificate['r'][0]['df'] ?? null,
                    'validUntil' => $certificate['r'][0]['du'] ?? null,
                    'date' => $certificate['r'][0]['fr'] ?? null,
                    'country' => $certificate['r'][0]['co'],
                    'issuer' => $certificate['r'][0]['is'],
                    'id' => $certificate['r'][0]['ci'],
                ],
            ];
        }

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

    protected function getVaccineType(?string $code): ?string
    {
        switch ($code) {
            case '1119305005':
                return 'SARS-CoV2 antigen vaccine';
            case '1119349007':
                return 'SARS-CoV2 mRNA vaccine';
            case 'J07BX03':
                return 'covid-19 vaccines';
        }

        return !empty($code) ? 'Unknown: ' . $code : null;
    }

    protected function getProduct(?string $code): ?string
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

        return !empty($code) ? 'Unknown: ' . $code : null;
    }

    protected function getManufacturer(?string $code): ?string
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

        return !empty($code) ? 'Unknown: ' . $code : null;
    }

    protected function getType(?string $code): ?string
    {
        switch ($code) {
            case 'LP217198-3':
                return 'Rapid immunoassay';
            case 'LP6464-4':
                return 'Nucleic acid amplification with probe detection';
        }

        return !empty($code) ? 'Unknown: ' . $code : null;
    }

    protected function getDevice(?string $code): ?string
    {
        if (empty($code)) {
            return null;
        }

        static $devices = null;
        if ($devices === null) {
            $devices = json_decode(file_get_contents(__DIR__ . '/data/hsc-common-recognition-rat.json'), true);
        }

        foreach ($devices['deviceList'] as $device) {
            if ($device['id_device'] === $code) {
                return $device['commercial_name'];
            }
        }

        return 'Unknown: ' . $code;
    }
}
