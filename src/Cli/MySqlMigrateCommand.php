<?php

namespace Neoan\Cli;

use Neoan\NeoanApp;
use Neoan\Database\Database;
use Neoan\Model\Migration\MySqlMigration;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'migrate:mysql', description: 'Syncs a model declaration to the database')]
class MySqlMigrateCommand extends Command
{
    protected static $defaultName = 'migrate:mysql';
    protected static $defaultDescription = 'Syncs a model declaration to the database';
    private string $appPath;

    public function __construct(NeoanApp $neoanApp, string $name = null)
    {
        $this->appPath = $neoanApp->appPath;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Syncs a model declaration to the database')
            ->addArgument(
                'model',
                InputArgument::REQUIRED,
                'Fully qualified model name',
                null
            )->addOption(
                'output-folder',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Output to file? You can specify the a folder.',
                false
            )->addOption(
                'with-copy',
            'c',
            InputOption::VALUE_OPTIONAL,
                'Creates a backup of the existing table with the specified name',
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $modelName = $input->getArgument('model');
        $sanitizedModelName = preg_replace('/[\/\\\]/','_', $modelName);
        if (!class_exists($modelName)) {
            $output->writeln("The requested model does not exist");
            return Command::FAILURE;
        }
        $migrate = new MySqlMigration($modelName);

        $fileOption = $input->getOption('output-folder');
        if(false !== $fileOption){

            $fileName = $sanitizedModelName . '-' . time() . '.sql';
            $directory = $this->appPath . DIRECTORY_SEPARATOR . $fileOption . DIRECTORY_SEPARATOR;
            $full = $directory .$fileName;
            $output->writeln("Writing to " . $full);

            @file_put_contents($full, $migrate->sql);
            usleep(200);
        }
        $output->writeln($migrate->sql);

        // backup?
        $backupOption = $input->getOption('with-copy');
        if(false !== $backupOption){
            try{
                Database::raw($migrate->backupSql,[]);
            } catch (\Exception $exception) {
                $output->writeln($exception->getMessage());
                $output->writeln($migrate->backupSql);
            }
        }
        // execute migration
        foreach ($migrate->sqlAsSingleCommands() as $singleCommand){
            if(trim($singleCommand) !== ''){
                try{
                    Database::raw($singleCommand,[]);
                } catch (\Exception $exception) {
                    $output->writeln($exception->getMessage());
                    $output->writeln($singleCommand);
                }
            }
        }

        return Command::SUCCESS;
    }
}