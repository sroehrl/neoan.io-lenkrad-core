<?php

namespace Neoan\Cli\Create;

use Neoan\NeoanApp;
use Neoan3\Apps\Template;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('create', 'File-creator command')]
class CreateCommand extends Command
{
    protected static $defaultName = 'create';
    protected static $defaultDescription = 'File-creator command';
    private string $appPath;

    public function __construct(NeoanApp $neoanApp, string $name = null)
    {
        $this->appPath = $neoanApp->appPath;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setHelp('Creates Models and Controllers via Namespace')
            ->addArgument(
                'type',
                InputArgument::REQUIRED,
                'model | controller',
                null
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'fully qualified namespace'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $folder = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR;
        $template = ucfirst($input->getArgument('type')) . 'Template.txt';
        if (!file_exists($folder . $template)) {
            $output->writeln(["No template for `{$input->getArgument('type')}`", "Try `model` or `controller`"]);
            return Command::FAILURE;
        }
        // find location
        $path = explode('\\', $input->getArgument('name'));

        $result = Template::embrace(file_get_contents($folder . $template), [
            'namespace' => preg_replace('/\\\[a-z]+$/i', '', $input->getArgument('name')),
            'name' => end($path)
        ]);

        // read composer
        $composerFile = json_decode(file_get_contents($this->appPath . '/composer.json'), true);
        if (isset($composerFile['autoload']['psr-4'])) {
            foreach ($composerFile['autoload']['psr-4'] as $ns => $loadPath) {
                if (($path[0] . '\\') === $ns) {
                    $path[0] = substr($loadPath, 0, -1);
                }
            }
        }

        $directoryExplorer = $this->appPath;
        foreach ($path as $i => $folder) {
            $directoryExplorer = $directoryExplorer . DIRECTORY_SEPARATOR . $folder;
            if ($i + 1 < count($path) && !file_exists($directoryExplorer)) {
                mkdir($directoryExplorer);
            }
        }

        $filePath = implode(DIRECTORY_SEPARATOR, $path);
        $output->writeln("Generating to `$filePath`");
        file_put_contents($filePath . '.php', $result);

        return Command::SUCCESS;
    }
}