<?php

namespace Neoan\Helper;

class Terminate
{
    /**
     * @throws \Exception
     */
    public static function exit(): void
    {
        if (defined('TEST_MODE')) {
            throw new \Exception('Wanted to exit');
        } else {
            exit();
        }

    }

    /**
     * @throws \Exception
     */
    public static function die(): void{
        if (defined('TEST_MODE')) {
            throw new \Exception('Wanted to die');
        } else {
            die();
        }

    }
}