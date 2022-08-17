<?php

namespace Neoan\Cli\Create;

use Neoan\NeoanApp;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('create:controller', 'Creates file implementing Routable')]
class CreateControllerCommand extends Command
{
    protected static $defaultName = 'create:controller';
    protected static $defaultDescription = 'create controller command';
    private NeoanApp $neoanApp;

    public function __construct(NeoanApp $neoanApp, string $name = null)
    {
        $this->neoanApp = $neoanApp;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setHelp('Creates Middleware or Route-controller via Namespace')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'fully qualified namespace'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        FileCreator::process('controller', $input->getArgument('name'), $this->neoanApp, $output);

        return Command::SUCCESS;
    }
}