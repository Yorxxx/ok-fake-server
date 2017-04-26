<?php

use Tests\BrowserKitTestCase;

class SMSProviderTest extends BrowserKitTestCase
{

    /**
     * @test
     * If environment requests to use NEXMO as the default SMS provider, the app should return NexmoRepository whenever requests to fulfill its dependencies
     */
    public function given_NEXMOProvider_When_getSMSRepository_Then_ShouldReturnNexmoRepository() {

        // Arrange
        config(['SMS_PROVIDER' => 'NEXMO']);

        // Act
        $result = $this->app->make(\App\Repositories\SMSRepositoryInterface::class);

        // Assert
        self::assertNotNull($result);
        self::assertTrue($result instanceof \App\Repositories\NexmoRepository);
    }

    /**
     * @test
     * If environment requests to use Twilio as the default SMS provider, the app should return TwilioRepository whenever requests to fulfill its dependencies
     */
    public function given_TwilioProvider_When_getSMSRepository_Then_ShouldReturnTwilioRepository() {

        // Arrange
        config(['SMS_PROVIDER' => 'TWILIO']);

        // Act
        $result = $this->app->make(\App\Repositories\SMSRepositoryInterface::class);

        // Assert
        self::assertNotNull($result);
        self::assertTrue($result instanceof \App\Repositories\TwilioRepository);
    }

    /**
     * @test
     * If environment requests to use SMSPubli as the default SMS provider, the app should return SMSPubliRepository whenever requests to fulfill its dependencies
     */
    public function given_SMSPubliProvider_When_getSMSRepository_Then_ShouldReturnSMSPubliRepository() {

        // Arrange
        config(['SMS_PROVIDER' => 'SMS_PUBLI']);

        // Act
        $result = $this->app->make(\App\Repositories\SMSRepositoryInterface::class);

        // Assert
        self::assertNotNull($result);
        self::assertTrue($result instanceof \App\Repositories\SMSPubliRepository);
    }

    /**
     * @test
     * If no SMS provider, should return a fake one.
     */
    public function given_noProviderSpecified_When_getSMSRepository_Then_ShouldReturnMockRepository() {

        // Arrange
        config(['SMS_PROVIDER' => null]);

        // Act
        $result = $this->app->make(\App\Repositories\SMSRepositoryInterface::class);

        // Assert
        self::assertNotNull($result);
        self::assertTrue($result instanceof \Tests\Unit\Repositories\SMSMockRepository);
    }
}
