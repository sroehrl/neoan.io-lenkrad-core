<?php

namespace Neoan\Database;

use Exception;
use Neoan\Enums\GenericEvent;
use Neoan\Event\Event;
use Neoan\Event\Listenable;

class Database implements Listenable
{
    public static Adapter $client;


    public static function connect(Adapter $client): void
    {
        self::$client = $client;
        Event::dispatch(GenericEvent::DATABASE_ADAPTER_CONNECTED, $client);
    }

    /**
     * @throws Exception
     */
    public static function raw(string $sql, array $conditions = [], $extra = null)
    {
        Event::dispatch(GenericEvent::BEFORE_DATABASE_TRANSACTION, [
            'clientMethod' => __FUNCTION__,
            'arguments' => func_get_args()
        ]);
        return self::respond(self::$client->raw($sql, $conditions, $extra));
    }

    /**
     * @throws Exception
     */
    public static function easy(string $selectorString, array $conditions = [], array $extra = null)
    {
        Event::dispatch(GenericEvent::BEFORE_DATABASE_TRANSACTION, [
            'clientMethod' => __FUNCTION__,
            'arguments' => func_get_args()
        ]);
        return self::respond(self::$client->easy($selectorString, $conditions, $extra));
    }

    /**
     * @throws Exception
     */
    public static function insert($table, ?array $content = null)
    {
        Event::dispatch(GenericEvent::BEFORE_DATABASE_TRANSACTION, [
            'clientMethod' => __FUNCTION__,
            'arguments' => func_get_args()
        ]);
        return self::respond(self::$client->insert($table, $content));
    }
    /**
     * @throws Exception
     */
    public static function update($table, array $values, array $where)
    {
        Event::dispatch(GenericEvent::BEFORE_DATABASE_TRANSACTION, [
            'clientMethod' => __FUNCTION__,
            'arguments' => func_get_args()
        ]);
        return self::respond(self::$client->update($table, $values, $where));
    }
    /**
     * @throws Exception
     */
    public static function delete($table, string $id, bool $hard = false)
    {
        Event::dispatch(GenericEvent::BEFORE_DATABASE_TRANSACTION, [
            'clientMethod' => __FUNCTION__,
            'arguments' => func_get_args()
        ]);
        return self::respond(self::$client->delete($table, $id, $hard));
    }
    private static function respond($result)
    {
        Event::dispatch(GenericEvent::AFTER_DATABASE_TRANSACTION, $result);
        return $result;
    }
}