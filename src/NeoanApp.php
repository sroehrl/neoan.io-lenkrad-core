<?php

namespace Neoan;

use Exception;
use Neoan\Errors\SystemError;
use Neoan\Helper\Env;
use Neoan\Helper\Setup;
use Neoan\Provider\DefaultProvider;
use Neoan\Provider\Interfaces\Provide;
use Neoan\Request\Request;
use Neoan\Routing\Route;

class NeoanApp
{
    public string $appPath;
    public string $publicPath;
    public string $webPath;
    public string $cliPath;
    public Provide $injectionProvider;
    private static NeoanApp $instance;
    private Setup $setup;

    /**
     * @throws Exception
     */
    public function __construct(Setup $setup, string $cliPath = null)
    {
        if(!$cliPath) {
            $cliPath = dirname(\Composer\Factory::getComposerFile());
        }
        Env::initialize($cliPath);
        try{
            $this->appPath = $setup->get('libraryPath');
            $this->publicPath = $setup->get('publicPath');
            $this->setup = $setup;
        } catch (Exception $e) {
            new SystemError($e->getMessage());
        }

        $this->cliPath = $cliPath;
        $this->webPath = Env::get('WEB_PATH', '/');

        if (isset($_SERVER["SERVER_PROTOCOL"])) {
            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/'))) . '://';
            if (!defined('base')) {
                define('base', $protocol . $_SERVER['HTTP_HOST'] . $this->webPath);
            }
        }
        $this->injectionProvider = new DefaultProvider();
        $this->injectionProvider->set(static::class, $this);
        $this->injectionProvider->set(Setup::class, $setup);
        self::$instance = $this;
    }

    public function setProvider(Provide $provider): void
    {
        $this->injectionProvider = $provider;
    }

    /**
     * @codeCoverageIgnore
     */
    public function run(): void
    {
        $this->invoke($this->setup);
        $this->invoke(new Request());
        $this->invoke(new Route());
    }

    public function invoke($instance): void
    {
        $instance($this);
    }
    public static function getInstance(): self
    {
        return self::$instance;
    }
    public function __invoke(): static
    {
        return $this;
    }
}