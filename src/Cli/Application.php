<?php

namespace Neoan\Cli;

use Neoan\Cli\Create\CreateControllerCommand;
use Neoan\Cli\Create\CreateModelCommand;
use Neoan\NeoanApp;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;

class Application
{
    private SymfonyApplication $app;

    function __construct(NeoanApp $app)
    {
        $this->app = new SymfonyApplication();
        $this->app->add(new MigrateCommand($app));
        $this->app->add(new MigrateAllCommand($app));
        $this->app->add(new CreateControllerCommand($app));
        $this->app->add(new CreateModelCommand($app));
    }

    function add(Command $instance): SymfonyApplication
    {
        $this->app->add($instance);
        return $this->app;
    }

    function run(): void
    {
        $this->app->run();
    }
}