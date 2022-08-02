<?php

namespace Neoan\Model;

use Neoan\Enums\AttributeType;
use Neoan\Enums\Direction;
use Neoan\Helper\AttributeHelper;
use Neoan\Model\Attributes\IsPrimaryKey;
use ReflectionClass;
use ReflectionException;

class Interpreter
{
    private AttributeHelper $reflection;
    public array $parsedModel;
    private Model $currentModel;

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

        foreach ($this->parsedModel as $property){
            // Custom Type?
            if(!$property['isBuiltIn']){
                $this->currentModel->{$property['name']} = new $property['type']();
            }
            // has default value?
            if(isset($property['defaultValue'])){
                $this->currentModel->{$property['name']} = $property['defaultValue'];
            }
            // fill from input
            if($property['isBuiltIn'] && isset($staticModel[$property['name']])){
                try{
                    $this->currentModel->{$property['name']} = $staticModel[$property['name']];
                } catch (\TypeError $e) {
                    // some day...
                    var_dump($e->getMessage());
                }
            }
            // initialization attributes
            $this->executeAttributes($property['attributes'], $property['name'], AttributeType::INITIAL, Direction::IN);
        }
        return $this->currentModel;
    }
    public function executeAttributes(array $attributes, string $propertyName, AttributeType $type, Direction $direction): void
    {
        foreach ($attributes as $attribute){
            if($attribute['type'] === $type){
                $interim = $attribute['instance']($this->currentModel->toArray(), $direction, $propertyName);
                $this->currentModel->{$propertyName} = $interim[$propertyName];
            }
        }
    }
    public function getTableName(): string
    {
        if($this->reflection->findConstant('tableName')) {
            return $this->reflection->findConstant('tableName');
        } else {
            preg_match('/[a-z]+$/i',$this->reflection->className, $from);
            return lcfirst($from[0]);
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
        $selectorString = '';
        $attachable = [];
        $mutatable = [];
        foreach ($this->reflection->properties as $i => $property) {
            $attributes = $property->getAttributes();
            if(empty($attributes)){
                $this->addToSelectorString($selectorString, $property->getName());
            } else {
                foreach ($attributes as $attribute){
                    $attributeInstance = $attribute->newInstance();
                    switch($attributeInstance->type) {
                        case AttributeType::ATTACH:
                            $attachable[$property->getName()] = $attributeInstance;
                            break;

                        case AttributeType::MUTATE:
                            $this->addToSelectorString($selectorString, $property->getName());
                            $mutatable[$property->getName()] = $attributeInstance;
                            break;
                        case AttributeType::DECLARE:
                        default:
                        $this->addToSelectorString($selectorString, $property->getName());
                            break;
                    }


                }

            }
        }
        return [
            'selectorString' => $selectorString,
            'attachable' => $attachable,
            'mutatable' => $mutatable
        ];
    }
    public function getPrimaryKey() :string
    {
        return $this->reflection->findPropertiesByAttribute(IsPrimaryKey::class)[0] ?? 'id';
    }
    private function addToSelectorString(&$selectorString, $columnName): void
    {
        $selectorString .= (strlen($selectorString) > 1 ? ' ' : ''). $this->getTableName() . '.' . $columnName;
    }

}