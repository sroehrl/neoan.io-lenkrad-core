<?php

namespace Neoan\Helper;

use Neoan\Enums\AttributeType;
use Neoan\Model\Attributes\IsPrimaryKey;
use ReflectionClass;
use ReflectionException;

class AttributeHelper
{
    public ReflectionClass $reflection;
    public array $constants = [];
    public array $properties = [];
    public string $className;
    public array $attributeMatchList = [];
    public array $propertyMatchList = [];
    private array $parsedClass = [];
    /**
     * @throws ReflectionException
     */
    public function __construct(string $classIdentifier)
    {
        $this->reflection = new ReflectionClass($classIdentifier);
        $this->constants = $this->reflection->getConstants();
        $this->properties = $this->reflection->getProperties();
        $this->className = $this->reflection->getName();
        $this->generateMachLists();
    }
    public function findConstant(string $constant): ?string
    {
        return $this->constants[$constant] ?? null;
    }
    public function findPropertiesByAttribute(string $attribute)
    {
        return $this->attributeMatchList[$attribute] ?? null;
    }
    public function findAttributesByProperty(string $property): array
    {
        return $this->propertyMatchList[$property];
    }
    private function generateMachLists() :void
    {
        foreach ($this->properties as $property){
            $attributes = $property->getAttributes();
            $this->propertyMatchList[$property->getName()] = $attributes;
            foreach ($attributes as $attribute){
                $this->attributeMatchList[$attribute->getName()][] = $property->getName();

            }
        }
    }
    public function parseClass(): array
    {
        $this->parsedClass[] = [];
        foreach ($this->properties as $i => $property){
            $attributes = $this->propertyMatchList[$property->getName()];
            $attributeList = [];
            $ignore = false;
            foreach ($attributes as $attribute){
                $instance = $attribute->newInstance();
                $attributeList[] = [
                    'name' => $attribute->getName(),
                    'instance' => $instance,
                    'type' => $instance->getType() ?? null,
                    'reflection' => $attribute
                ];
                $ignore = $instance->getType() === AttributeType::PRIVATE;
            }
            if(!$ignore){
                $this->parsedClass[$i] = [
                    'name' => $property->getName(),
                    'type' => $property->getType()->getName(),
                    'isBuiltIn' => $property->getType()->isBuiltin(),
                    'isReadOnly' => $property->isReadOnly(),
                    'isWritable' => $property->isPublic() && !$property->isReadOnly(),
                    'nullable' => $property->getType()->allowsNull(),
                    'isPrimary' => !empty($property->getAttributes(IsPrimaryKey::class)),
                    'attributes' => $attributeList,
                ];
                if($property->hasDefaultValue()){
                    $this->parsedClass[$i]['defaultValue'] = $property->getDefaultValue();
                }
            }


        }
        return $this->parsedClass;
    }

}