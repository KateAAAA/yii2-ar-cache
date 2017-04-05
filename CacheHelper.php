<?php

namespace sitkoru\cache\ar;

use yii\redis\Connection;

/**
 * Class CacheHelper
 *
 * @package common\components\cache
 */
class CacheHelper
{
    /**
     * @return Connection
     */
    public static function getRedis()
    {
        return \Yii::$app->cache->redis;
    }

    public static function loadScript($path)
    {
        $script = file_get_contents($path);

        return static::getRedis()->executeCommand('SCRIPT', ['load', $script]);
    }

    public static function scriptExists($sha)
    {
        return reset(static::getRedis()->executeCommand('SCRIPT', ['exists', $sha]));
    }

    public static function evalSHA($sha, $args, $numKeys)
    {
        return static::getRedis()->evalsha($sha, $numKeys, ...$args);
    }

    public static function get($key)
    {
        $res = static::getRedis()->get($key);

        if ($res !== false) {
            try {
                return unserialize(zlib_decode($res));
            } catch (\Throwable $ex) {
                \Yii::error("Can't unserialize data for key {$key}: {$ex->getMessage()}");
            }
            return null;
        }
        return $res;
    }
}