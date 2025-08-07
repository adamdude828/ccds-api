<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;

class UserModelTest extends TestCase
{
    /**
     * Test JWT identifier method returns getKey.
     */
    public function test_jwt_identifier_method()
    {
        $user = new User();
        $user->id = 123;
        
        $this->assertEquals(123, $user->getJWTIdentifier());
    }

    /**
     * Test JWT custom claims returns empty array.
     */
    public function test_jwt_custom_claims_returns_empty_array()
    {
        $user = new User();
        
        $this->assertIsArray($user->getJWTCustomClaims());
        $this->assertEmpty($user->getJWTCustomClaims());
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes()
    {
        $user = new User();
        $fillable = $user->getFillable();
        
        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    /**
     * Test hidden attributes.
     */
    public function test_hidden_attributes()
    {
        $user = new User();
        $hidden = $user->getHidden();
        
        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    /**
     * Test casts method returns correct types.
     */
    public function test_casts_method_returns_correct_types()
    {
        $user = new User();
        $casts = $user->getCasts();
        
        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertArrayHasKey('password', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']);
    }

    /**
     * Test model table name.
     */
    public function test_model_table_name()
    {
        $user = new User();
        
        $this->assertEquals('users', $user->getTable());
    }
} 