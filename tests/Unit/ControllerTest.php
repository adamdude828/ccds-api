<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Mockery;

class ControllerTest extends TestCase
{
    protected $controller;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new class extends Controller {
            public function __construct() {
                // Skip middleware in tests
            }
        };
    }
    
    /**
     * Test success method returns proper JSON response.
     */
    public function test_success_method_returns_proper_json()
    {
        $data = ['test' => 'value'];
        $response = $this->controller->success($data);
        
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals($data, $content['data']);
        $this->assertEquals('success', $content['message']);
    }
    
    /**
     * Test fail method returns proper error response.
     */
    public function test_fail_method_returns_error_response()
    {
        $message = 'Error occurred';
        $response = $this->controller->fail($message);
        
        $this->assertEquals(400, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('status', $content);
        $this->assertEquals($message, $content['message']);
        $this->assertEquals(0, $content['status']);
    }
    
    /**
     * Test successWithModels method with collection.
     */
    public function test_success_with_models_method()
    {
        $collection = new Collection([
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2']
        ]);
        
        $response = $this->controller->successWithModels($collection);
        
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('status', $content);
        $this->assertEquals($collection->toArray(), $content['data']);
        $this->assertEquals('success', $content['message']);
        $this->assertEquals(1, $content['status']);
    }
    
    /**
     * Test model method returns proper JSON response.
     */
    public function test_model_method_returns_proper_json()
    {
        $model = ['id' => 1, 'name' => 'Test Model'];
        $response = $this->controller->model($model);
        
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('status', $content);
        $this->assertEquals($model, $content['data']);
        $this->assertEquals('success', $content['message']);
        $this->assertEquals(1, $content['status']);
    }
} 