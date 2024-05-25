<?php

use Cinexpert\CertificateValidation\CertificateService;
use PHPUnit\Framework\TestCase;

class CertificateServiceTest extends TestCase
{
    protected CertificateService $instance;

    public function setUp(): void
    {
    }

    protected function setupInstance(string $pathToCertificate): void
    {
        $this->instance = new CertificateService(
            $pathToCertificate,
            __DIR__ . '/../certificates/public-key.pem'
        );
    }

    public function testCanCreateCinema(): void
    {
        $this->setupInstance(__DIR__ . '/../certificates/cert-valid-1-1.pem');
        $this->assertTrue($this->instance->canCreateCinema(0));
    }

    public function testCanCreateCinema_NbCinemasAlreadyReached(): void
    {
        $this->setupInstance(__DIR__ . '/../certificates/cert-valid-1-1.pem');
        $this->assertFalse($this->instance->canCreateCinema(1));
    }

    public function testCanCreateCinema_TooManyCinemas(): void
    {
        $this->setupInstance(__DIR__ . '/../certificates/cert-valid-1-1.pem');
        $this->assertFalse($this->instance->canCreateCinema(2));
    }

    public function testCanCreateRoom(): void
    {
        $this->setupInstance(__DIR__ . '/../certificates/cert-valid-1-1.pem');
        $this->assertTrue($this->instance->canCreateRoom(0));
    }

    public function testCanCreateRoom_NbRoomsAlreadyReached(): void
    {
        $this->setupInstance(__DIR__ . '/../certificates/cert-valid-1-1.pem');
        $this->assertFalse($this->instance->canCreateRoom(1));
    }

    public function testCanCreateRoom_TooManyRooms(): void
    {
        $this->setupInstance(__DIR__ . '/../certificates/cert-valid-1-1.pem');
        $this->assertFalse($this->instance->canCreateRoom(2));
    }
}
