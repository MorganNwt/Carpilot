<?php

namespace App\Tests\Controller\Api\Admin;

use App\Entity\User\Seller;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class DeletionAccessTest extends WebTestCase
{
    public static function endpoints(): iterable
    {
        foreach (['sellers', 'agents', 'vehicles'] as $type) {
            yield $type => ['/api/admin/' . $type . '/1'];
        }
    }

    #[DataProvider('endpoints')]
    public function testAnonymousCannotDelete(string $url): void
    {
        $client = static::createClient();
        $client->request('DELETE', $url);
        self::assertResponseStatusCodeSame(401);
    }

    #[DataProvider('endpoints')]
    public function testSellerCannotDelete(string $url): void
    {
        $client = static::createClient();
        $this->authenticateSeller(false);
        $client->request('DELETE', $url, server: ['HTTP_AUTHORIZATION' => 'Bearer test-token']);
        self::assertResponseStatusCodeSame(403);
    }

    public function testDeletedAccountCannotUseExistingToken(): void
    {
        $client = static::createClient();
        $this->authenticateSeller(true);
        $client->request('GET', '/api/sellers/profile', server: ['HTTP_AUTHORIZATION' => 'Bearer test-token']);
        self::assertResponseStatusCodeSame(401);
    }

    private function authenticateSeller(bool $deleted): void
    {
        $seller = (new Seller())->setEmail('seller@example.test')->setRoles(['ROLE_SELLER']);
        if ($deleted) {
            $seller->softDelete();
        }
        $provider = $this->createMock(UserProviderInterface::class);
        $provider->method('loadUserByIdentifier')->willReturn($seller);
        $jwt = $this->createMock(JWTTokenManagerInterface::class);
        $jwt->method('parse')->willReturn(['username' => $seller->getEmail()]);
        $jwt->method('getUserIdClaim')->willReturn('username');
        static::getContainer()->set('security.user.provider.concrete.app_user_provider', $provider);
        static::getContainer()->set('lexik_jwt_authentication.jwt_manager', $jwt);
    }
}
