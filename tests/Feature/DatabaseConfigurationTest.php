<?php

use Pdo\Pgsql;

function databaseConfigurationWith(array $values): array
{
    $originalValues = [];
    foreach ($values as $key => $value) {
        $originalValues[$key] = [$_ENV[$key] ?? null, $_SERVER[$key] ?? null, getenv($key)];
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key.'='.$value);
    }

    try {
        return require base_path('config/database.php');
    } finally {
        foreach ($originalValues as $key => [$envValue, $serverValue, $processValue]) {
            if ($envValue === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $envValue;
            }
            if ($serverValue === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $serverValue;
            }
            putenv($processValue === false ? $key : $key.'='.$processValue);
        }
    }
}

it('reads Neon URLs with decoded credentials and explicit verified TLS', function (): void {
    $configuration = databaseConfigurationWith([
        'DATABASE_URL' => 'postgresql://portfolio:Example%3ASecret%40Only@ep-example-pooler.aws.neon.tech/neondb?sslmode=require&sslrootcert=untrusted.pem',
        'DB_URL' => '', 'DB_SEARCH_PATH' => 'rewortha', 'DB_SSLMODE' => 'verify-full',
        'DB_SSLROOTCERT' => '/trusted/ca-bundle.pem',
    ]);
    expect($configuration['connections']['pgsql'])->toMatchArray([
        'driver' => 'pgsql', 'host' => 'ep-example-pooler.aws.neon.tech', 'database' => 'neondb',
        'username' => 'portfolio', 'password' => 'Example:Secret@Only', 'search_path' => 'rewortha',
        'sslmode' => 'verify-full', 'sslrootcert' => '/trusted/ca-bundle.pem',
    ])->and($configuration['default'])->toBe('sqlite')
        ->and($configuration['connections']['sqlite']['url'])->toBe('')
        ->and($configuration['connections']['pgsql']['options'][Pgsql::ATTR_DISABLE_PREPARES])->toBeTrue();
});

it('allows an explicit direct URL for migrations without replacing the pooled URL', function (): void {
    $configuration = databaseConfigurationWith([
        'DATABASE_URL' => 'postgresql://portfolio:ExampleSecret@ep-example-pooler.aws.neon.tech/neondb',
        'DB_URL' => 'postgresql://portfolio:ExampleSecret@ep-example.aws.neon.tech/neondb',
        'DB_SSLMODE' => 'verify-full', 'DB_SSLROOTCERT' => '/trusted/ca-bundle.pem',
    ]);
    expect($configuration['connections']['pgsql']['host'])->toBe('ep-example.aws.neon.tech');
});

it('preserves individual PostgreSQL connection settings when no URL is supplied', function (): void {
    $configuration = databaseConfigurationWith([
        'DATABASE_URL' => '', 'DB_URL' => '', 'DB_HOST' => 'legacy-postgres.example.test',
        'DB_DATABASE' => 'portfolio', 'DB_USERNAME' => 'student', 'DB_PASSWORD' => 'ExampleSecret',
        'DB_SSLMODE' => 'verify-full', 'DB_SSLROOTCERT' => '/trusted/legacy-ca.pem',
    ]);
    expect($configuration['connections']['pgsql'])->toMatchArray([
        'host' => 'legacy-postgres.example.test', 'database' => 'portfolio',
        'username' => 'student', 'password' => 'ExampleSecret', 'sslmode' => 'verify-full',
    ]);
});
