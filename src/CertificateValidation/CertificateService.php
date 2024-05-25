<?php

namespace Cinexpert\CertificateValidation;

class CertificateService
{
    protected bool $certificateCheckEnabled = false;
    protected int $nbOfAllowedCinemas       = 0;
    protected int $nbOfAllowedRooms         = 0;

    /** @var array<string, mixed>|null */
    protected ?array $decodedCertificate = null;

    public function __construct(
        protected string $pathToCertificate,
        protected string $pathToPublicKey
    ) {
    }

    public function canCreateCinema(int $nbExistingCinemas): bool
    {
        if (empty($this->decodedCertificate) && empty($this->pathToPublicKey)) {
            return true;
        }

        $this->decodeCertificate();

        if ($nbExistingCinemas >= $this->nbOfAllowedCinemas) {
            return false;
        }

        return true;
    }

    public function canCreateRoom(int $nbExistingRooms): bool
    {
        if (empty($this->decodedCertificate) && empty($this->pathToPublicKey)) {
            return true;
        }

        $this->decodeCertificate();

        if ($nbExistingRooms >= $this->nbOfAllowedRooms) {
            return false;
        }

        return true;
    }

    public function decodeCertificate(): void
    {
        if (empty($this->pathToCertificate) && empty($this->pathToPublicKey)) {
            return;
        }

        $this->certificateCheckEnabled = true;

        if (!file_exists($this->pathToCertificate)) {
            throw new CertificateException('No certificate found.');
        }

        if (!$certStr = file_get_contents($this->pathToCertificate)) {
            throw new CertificateException('Unable to read certificate file.');
        }

        if (!$certificate = openssl_x509_parse($certStr)) {
            throw new CertificateException('Unable to read certificate.');
        }

        $this->decodedCertificate = $certificate;

        if (!file_exists($this->pathToPublicKey)) {
            throw new CertificateException('No public key found.');
        }

        if (!$publicKey = file_get_contents($this->pathToPublicKey)) {
            throw new CertificateException('Unable to read public key.');
        }

        if (1 !== openssl_x509_verify($certStr, $publicKey)) {
            throw new CertificateException('Certificate is not valid.');
        }

        if (time() < $certificate['validFrom_time_t'] || $certificate['validTo_time_t'] < time()) {
            throw new CertificateException('Certificate not yet valid or is expired.');
        }

        if (
            !array_key_exists('extensions', $certificate) ||
            !array_key_exists('1.3.6.1.4.1.9999.1', $certificate['extensions']) ||
            !array_key_exists('1.3.6.1.4.1.9999.2', $certificate['extensions'])
        ) {
            throw new CertificateException("Certificate doesn't contain the necessary information.");
        }

        preg_match('/\d+/', $this->decodedCertificate['extensions']['1.3.6.1.4.1.9999.1'], $matches);
        if (!array_key_exists(0, $matches) || !ctype_digit($matches[0])) {
            throw new CertificateException('Invalid number of allowed cinemas in certificate.');
        }
        $this->nbOfAllowedCinemas = (int)$matches[0];

        preg_match('/\d+/', $this->decodedCertificate['extensions']['1.3.6.1.4.1.9999.2'], $matches);
        if (!array_key_exists(0, $matches) || !ctype_digit($matches[0])) {
            throw new CertificateException('Invalid number of allowed rooms in certificate.');
        }
        $this->nbOfAllowedRooms = (int)$matches[0];
    }
}
