<?php
namespace App;

use \TelegramBot\Api\Types\Update;

/**
 * Created by PhpStorm.
 * User: laurent
 * Date: 10.01.18
 * Time: 21:21
 */
class CustomClient extends \TelegramBot\Api\Client
{
    private static function getMessageEvent($action)
    {
        return function (Update $update) use ($action) {
            if (!$update->getMessage()) {
                return true;
            }

            $reflectionAction = new \ReflectionFunction($action);
            $reflectionAction->invokeArgs([$update->getMessage()]);

            return false;
        };
    }

    public function message(\Closure $action)
    {
        return $this->on(self::getMessageEvent($action), self::getMessageChecker());
    }

    /**
     * Returns check function to handling the message.
     *
     * @return \Closure
     */
    protected static function getMessageChecker()
    {
        return function (Update $update) {
            return !is_null($update->getMessage()) && !empty($update->getMessage()->getText()) && strpos(
                $update->getMessage()->getText(),
                "/"
            ) !== 0;
        };
    }
}