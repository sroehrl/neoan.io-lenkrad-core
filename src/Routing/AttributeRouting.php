<?php

namespace Neoan\Routing;

use Neoan\Helper\ComposerParser;
use Neoan\NeoanApp;
use ReflectionClass;
use ReflectionException;

class AttributeRouting
{

    public array $routingClasses = [];

    public function __construct(private readonly string $searchableNamespace)
    {

    }

    public function __invoke(NeoanApp $neoanApp): void
    {
        $composer = new ComposerParser($neoanApp);
        $autoloader = $composer->getAutoloadNamespaces();
        $nameSpaceParts = explode('\\', $this->searchableNamespace);
        $searchable = '';
        foreach($nameSpaceParts as $part) {
            $searchable .= $part . '\\';
            foreach ($autoloader as $name => $path) {
                if ($name === $searchable) {
                    $directoryPath = $neoanApp->cliPath . '/' . $path;
                    $this->directoryExplorer($directoryPath, $name);
                }
            }
        }


    }

    private function directoryExplorer(string $startingPoint, string $nameSpace): void
    {
        foreach (scandir($startingPoint) as $fileOrFolder) {
            if (str_ends_with($fileOrFolder, '.php')) {
                try {
                    $potentialRoutable = $nameSpace . mb_substr($fileOrFolder, 0, -4);
                    $this->reflectUpon($potentialRoutable);
                    $this->routingClasses[] = $potentialRoutable;
                } catch (ReflectionException $e) {
                    // when file doesn't even contain a class, this will gracefully fail
                    // Inform event?
                }

            } elseif (is_dir($startingPoint . $fileOrFolder) && $fileOrFolder != '.' && $fileOrFolder != '..') {
                $newNamespace = str_replace('\\\\', '\\', $nameSpace . '\\' . $fileOrFolder . '\\');
                $this->directoryExplorer($startingPoint . $fileOrFolder, $newNamespace);
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    private function reflectUpon(string $namespace): void
    {
        $reflection = new ReflectionClass($namespace);
        if ($reflection->implementsInterface(Routable::class)) {
            foreach ($reflection->getAttributes() as $attribute) {
                $routable = $attribute->newInstance();
                $routable->setControllerClass($reflection->getName());
                $routable->generateRoute();
            }
        }
    }

}