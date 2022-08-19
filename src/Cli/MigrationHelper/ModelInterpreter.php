<?php

namespace Neoan\Cli\MigrationHelper;

use Exception;
use Neoan\Enums\AttributeType;

class ModelInterpreter
{
    private array $declaration;

    function __construct($model)
    {
        $this->declaration = $model::declare();
    }

    /**
     * @throws Exception
     */
    public function getPrimaryField(): ?array
    {
        $primaryField = null;
        foreach ($this->declaration[$this->getTableName()] as $property) {
            $primaryField = $property['isPrimary'] ? $property : $primaryField;
        }
        if (!$primaryField) {
            throw new Exception('No Primary key set in Model!');
        }
        return $primaryField;
    }

    function getTableName(): string
    {
        return array_key_first($this->declaration);
    }

    public function isUnique(array $property): bool
    {
        foreach ($property['attributes'] as $attribute) {
            if ($attribute['name'] === "Neoan\\Model\\Attributes\\IsUnique") {
                return true;
            }
        }
        return false;
    }

    public function filteredProperties(): array
    {
        return array_filter($this->declaration[$this->getTableName()], function (array $property) {
            foreach ($property['attributes'] as $attribute) {
                if ($attribute['type'] === AttributeType::ATTACH) {
                    return false;
                }
            }
            return true;
        });
    }
}