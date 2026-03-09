<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserControllerTest extends WebTestCase
{
    public function testUserEndpointRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/admin/agents');

        self::assertResponseStatusCodeSame(401);
    }
}
