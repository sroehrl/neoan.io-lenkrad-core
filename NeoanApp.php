<?php

namespace Neoan;

use Neoan\Helper\Env;
use Neoan\Request\Request;
use Neoan\Routing\Route;

class NeoanApp
{
    public string $appPath;
    public string $publicPath;

    public function __construct(string $appPath, string $publicPath)
    {
        Env::initialize($appPath);
        $this->appPath = $appPath;
        $this->publicPath = $publicPath;
        if(isset($_SERVER["SERVER_PROTOCOL"])){
            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
            define('base',$protocol . $_SERVER['HTTP_HOST']);
        }


    }
    public function invoke($instance): void
    {
        $instance($this);
    }
    public function run(): void
    {
        $this->invoke(new Request());
        $this->invoke(new Route());
    }
}