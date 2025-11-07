<?php

namespace App\Services\Verifactu;

class QrService
{
    public function __construct(
        protected array $config = []
    ) {
        $this->config = $config ?: config('verifactu.qr', []);
    }

    public function buildData(array $payload, array $providerResponse = []): string
    {
        $data = [
            'nif'  => $payload['emisor']['nif'] ?? '',
            'num'  => $payload['numero'] ?? '',
            'fec'  => $payload['fechaExpedicion'] ?? '',
            'tot'  => $payload['totales']['total'] ?? 0,
            'hash' => $providerResponse['verification_hash'] ?? '',
        ];
        return http_build_query($data, '', '&', PHP_QUERY_RFC3986);
    }

    public function makeSvg(array $payload, array $providerResponse = []): string
    {
        $text = $this->buildData($payload, $providerResponse);

        if (class_exists(\Endroid\QrCode\QrCode::class)) {
            $size = (int)($this->config['size'] ?? 180);
            $margin = (int)($this->config['margin'] ?? 2);
            $ecc = strtoupper((string)($this->config['ecc'] ?? 'M'));

            $qr = new \Endroid\QrCode\QrCode($text);
            $qr->setSize($size);
            $qr->setMargin($margin);

            if (class_exists(\Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevel::class)) {
                $level = match ($ecc) {
                    'L' => new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow(),
                    'Q' => new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelQuartile(),
                    'H' => new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh(),
                    default => new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium(),
                };
                $qr->setErrorCorrectionLevel($level);
            }

            $writer = new \Endroid\QrCode\Writer\SvgWriter();
            return $writer->write($qr)->getString();
        }

        $safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        return '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120"><rect width="120" height="120" fill="#eee"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="10">QR</text><title>' . $safe . '</title></svg>';
    }
}
