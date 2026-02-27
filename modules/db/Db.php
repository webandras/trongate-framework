<?php

declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Database abstraction class for handling database operations using PDO.
 * Provides methods for querying, inserting, updating, deleting, and managing database records.
 * Supports connection management, parameter binding, batch operations, and table validation.
 *
 * Security features include prepared statements and environment-aware error handling.
 */
final class Db extends Trongate
{
    private string $host;

    private int $port;

    private string $user;

    private string $pass;

    private string $dbname;

    private string $charset;

    private bool $is_dev_mode;

    public Connection $conn;

    public QueryBuilder $query;

    /**
     * Initialize database connection
     *
     * Establishes a PDO connection to the MySQL database using configuration from
     * config/database.php. Supports multiple database groups and provides environment-aware
     * error handling (detailed errors in development, generic errors in production).
     *
     * IMPORTANT: Framework passes module_name as first parameter for proper integration
     * with the Trongate module system.
     *
     * @param string|null $module_name The module name (passed by framework for integration)
     * @param string|null $db_group Database group name from config/database.php (defaults to 'default')
     *
     * @throws Exception If database group is not configured
     * @throws Exception If database connection fails
     *
     * Examples:
     * $db = new Db('users');                    // Framework instantiation with module name
     * $db = new Db('users', 'analytics');       // Framework instantiation with custom db group
     * $db = new Db();                           // Direct instantiation, uses 'default' group
     *
     * Configuration example (config/database.php):
     * $databases['default'] = [
     *     'host' => 'localhost',
     *     'port' => '3306',
     *     'user' => 'root',
     *     'password' => 'secret',
     *     'database' => 'myapp'
     * ];
     */
    public function __construct(?string $module_name = null, ?string $db_group = null)
    {
        // Call parent constructor first - REQUIRED by framework!
        parent::__construct($module_name);

        // Block all direct URL access to this module
        block_url('db');

        // Determine environment mode
        // @phpstan-ignore-next-line
        $this->is_dev_mode = defined('ENV') && strtolower(ENV) === 'dev';

        // Default to 'default' group if none specified
        $db_group = $db_group ?? 'default';

        if (!isset($GLOBALS['databases'][$db_group])) {
            if ($this->is_dev_mode) {
                throw new Exception("Database group '$db_group' is not configured in /config/database.php");
            }

            throw new Exception('Configuration error.');
        }

        /** @var array{host:string, port:int|null, user:string, password:string, database:string, charset:string|null} $config */
        $config = $GLOBALS['databases'][$db_group];

        $this->host = $config['host'];
        $this->port = $config['port'] ?? 3306;
        $this->user = $config['user'];
        $this->pass = $config['password'];
        $this->dbname = $config['database'];
        $this->charset = $config['charset'] ?? 'utf8';

        // If database name is empty, return without connecting
        if ($this->dbname === '') {
            return;
        }

        try {
            $this->conn = DriverManager::getConnection([
                'driver' => 'pdo_mysql',
                'host' => $this->host,
                'port' => $this->port,
                'dbname' => $this->dbname,
                'user' => $this->user,
                'password' => $this->pass,
                'charset' => $this->charset,
            ]);

            $this->query = $this->conn->createQueryBuilder();

        } catch (PDOException $e) {
            if ($this->is_dev_mode) {
                throw new Exception('Database connection failed: ' . $e->getMessage(), $e->getCode(), $e);
            }

            throw new Exception('Service unavailable.', $e->getCode(), $e);
        }
    }
}
