<?php


use App\DataFixtures\CustomerFixtures;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;

uses(WebTestCase::class)->beforeAll(fn () => self::ensureKernelShutdown())->in('Functional');

function createClientAnonymous(WebTestCase $context, bool $reuse = false): KernelBrowser
{
    static $client = null;

    if ($reuse && null !== $client) {
        return $client;
    }

    $reflection = new ReflectionMethod($context, 'createClient');
    $reflection->setAccessible(true);
    $client = $reflection->invoke($context);
    $client->insulate();

    return $client;
}

function getTokenForRole(KernelBrowser $client, string $role): string
{
    $email = match ($role) {
        'ROLE_ADMIN' => CustomerFixtures::CUSTOMER_ADMIN_EMAIL,
        'ROLE_USER' => CustomerFixtures::CUSTOMER_USER_EMAIL,
        default => throw new InvalidArgumentException(sprintf('Unknown role "%s"', $role)),
    };

    $client->jsonRequest('POST', '/auth', ['email' => $email, 'password' => CustomerFixtures::CUSTOMER_PASSWORD]);

    $response = $client->getResponse();
    if (200 !== $response->getStatusCode()) {
        throw new RuntimeException(sprintf('Failed to get token for role "%s": %s', $role, $response->getContent()));
    }

    $data = json_decode($response->getContent(), true);

    return $data['token'] ?? throw new RuntimeException('Token not found in response');
}

function resetDatabase(): void
{
    $kernel = new Kernel('test', true);
    $kernel->boot();

    $application = new Application($kernel);
    $application->setAutoExit(false);

    $commands = [
        ['command' => 'doctrine:database:drop', '--if-exists' => true, '--force' => true, '--env' => 'test'],
        ['command' => 'doctrine:database:create', '--env' => 'test'],
        ['command' => 'doctrine:schema:update', '--force' => true, '--env' => 'test'],
        ['command' => 'doctrine:fixtures:load', '--no-interaction' => true, '--env' => 'test'],
    ];

    foreach ($commands as $command) {
        $application->run(new ArrayInput($command));
    }

    $kernel->shutdown();
}
