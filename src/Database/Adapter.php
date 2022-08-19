<?php

namespace Neoan\Database;

use Exception;

interface Adapter
{
    public function __construct($credentials = []);

    /**
     * @throws Exception
     */
    public function raw(string $sql, array $conditions, mixed $extra);

    /**
     * @throws Exception
     */
    public function easy(string $selectorString, array $conditions = [], mixed $extra = null);

    /**
     * @throws Exception
     */
    public function insert($table, array $content);

    /**
     * @throws Exception
     */
    public function update($table, array $values, array $where);

    /**
     * @throws Exception
     */
    public function delete($table, string $id, bool $hard = false);
}