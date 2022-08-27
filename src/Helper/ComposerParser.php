<?php

namespace Neoan\Helper;

use Neoan\NeoanApp;

class ComposerParser
{
    private NeoanApp $app;
    private array $composerData;
    public function __construct(NeoanApp $app)
    {
        $this->app = $app;
        $composerFile = file_get_contents($app->cliPath . '/composer.json');
        $this->composerData = json_decode($composerFile, true);
    }
    public function getAutoloadNamespaces(): array
    {
        $all = [];
        if(isset($this->composerData['autoload']['psr-4'])){
            $all = [...$this->composerData['autoload']['psr-4']];
        }
        if(isset($this->composerData['autoload-dev']['psr-4'])){
            $all = [...$all, ...$this->composerData['autoload-dev']['psr-4']];
        }
        return $all;
    }
}