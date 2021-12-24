<?php

declare(strict_types=1);

namespace Yii\DBAL;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use LogicException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Yii\DBAL\Contracts\ConnectionManagerInterface;

use function is_string;

class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * @var array<string, array>
     */
    protected readonly array $connectionsParams;
    protected readonly ContainerInterface $container;
    protected readonly string $defaultConnection;

    /**
     * @var array<string, Connection>
     */
    private array $connections = [];
    private static bool $typesLoaded = false;

    /**
     * @phpcs:ignore
     * @param array{addTypes: array<string, class-string>|null, overrideTypes: array<string, class-string>|null} $typesConfig
     * @param array{defaultConnection: string, connections: array<string, array>} $connManagerConfig
     *
     * @throws DoctrineException
     *
     * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#configuration
     */
    public function __construct(ContainerInterface $container, array $typesConfig, array $connManagerConfig)
    {
        $this->container = $container;

        $this->defaultConnection = $connManagerConfig['defaultConnection'] ?? 'default';
        $this->connectionsParams = $connManagerConfig['connections'] ?? [];

        if (!self::$typesLoaded) {
            self::$typesLoaded = true;
            self::loadTypes($typesConfig['addTypes'] ?? [], $typesConfig['overrideTypes'] ?? []);
        }
    }

    /**
     * @throws DoctrineException
     */
    final public function getConnection(string $name = null): Connection
    {
        $name = $name ?? $this->defaultConnection;

        return $this->connections[$name] ?? ($this->connections[$name] = $this->makeConnection($name));
    }

    public function resetConnection(string $name = null): void
    {
        if ($name) {
            unset($this->connections[$name]);
        } else {
            $this->connections = [];
        }
    }

    /**
     * @throws DoctrineException
     */
    protected function makeConnection(string $name): Connection
    {
        $connParams = $this->connectionsParams[$name] ?? null;

        if (!isset($connParams)) {
            throw new InvalidArgumentException(sprintf('Connection with name "%s" was not found.', $name));
        }

        return new Connection(
            $this->makeDoctrineConnection($connParams),
            $connParams['useNamedParameters'] ?? false
        );
    }

    /**
     * @throws DoctrineException
     */
    protected function makeDoctrineConnection($connParams): DoctrineConnection
    {
        $config = $this->makeDoctrineConnectionConfiguration($connParams);
        $eventManager = $this->makeDoctrineConnectionEventManager($connParams);

        return DriverManager::getConnection($connParams['dbal'], $config, $eventManager);
    }

    /**
     * @param array<string, mixed> $connParams
     */
    protected function makeDoctrineConnectionConfiguration(array $connParams, Configuration $conf = null): Configuration
    {
        $conf = $conf ?? new Configuration();

        if ($queryLogger = $connParams['queryLogger'] ?? null) {
            if (is_string($queryLogger)) {
                $queryLogger = $this->container->get($queryLogger);
            }
            if (($queryLogger instanceof LoggerInterface)) {
                throw new LogicException('The "queryLogger" configuration parameter must contain '
                    . 'a class name or an object that supports the PSR-3 (Logger) interface.');
            }
            $conf->setMiddlewares([new Middleware($queryLogger)]);
        }

        if ($queryCache = $connParams['queryCache'] ?? null) {
            if (is_string($queryCache)) {
                $queryCache = $this->container->get($queryCache);
            }
            if ($queryCache instanceof CacheInterface) {
                $queryCache = new Psr16Adapter($queryCache);
            }
            if (!($queryCache instanceof CacheItemPoolInterface)) {
                throw new LogicException('The "queryCache" configuration parameter must contain '
                    . 'a class name or object that supports the PSR-6 or PSR-16 interface.');
            }
            $conf->setResultCache($queryCache);
        }

        return $conf;
    }

    /**
     * @param array<string, mixed> $connParams
     *
     * @noinspection PhpUnusedParameterInspection
     */
    protected function makeDoctrineConnectionEventManager(array $connParams): EventManager
    {
        return new EventManager();
    }

    /**
     * @param iterable<string, string> $addTypes
     * @param iterable<string, string> $overrideTypes
     * @throws DoctrineException
     */
    private static function loadTypes(iterable $addTypes, iterable $overrideTypes): void
    {
        foreach ($addTypes as $name => $className) {
            Type::addType($name, $className);
        }
        foreach ($overrideTypes as $name => $className) {
            Type::overrideType($name, $className);
        }
    }
}
