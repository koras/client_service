<?php

namespace Unit;

use App\Contracts\Services\ValidationServiceInterface;
use Tests\TestCase;

class ValidationServiceTest extends TestCase
{
    private ValidationServiceInterface $validationService;
    protected function setUp(): void
    {
        parent::setUp();
        $this->validationService = app(ValidationServiceInterface::class);
    }

    /**
     * @covers \App\Services\ValidationService::validatePhoneNumber
     */
    public function testValidatePhone(): void
    {
        $validPhone = '79001112233';
        $result = $this->validationService->validatePhoneNumber($validPhone);
        self::assertNull($result);

        $validPhone = '+79001112233';
        $result = $this->validationService->validatePhoneNumber($validPhone);
        self::assertNull($result);

        $validPhone = '+7(900)1112233';
        $result = $this->validationService->validatePhoneNumber($validPhone);
        self::assertNull($result);

        $validPhone = '8(900)1112233';
        $result = $this->validationService->validatePhoneNumber($validPhone);
        self::assertNull($result);
    }

    /**
     * @covers \App\Services\ValidationService::validatePhoneNumber
     * @return void
     */
    public function testFailValidatePhone(): void
    {
        $invalidPhone = 'w9001112233';
        $result = $this->validationService->validatePhoneNumber($invalidPhone);
        self::assertNotNull($result);

        $invalidPhone = '890033';
        $result = $this->validationService->validatePhoneNumber($invalidPhone);
        self::assertNotNull($result);
    }

    /**
     * @covers \App\Services\ValidationService::validateEmail
     * @return void
     */
    public function testValidateEmail(): void
    {
        $validEmail = 'mail@test.com';
        $result = $this->validationService->validateEmail($validEmail);
        self::assertNull($result);
    }

    /**
     * @covers \App\Services\ValidationService::validateEmail
     * @return void
     */
    public function testFailValidateEmail(): void
    {
        $invalidEmail = 'mail_test.com';
        $result = $this->validationService->validateEmail($invalidEmail);
        self::assertNotNull($result);
    }
}
