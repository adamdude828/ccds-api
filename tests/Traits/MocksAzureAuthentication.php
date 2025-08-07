<?php

namespace Tests\Traits;

use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;

trait MocksAzureAuthentication
{
    protected function mockSuccessfulAzureAuthentication(string $token, string $email = 'test@example.com', string $name = 'Test User', string $id = '12345')
    {
        $socialiteUser = $this->createSocialiteUser($email, $name, $id);

        Socialite::shouldReceive('driver')
            ->with('azure')
            ->andReturn(Mockery::mock()
                ->shouldReceive('stateless')
                ->andReturn(Mockery::self())
                ->shouldReceive('userFromToken')
                ->with($token)
                ->andReturn($socialiteUser)
                ->getMock()
            );
    }

    protected function mockFailedAzureAuthentication(string $token)
    {
        Socialite::shouldReceive('driver')
            ->with('azure')
            ->andReturn(Mockery::mock()
                ->shouldReceive('stateless')
                ->andReturn(Mockery::self())
                ->shouldReceive('userFromToken')
                ->with($token)
                ->andThrow(new \Exception('Invalid token'))
                ->getMock()
            );
    }

    private function createSocialiteUser(string $email, string $name, string $id): SocialiteUser
    {
        $user = new SocialiteUser;
        $user->id = $id;
        $user->name = $name;
        $user->email = $email;

        return $user;
    }
} 