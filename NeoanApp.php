<?php

namespace Neoan;

use Neoan\Helper\Env;
use Neoan\Request\Request;
use Neoan\Routing\Route;
use Neoan3\Apps\Template;

class NeoanApp
{
    public string $appPath;
    public string $publicPath;
    public string $webPath;

    public function __construct(string $appPath, string $publicPath)
    {
        Env::initialize($appPath);
        $this->appPath = $appPath;
        $this->publicPath = $publicPath;
        $this->webPath = $this->findWebPath();
        if(isset($_SERVER["SERVER_PROTOCOL"])){
            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
            define('base',$protocol . $_SERVER['HTTP_HOST']);
        }


    }
    public function invoke($instance): void
    {
        $instance($this);
    }
    private function normalizePath(string $path):string
    {
        return str_replace(DIRECTORY_SEPARATOR,'/',$path);
    }
    private function findWebPath(): string
    {
        $appPathParts = explode('/', $this->normalizePath($this->appPath));
        $publicPathParts = explode('/', $this->normalizePath($this->publicPath));
        foreach ($appPathParts as $i => $appPathPart) {
            if($publicPathParts[$i] === $appPathPart){
                unset($publicPathParts[$i]);
            }
        }
        return '/' . implode('/', $publicPathParts);

    }
    public function run(): void
    {
        $this->invoke(new Request());
        $this->invoke(new Route());
    }
}