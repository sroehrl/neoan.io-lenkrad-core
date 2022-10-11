<?php

namespace Neoan\Cli;

use Exception;
use Neoan\Cli\MigrationHelper\ModelInterpreter;
use Neoan\Cli\MigrationHelper\MySqlMigration;
use Neoan\Cli\MigrationHelper\SqLiteMigration;
use Neoan\Database\Database;
use Neoan\Helper\ComposerParser;
use Neoan\NeoanApp;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'migrate:models', description: 'Syncs all models within a namespace with the database')]
class MigrateAllCommand extends Command
{
    protected static $defaultName = 'migrate:models';
    protected static $defaultDescription = 'Syncs all models within a namespace with the database';
    private NeoanApp $app;

    public function __construct(NeoanApp $neoanApp, string $name = null)
    {
        $this->app = $neoanApp;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Syncs a model declaration to the database')
            ->addArgument(
                'dialect',
                InputArgument::REQUIRED,
                'sqlite | mysql'
            )
            ->addArgument(
                'namespace',
                InputArgument::REQUIRED,
                'PSR4 namespace range',
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

    private function migrateOne($modelName, $input, $output)
    {
        if ($input->getArgument('dialect') === 'sqlite') {
            $migrate = new SqLiteMigration(new ModelInterpreter($modelName), $input->getOption('with-copy'));
        } else {
            $migrate = new MySqlMigration(new ModelInterpreter($modelName), $input->getOption('with-copy'));
        }
        $fileOption = $input->getOption('output-folder');
        if (false !== $fileOption) {

            $fileName = $modelName . '-' . time() . '.sql';
            $directory = $this->appPath . DIRECTORY_SEPARATOR . $fileOption . DIRECTORY_SEPARATOR;
            $full = $directory . $fileName;
            $output->writeln("Writing to " . $full);

            @file_put_contents($full, $migrate->sql);
            usleep(200);
        }
        $output->writeln("/****** Generated SQL ******/");
        $output->writeln($migrate->sql);

        // backup?
        $backupOption = $input->getOption('with-copy');
        if (false !== $backupOption) {
            try {
                Database::raw($migrate->backupSql, []);
            } catch (Exception $exception) {
                $output->writeln($exception->getMessage());
                $output->writeln($migrate->backupSql);
            }
        }
        // execute migration
        foreach ($migrate->sqlAsSingleCommands() as $singleCommand) {
            if (trim($singleCommand) !== '') {
                try {
                    Database::raw($singleCommand, []);
                } catch (Exception $exception) {
                    $output->writeln("/***** ERROR *****/");
                    $output->writeln($exception->getMessage());
                    $output->writeln("Failed command:");
                    $output->writeln(trim($singleCommand));
                    $output->writeln("aborting...");
                    $output->writeln("Check your database.");
                    return Command::FAILURE;
                }
            }
        }
        $output->writeln("/**** SUCCESS ****/");
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $namespaceName = $input->getArgument('namespace');
        $composerParser = new ComposerParser($this->app);



        foreach ($composerParser->getAutoloadNamespaces() as $ns => $path){
            if(str_starts_with($namespaceName, $ns)){
                $entryPoint = str_replace($ns, $path, $namespaceName);
                foreach (scandir($entryPoint) as $possible){
                    if(str_ends_with($possible, '.php')){
                        $this->migrateOne(substr($possible, 0, -4), $input, $output);
                    }

                }
            }
        }


        return Command::SUCCESS;
    }
}