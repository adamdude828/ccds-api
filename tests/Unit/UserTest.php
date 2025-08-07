<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test JWT identifier method.
     */
    public function test_get_jwt_identifier_returns_user_key()
    {
        $user = User::factory()->create();
        
        $this->assertEquals($user->getKey(), $user->getJWTIdentifier());
    }

    /**
     * Test JWT custom claims method.
     */
    public function test_get_jwt_custom_claims_returns_empty_array()
    {
        $user = User::factory()->create();
        
        $this->assertIsArray($user->getJWTCustomClaims());
        $this->assertEmpty($user->getJWTCustomClaims());
    }

    /**
     * Test password is hashed.
     */
    public function test_password_is_hashed()
    {
        $user = User::factory()->create([
            'password' => 'password123'
        ]);
        
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(password_verify('password123', $user->password));
    }

    /**
     * Test email verified at cast.
     */
    public function test_email_verified_at_is_cast_to_datetime()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);
        
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes()
    {
        $fillableData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password'
        ];
        
        $user = User::create($fillableData);
        
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNotNull($user->password);
    }

    /**
     * Test hidden attributes.
     */
    public function test_hidden_attributes_are_not_visible_in_array()
    {
        $user = User::factory()->create();
        
        $array = $user->toArray();
        
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }
} 