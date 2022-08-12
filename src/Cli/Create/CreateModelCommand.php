<?php

namespace Neoan\Cli\Create;

use Neoan\NeoanApp;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('create:model', 'Creates model file')]
class CreateModelCommand extends Command
{
    protected static $defaultName = 'create:model';
    protected static $defaultDescription = 'create model command';
    private NeoanApp $neoanApp;

    public function __construct(NeoanApp $neoanApp, string $name = null)
    {
        $this->neoanApp = $neoanApp;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setHelp('Creates Models via Namespace')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'fully qualified namespace'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        FileCreator::process('model', $input->getArgument('name'), $this->neoanApp, $output);

        return Command::SUCCESS;
    }
}