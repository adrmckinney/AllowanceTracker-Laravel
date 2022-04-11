<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;

/**
 * Class APITestCase
 *
 * Prepares the GrapQL Client for testing and
 * Injects the authenticated user into the application
 *
 * @package Tests
 */
abstract class APITestCase extends TestCase
{
    use RefreshDatabase, WithFaker;

    // this is useful while making new API tests for debugging / testing your test code
    public function echoResponse(TestResponse $response, bool $pretty = true): void
    {
        echo "\n" . "\n" . 'calling function: ' . debug_backtrace()[1]['function'] . "\n" . "\n";
        echo '==============================' . "\n" . "\n";
        $this->echoResponseObj($response, $pretty);
        echo '==============================' . "\n" . "\n";
    }

    public function echoResponses(array $responses, bool $pretty = true): void
    {
        echo "\n" . "\n" . 'calling function: ' . debug_backtrace()[1]['function'] . "\n" . "\n";
        echo '==============================' . "\n" . "\n";
        foreach ($responses as $key => $response) {
            echo $key . ": \n";
            $this->echoResponseObj($response, $pretty);
        }
        echo '==============================' . "\n" . "\n";
    }

    protected function assertObjectKeyMissing($object, $objectKey)
    {
        $keyFound = false;
        $json = json_decode($object);
        foreach ($json as $key => $val) {
            if ($key === $objectKey) {
                $keyFound = true;
            }
        }

        $this->assertFalse($keyFound, 'Key: ' . $objectKey . ' found in response');
    }

    protected function assertApiSuccess($response)
    {
        $response->assertStatus(200);
        $this->assertObjectKeyMissing($response->content(), 'errors');
    }

    private function echoResponseObj(TestResponse $response, bool $pretty)
    {
        $flags = $pretty ? JSON_PRETTY_PRINT : 0;
        echo json_encode($response, $flags) . "\n" . "\n";
    }
}
