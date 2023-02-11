<?php

namespace Neoan\Cli\Create;

use Neoan\NeoanApp;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('create:request', 'Creates file extending RouteGuard')]
class CreateGuardCommand extends Command
{
    protected static $defaultName = 'create:request';
    protected static $defaultDescription = 'Creates file extending RouteGuard';
    private NeoanApp $neoanApp;

    public function __construct(NeoanApp $neoanApp, string $name = null)
    {
        $this->neoanApp = $neoanApp;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setHelp('Creates class extending RouteGuard via Namespace')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'fully qualified namespace'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        FileCreator::process('request', $input->getArgument('name'), $this->neoanApp, $output);

        return Command::SUCCESS;
    }
}