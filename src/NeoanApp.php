<?php

namespace Neoan;

use Neoan\Helper\Env;
use Neoan\Request\Request;
use Neoan\Routing\Route;

class NeoanApp
{
    public string $appPath;
    public string $publicPath;
    public string $webPath;
    public string $cliPath;
    private static NeoanApp $instance;

    public function __construct(string $appPath, string $publicPath, string $cliPath = null)
    {
        if(!$cliPath) {
            $cliPath = dirname(\Composer\Factory::getComposerFile());
        }
        Env::initialize($cliPath);
        $this->appPath = $appPath;
        $this->publicPath = $publicPath;
        $this->cliPath = $cliPath;
        $this->webPath = Env::get('WEB_PATH', '/');
        if (isset($_SERVER["SERVER_PROTOCOL"])) {
            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/'))) . '://';
            if (!defined('base')) {
                define('base', $protocol . $_SERVER['HTTP_HOST'] . $this->webPath);
            }
        }
        self::$instance = $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function run(): void
    {
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
}