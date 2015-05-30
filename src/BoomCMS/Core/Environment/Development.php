<?php

namespace BoomCMS\Core\Environment;

use Exception;
use BoomCMS\Core\Exception\Handler\Pub as PublicExceptionHandler;

class Development extends Environment
{
    protected $requiresLogin = true;

    /**
     *
     * @param  Exception              $e
     * @return PublicExceptionHandler
     */
    public function getExceptionHandler(Exception $e)
    {
        return new PublicExceptionHandler($e);
    }

    /**
     *
     * @return boolean
     */
    public function isDevelopment()
    {
        return true;
    }
}