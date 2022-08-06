<?php

namespace Neoan\Model;


use App\Models\User;
use Exception;
use Neoan\Database\Database;
use Neoan\Enums\AttributeType;
use Neoan\Enums\Direction;
use Neoan\Enums\TransactionType;
use Neoan\Event\Event;
use Neoan\Event\EventNotification;
use Neoan\Helper\AttributeHelper;
use Neoan\Model\Attributes\HasMany;
use Neoan\Model\Attributes\IsPrimaryKey;
use Neoan3\Apps\Db;
use ReflectionAttribute;
use ReflectionException;


class Model
{
    private TransactionType $transactionMode = TransactionType::INSERT;
    private static string $tableName;
    private static Interpreter $interpreter;
    private static EventNotification $notify;

    public function __construct(array $staticModel = [])
    {
        self::$notify = Event::makeListenable($this);
        self::$interpreter = new Interpreter(static::class);
        self::$interpreter->asInstance($this);
        self::$interpreter->initialize($staticModel);
        self::$notify->inform();
    }


    private static function interpret(): void
    {

        self::$interpreter = new Interpreter(static::class);
        self::$tableName = self::$interpreter->getTableName();
    }

    /**
     * @throws Exception
     */
    public function store(): static
    {
        self::interpret();
        $modelClass = get_called_class();
        $copy = new $modelClass($this->toArray(true));
        $insertOrUpdate = self::$interpreter->generateInsertUpdate($copy);

        $id = null;
        switch ($this->transactionMode) {
            case TransactionType::INSERT:
                $id = Database::insert(self::$tableName, $insertOrUpdate);
                break;
            case TransactionType::UPDATE:
                $id = $this->{self::$interpreter->getPrimaryKey()};
                Database::update(self::$tableName, $insertOrUpdate, [
                    self::$interpreter->getPrimaryKey() => $this->{self::$interpreter->getPrimaryKey()}
                ]);
                break;
        }

        $this->rehydrate($id);
        self::$notify->inform();
        return $this;
    }


    public function rehydrate($id = null): void
    {
        $fromDisk = $this->get($id ?? $this->{self::$interpreter->getPrimaryKey()});

        self::$interpreter->asInstance($fromDisk);
        $hasSetter = method_exists($this, 'set');
        foreach (self::$interpreter->initialize($fromDisk->toArray()) as $property => $value){
            $hasSetter ? $this->set($property, $value) : $this->{$property} = $value;
        }
        $this->transactionMode = TransactionType::UPDATE;
        self::$notify->inform();
    }

    /**
     * @throws Exception
     */
    public static function retrieve(array $condition = [], array $filter = []): Collection
    {
        self::interpret();
        $select = self::$tableName . '.' . self::$interpreter->getPrimaryKey();
        $results = Database::easy($select, $condition, $filter);

        $collection = new Collection();
        foreach ($results as $item) {
            $collection->add(self::get($item[self::$interpreter->getPrimaryKey()]));
        }
        return $collection;
    }

    /**
     * @throws Exception
     */
    public static function retrieveOne(array $condition): ?Model
    {
        self::interpret();
        $select = self::$tableName . '.' . self::$interpreter->getPrimaryKey();
        $results = Database::easy($select, $condition, ['limit'=>[0,1]]);
        if(!empty($results)){
            return self::get($results[0][self::$interpreter->getPrimaryKey()]);
        }
        return null;
    }

    public function toArray(bool $flat = false): array
    {
        $ignore = ['transactionMode','notify'];
        $values = get_object_vars($this);
        foreach ($ignore as $key) {
            unset($values[$key]);
        }
        foreach ($values as $key => $value) {
            if ($value instanceof Collection && $flat) {
                unset($values[$key]);
            } elseif ($value instanceof Collection) {
                $values[$key] = $value->toArray();
            }

        }
        return $values;
    }


    /**
     * @throws Exception
     */
    public static function get($primaryValue): static
    {
        self::interpret();

        $generated = self::$interpreter->generateSelect();
        $result = Database::easy($generated['selectorString'], [self::$tableName . '.id' => $primaryValue], ['limit' => [0, 1]]);

        if (empty($result)) {
            // some exception
            throw new Exception(static::class . '::get failed');
        }
        $pure = $result[0];
        /* Attachable: current primary value, current primary key */
        foreach ($generated['attachable'] as $property => $attachable) {
            $pure[$property] = $attachable($primaryValue, self::$interpreter->getPrimaryKey());
        }
        /* Mutatable: current property, current direction */
        foreach ($generated['mutatable'] as $property => $mutatable) {
            $pure = $mutatable($pure, Direction::OUT, $property);
        }
        $class = static::class;
        $model = new $class($pure);
        $model->setTransactionMode(TransactionType::UPDATE);
        return $model;
    }

    public static function declare(): array
    {
        self::interpret();
        return [self::$tableName => self::$interpreter->parsedModel];
    }

    public function getTransactionMode(): TransactionType
    {
        self::$notify->inform();
        return $this->transactionMode;
    }

    public function setTransactionMode(TransactionType $type): void
    {
        self::$notify->inform();
        $this->transactionMode = $type;
    }





}