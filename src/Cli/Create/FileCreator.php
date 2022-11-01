<?php

namespace Neoan\Cli\Create;

use Exception;
use Neoan\NeoanApp;
use Symfony\Component\Console\Output\Output;

class FileCreator
{
    public static string $template;
    public static string $folder;
    public static string $fileContent;
    public static array $path;
    public static NeoanApp $neoanApp;
    public static Output $output;

    public static function process($type, $name, $neoanApp, $output)
    {
        self::$neoanApp = $neoanApp;
        self::$output = $output;
        self::parse($type);
        self::getPath($name);
        self::$fileContent = self::getFileContent($name);
        self::readComposer();
        self::ensureDirectory();
        self::writeFile();
    }

    /**
     * @throws Exception
     */
    private static function parse($type): array
    {
        self::$template = ucfirst($type) . 'Template.txt';

        self::$folder = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR;
        if(file_exists(self::$neoanApp->cliPath . DIRECTORY_SEPARATOR . '.templates' . DIRECTORY_SEPARATOR . self::$template)) {
            self::$folder = self::$neoanApp->cliPath . DIRECTORY_SEPARATOR . '.templates' . DIRECTORY_SEPARATOR;
        }

        if (!file_exists(self::$folder . self::$template)) {
            self::$output->writeln(["No template for `{$type}`", "Try `model` or `controller`"]);
            throw new Exception('Template not found');
        }
        return [self::$folder, self::$template];
    }

    private static function getPath($name): void
    {
        self::$path = explode('\\', $name);
    }

    private static function getFileContent($name): string
    {
        $variables = [
            'namespace' => preg_replace('/\\\[a-z]+$/i', '', $name),
            'name' => end(self::$path)
        ];
        $variables['name.toLower'] = strtolower($variables['name']);
        $variables['name.toUpper'] = strtoupper($variables['name']);
        return str_replace(['{{namespace}}', '{{name}}', '{{name.toLower}}', '{{name.toUpper}}'], $variables, file_get_contents(self::$folder . self::$template));
    }

    private static function readComposer(): void
    {
        // read composer
        $composerFile = json_decode(file_get_contents(self::$neoanApp->cliPath . '/composer.json'), true);
        if (isset($composerFile['autoload']['psr-4'])) {
            foreach ($composerFile['autoload']['psr-4'] as $ns => $loadPath) {
                if ((self::$path[0] . '\\') === $ns) {
                    self::$path[0] = substr($loadPath, 0, -1);
                }
            }
        }
    }

    private static function ensureDirectory(): void
    {
        $directoryExplorer = self::$neoanApp->cliPath;
        foreach (self::$path as $i => $folder) {
            $directoryExplorer = $directoryExplorer . DIRECTORY_SEPARATOR . $folder;
            if ($i + 1 < count(self::$path) && !file_exists($directoryExplorer)) {
                mkdir($directoryExplorer);
            }
        }
    }

    private static function writeFile(): void
    {
        $filePath = implode(DIRECTORY_SEPARATOR, self::$path);
        self::$output->writeln("Generating to `$filePath`");
        file_put_contents($filePath . '.php', self::$fileContent);
    }
}