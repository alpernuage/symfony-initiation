<?php

namespace App\Tests;

use Symfony\Contracts\HttpClient\ResponseInterface;

trait ApiTestTrait
{
    use ContainerServiceTrait;

    /**
     * @return array<string, mixed>
     */
    private function getResponseContent(ResponseInterface $response): array
    {
        // param "false" to avoid throwing an Exception by ApiPlatform\Symfony\Bundle\Test\Response
        /** @var array<string, mixed> */
        return json_decode($response->getContent(false), true);
    }
}
