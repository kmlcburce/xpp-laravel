<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class ForecastApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the forecast API endpoint returns expected JSON structure.
     */
    public function test_forecast_endpoint_returns_data()
    {
        $response = $this->getJson('/api/forecast?type=fnd');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'weatherForecast' => [
                        '*' => [
                            'forecastDate',
                            'forecastWeather',
                            'forecastMaxtemp' => ['value'],
                            'forecastMintemp' => ['value'],
                        ],
                    ],
                 ]);
    }

    /**
     * Test that generate-forecast endpoint returns JSON with metadata.
     */
    public function test_generate_forecast_returns_json_metadata()
    {
        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('put')->andReturn(true);
        Storage::shouldReceive('url')->andReturn('https://example.com/fake.csv');
        $response = $this->getJson('/api/generate-forecast?type=9day');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');

        $response->assertJsonStructure([
            'type',
            'filename',
            's3_url',
            'created_at',
            'updated_at',
            'id',
        ]);

        $responseData = $response->json(); // ✅ Fix: access response JSON properly

        $this->assertEquals('9day', $responseData['type']);
        $this->assertStringEndsWith('.csv', $responseData['filename']);
        $this->assertStringContainsString('https://', $responseData['s3_url']);
    }
}
