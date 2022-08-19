<?php

namespace Neoan\Model;

use Neoan\Enums\AttributeType;
use Neoan\Enums\Direction;
use Neoan\Helper\AttributeHelper;
use Neoan\Model\Attributes\IsPrimaryKey;
use ReflectionException;
use ReflectionProperty;
use TypeError;

class Interpreter
{
    public array $parsedModel;
    private AttributeHelper $reflection;
    private Model $currentModel;
    private array $mutationAttributes = [];
    private array $attachableAttributes = [];
    private string $selectorString = '';

    /**
     * @throws ReflectionException
     */
    public function __construct(string $currentModelClass)
    {
        $this->reflection = new AttributeHelper($currentModelClass);
        $this->parsedModel = $this->reflection->parseClass();
    }

    public function asInstance(Model $currentModel): void
    {
        $this->currentModel = $currentModel;
    }

    public function initialize(array $staticModel = []): Model
    {
        foreach ($this->parsedModel as $property) {


            // Custom Type?
            if (!$property['isBuiltIn']) {
                $this->currentModel->{$property['name']} = new $property['type']();
            }
            // has default value?
            if (isset($property['defaultValue'])) {
                $this->currentModel->{$property['name']} = $property['defaultValue'];
            }
            // fill from input
            if ($property['isBuiltIn'] && isset($staticModel[$property['name']])) {
                $this->fillWithReadOnlyGuard($property, $property['isReadOnly'], $staticModel[$property['name']]);
            }


            // initialization attributes
            $this->executeAttributes($property['attributes'], $property['name'], AttributeType::INITIAL, Direction::IN);
        }
        return $this->currentModel;
    }

    public function fillWithReadOnlyGuard(array $property, bool $readOnly, string $value): void
    {
        if ($readOnly && !isset($this->currentModel->{$property['name']})) {
            $this->currentModel->set($property['name'], $value);
        } elseif (!$readOnly) {
            try {
                $this->currentModel->{$property['name']} = $value;
            } catch (TypeError $e) {
                // some day...
                var_dump($e->getMessage());
            }
        }
    }

    public function executeAttributes(array $attributes, string $propertyName, AttributeType $type, Direction $direction): void
    {
        foreach ($attributes as $attribute) {
            if ($attribute['type'] === $type) {
                $interim = $attribute['instance']($this->currentModel->toArray(), $direction, $propertyName);
                $this->currentModel->{$propertyName} = $interim[$propertyName];
            }
        }
    }

    public function generateInsertUpdate(Model $model): array
    {
        $this->currentModel = $model;
        foreach ($this->parsedModel as $property) {
            $this->executeAttributes($property['attributes'], $property['name'], AttributeType::MUTATE, Direction::IN);
        }
        return $this->currentModel->toArray(true);
    }

    public function generateSelect(): array
    {
        $this->selectorString = '';
        $this->mutationAttributes = [];
        foreach ($this->reflection->properties as $i => $property) {
            $attributes = $property->getAttributes();
            if (empty($attributes)) {
                $this->addToSelectorString($property->getName());
            } else {
                foreach ($attributes as $attribute) {
                    $this->attributeCheck($attribute->newInstance(), $property);
                }
            }
        }
        return [
            'selectorString' => $this->selectorString,
            'attachable' => $this->attachableAttributes,
            'mutatable' => $this->mutationAttributes
        ];
    }

    private function addToSelectorString($columnName): void
    {
        $this->selectorString .= (strlen($this->selectorString) > 1 ? ' ' : '') . $this->getTableName() . '.' . $columnName;
    }

    public function getTableName(): string
    {
        if ($this->reflection->findConstant('tableName')) {
            return $this->reflection->findConstant('tableName');
        } else {
            preg_match('/[a-z]+$/i', $this->reflection->className, $from);
            return lcfirst($from[0]);
        }
    }

    private function attributeCheck($attributeInstance, ReflectionProperty $property): void
    {
        switch ($attributeInstance->getType()) {
            case AttributeType::ATTACH:
                $this->attachableAttributes[$property->getName()] = $attributeInstance;
                break;

            case AttributeType::MUTATE:
                $this->addToSelectorString($property->getName());
                $this->mutationAttributes[$property->getName()] = $attributeInstance;
                break;
            case AttributeType::PRIVATE:
                break;
            case AttributeType::DECLARE:
            default:
                $this->addToSelectorString($property->getName());
                break;
        }
    }

    public function getPrimaryKey(): string
    {
        return $this->reflection->findPropertiesByAttribute(IsPrimaryKey::class)[0] ?? 'id';
    }

}