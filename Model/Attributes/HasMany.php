<?php

namespace Neoan\Model\Attributes;
use Attribute;
use Neoan\Enums\AttributeType;
use Neoan\Model\ModelAttribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class HasMany extends ModelAttribute
{
    public string $modelClass;
    public array $matching;
    public AttributeType $type = AttributeType::ATTACH;

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
}