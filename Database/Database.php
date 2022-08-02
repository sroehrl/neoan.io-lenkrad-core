<?php

namespace Neoan\Database;

use Exception;

class Database
{
    public static Adapter $client;


    public static function connect(Adapter $client): void
    {
        self::$client = $client;
    }

    /**
     * @throws Exception
     */
    public static function raw(string $sql, array $conditions = [], $extra = null)
    {
        return self::$client->raw($sql, $conditions, $extra);
    }

    /**
     * @throws Exception
     */
    public static function easy(string $selectorString, array $conditions = [], array $extra = null)
    {
        return self::$client->easy($selectorString, $conditions, $extra);
    }

    /**
     * @throws Exception
     */
    public static function insert($table, ?array $content = null)
    {
        return self::$client->insert($table, $content);
    }
    /**
     * @throws Exception
     */
    public static function update($table, array $values, array $where)
    {
        return self::$client->update($table, $values, $where);
    }
    /**
     * @throws Exception
     */
    public static function delete($table, string $id, bool $hard = false)
    {
        return self::$client->delete($table, $id, $hard);
    }
}