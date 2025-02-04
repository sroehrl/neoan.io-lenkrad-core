<?php

namespace Neoan\Helper;


use Exception;
use Neoan\Database\Adapter;
use Neoan\Database\Database;
use Neoan\Enums\ResponseOutput;
use Neoan\Errors\NotFound;
use Neoan\Errors\SystemError;
use Neoan\Render\Renderer;
use Neoan\Response\Response;

class Setup
{

    private array $configuration = [
        'webPath' => '/',
    ];

    public function __construct()
    {
        return $this;
    }

    /**
     * @param string $templatePath
     * @return Setup
     */
    public function setTemplatePath(string $templatePath): self
    {
        $this->configuration['templatePath'] = $templatePath;
        return $this;
    }

    /**
     * @param string $default404
     * @return Setup
     */
    public function setDefault404(string $default404): self
    {
        $this->configuration['default404'] = $default404;
        NotFound::setTemplate($default404);
        return $this;
    }

    /**
     * @param string $default500
     * @return Setup
     */
    public function setDefault500(string $default500): self
    {
        $this->configuration['default500'] = $default500;
        SystemError::setTemplate($default500);
        return $this;
    }

    /**
     * @param bool $useSkeleton
     * @return Setup
     */
    public function setUseSkeleton(bool $useSkeleton): self
    {
        $this->configuration['useSkeleton'] = $useSkeleton;
        return $this;
    }

    /**
     * @param string $skeletonHTML
     * @return Setup
     */
    public function setSkeletonHTML(string $skeletonHTML): self
    {
        $this->configuration['skeletonHTML'] = $skeletonHTML;
        return $this;
    }

    /**
     * @param string $skeletonComponentPlacement
     * @return Setup
     */
    public function setSkeletonComponentPlacement(string $skeletonComponentPlacement): self
    {
        $this->configuration['skeletonComponentPlacement'] = $skeletonComponentPlacement;
        return $this;
    }

    /**
     * @param array $skeletonVariables
     * @return Setup
     */
    public function setSkeletonVariables(array $skeletonVariables): self
    {
        $this->configuration['skeletonVariables'] = $skeletonVariables;
        return $this;
    }

    public function setDefaultOutput(ResponseOutput $output): static
    {
        $this->configuration['defaultOutput'] = $output;
        return $this;
    }

    public function setDatabaseAdapter(Adapter $adapter): self
    {
        Database::connect($adapter);
        $instance = new \ReflectionClass($adapter);
        $this->configuration['databaseAdapter'] = $instance->getName();
        return $this;
    }

    public function setLibraryPath(string $path): self
    {
        $this->configuration['libraryPath'] = $path;
        return $this;
    }

    public function setPublicPath(string $path): self
    {
        $this->configuration['publicPath'] = $path;
        return $this;
    }

    public function setWebPath(string $path): self
    {
        $this->configuration['webPath'] = $path;
        return $this;
    }

    public function setLogFile(string $path): self
    {
        $this->configuration['logFile'] = $path;
        return $this;
    }

    public function set(string $key, mixed $value):self
    {
        $this->configuration[$key] = $value;
        return $this;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @throws Exception
     */
    public function get(string $key): mixed
    {
        if(!array_key_exists($key, $this->configuration)) {
            throw new Exception('Missing setup key "' . $key . '"!');
        }
        return $this->configuration[$key];
    }

    public function __invoke(): static
    {
        // templating
        if(isset($this->configuration['templatePath'])) {
            Renderer::setTemplatePath($this->configuration['templatePath']);
        }
        if(isset($this->configuration['defaultOutput'])) {
            Response::setDefaultOutput($this->configuration['defaultOutput']);
        }
        if(isset($this->configuration['useSkeleton']) && $this->configuration['useSkeleton']) {
            Renderer::setHtmlSkeleton(
                $this->configuration['skeletonHTML'],
                $this->configuration['skeletonComponentPlacement'],
                $this->configuration['skeletonVariables']
            );
        }
        return $this;

    }

}