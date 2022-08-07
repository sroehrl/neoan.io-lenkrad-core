<?php

namespace Neoan\Model\Attributes;
use Attribute;
use Neoan\Enums\AttributeType;
use Neoan\Model\Interfaces\ModelAttribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class HasMany implements ModelAttribute
{
    public string $modelClass;
    public array $matching;

    public function __construct(string $modelClass, array $matching = [])
    {
        $this->modelClass = $modelClass;
        $this->matching = $matching;
    }
    function __invoke($primaryValue, $primaryKey)
    {
        $matching = [];
        foreach($this->matching as $key => $value){
            if($value === $primaryKey) {
                $matching[$key] = $primaryValue;
            } else {
                $matching[$key] = $value;
            }
        }

        return $this->modelClass::retrieve($matching);

    }

    public function getType(): AttributeType
    {
        return AttributeType::ATTACH;
    }
}