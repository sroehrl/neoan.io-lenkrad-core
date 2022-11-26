<?php

namespace Neoan\Helper;


use Neoan\Database\Adapter;
use Neoan\Database\Database;

class Setup
{

    private array $configuration = [];

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
        return $this;
    }

    /**
     * @param string $default500
     * @return Setup
     */
    public function setDefault500(string $default500): self
    {
        $this->configuration['default500'] = $default500;
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

    public function setDatabaseAdapter(Adapter $adapter): self
    {
        Database::connect($adapter);
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
    public function get(string $key): mixed
    {
        return $this->configuration[$key] ?? null;
    }

}