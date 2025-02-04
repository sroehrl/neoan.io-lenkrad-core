<?php

namespace Neoan\Cli;

use Exception;
use Neoan\Cli\MigrationHelper\ModelInterpreter;
use Neoan\Cli\MigrationHelper\MySqlMigration;
use Neoan\Cli\MigrationHelper\SqLiteMigration;
use Neoan\Database\Database;
use Neoan\Helper\ComposerParser;
use Neoan\NeoanApp;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'migrate:models', description: 'Syncs all models with the database')]
class MigrateAllCommand extends Command
{
    protected static $defaultName = 'migrate:models';
    protected static $defaultDescription = 'Syncs all models with the database';
    private NeoanApp $app;

    private InputInterface $input;
    private OutputInterface $output;

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
            ->addOption(
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

    private function migrateOne($modelName): void
    {
        if ($this->input->getArgument('dialect') === 'sqlite') {
            $migrate = new SqLiteMigration(new ModelInterpreter($modelName), $this->input->getOption('with-copy'));
        } else {
            $migrate = new MySqlMigration(new ModelInterpreter($modelName), $this->input->getOption('with-copy'));
        }
        $fileOption = $this->input->getOption('output-folder');
        if (false !== $fileOption) {

            $fileName = $modelName . '-' . time() . '.sql';
            $directory = $this->app->appPath . DIRECTORY_SEPARATOR . $fileOption . DIRECTORY_SEPARATOR;
            $full = $directory . $fileName;
            $this->output->writeln("Writing to " . $full);

            @file_put_contents($full, $migrate->sql);
            usleep(200);
        }
        $this->output->writeln("/****** Generated SQL ******/");
        $this->output->writeln($migrate->sql);

        // backup?
        $backupOption = $this->input->getOption('with-copy');
        if (false !== $backupOption) {
            try {
                Database::raw($migrate->backupSql, []);
            } catch (Exception $exception) {
                $this->output->writeln($exception->getMessage());
                $this->output->writeln($migrate->backupSql);
            }
        }
        // execute migration
        foreach ($migrate->sqlAsSingleCommands() as $singleCommand) {
            if (trim($singleCommand) !== '') {
                try {
                    Database::raw($singleCommand, []);
                } catch (Exception $exception) {
                    $this->output->writeln("/***** ERROR *****/");
                    $this->output->writeln($exception->getMessage());
                    $this->output->writeln("Failed command:");
                    $this->output->writeln(trim($singleCommand));
                    $this->output->writeln("aborting...");
                    $this->output->writeln("Check your database.");
                    return;
                }
            }
        }
        $this->output->writeln("/**** SUCCESS ****/");
    }


    private function directoryExplorer(string $startingPoint, string $nameSpace): void
    {
        foreach (scandir($startingPoint) as $fileOrFolder) {
            if (str_ends_with($fileOrFolder, '.php')) {
                try {
                    $potentialRoutable = $nameSpace . mb_substr($fileOrFolder, 0, -4);
                    $this->checkAndMigrate($potentialRoutable);
                } catch (\ReflectionException $e) {
                    // when file doesn't even contain a class, this will gracefully fail
                    // Inform event?
                }

            } elseif (is_dir($startingPoint . $fileOrFolder) && $fileOrFolder != '.' && $fileOrFolder != '..') {
                $newNamespace = str_replace('\\\\', '\\', $nameSpace . '\\' . $fileOrFolder . '\\');
                $this->directoryExplorer($startingPoint . $fileOrFolder . DIRECTORY_SEPARATOR, $newNamespace);
            }
        }
    }

    private function checkAndMigrate(string $namespace)
    {
        try {
            $reflection = new ReflectionClass($namespace);
            if ($reflection->getParentClass() && $reflection->getParentClass()->getName() === 'Neoan\\Model\\Model') {
                $this->migrateOne($namespace);
            }

        } catch (Exception $e) {

        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $this->input = $input;
        $this->output = $output;
        $composerParser = new ComposerParser($this->app);

        foreach ($composerParser->getAutoloadNamespaces() as $ns => $path) {
            $this->directoryExplorer($this->app->cliPath . '/'. $path, $ns);
        }


        return Command::SUCCESS;
    }
}