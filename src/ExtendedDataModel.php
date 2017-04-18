<?php

namespace ItvisionSy\SimpleORM;

/**
 * Description of ExtendedDataModel
 *
 * @author muhannad
 */
abstract class ExtendedDataModel extends DataModel {

    protected $cache = [];
    protected static $staticCache = [];

    /**
     *
     * @param string $key
     * @param scalar|mixed $value
     * @return $this
     */
    protected function cache($key, $value) {
        $this->cache[$key] = $value;
        return $this;
    }

    /**
     *
     * @param string $key
     * @return boolean
     */
    protected function isCached($key) {
        return array_key_exists($key, $this->cache);
    }

    /**
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function cached($key, $default = null) {
        return $this->isCached($key) ? $this->cache[$key] : $default;
    }

    /**
     *
     * @param tring $key
     * @param \callable $value
     * @return mixed
     */
    protected function cachedOrCache($key, callable $value) {
        if (!$this->isCached($key)) {
            $this->cache($key, $value());
        }
        return $this->cached($key);
    }

    /**
     *
     */
    protected static function assertStaticCacheExists() {
        if (!array_key_exists(get_called_class(), static::$staticCache)) {
            static::$staticCache[get_called_class()] = [];
        }
    }

    /**
     *
     * @param string $key
     * @param scalar|mixed $value
     * @return $this
     */
    protected static function staticCache($key, $value) {
        static::assertStaticCacheExists();
        static::$staticCache[get_called_class()][$key] = $value;
        return $this;
    }

    /**
     *
     * @param string $key
     * @return boolean
     */
    protected static function staticIsCached($key) {
        static::assertStaticCacheExists();
        return array_key_exists($key, static::$staticCache[get_called_class()]);
    }

    /**
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected static function staticCached($key, $default = null) {
        static::assertStaticCacheExists();
        return static::$staticIsCached($key) ? static::$staticCache[get_called_class()][$key] : $default;
    }

    /**
     *
     * @param string $key
     * @param \callable $value
     * @return mixed
     */
    protected static function staticCachedOrCache($key, callable $value) {
        if (!static::staticIsCached($key)) {
            static::staticCache($key, $value());
        }
        return static::staticCached($key);
    }

    /**
     *
     * @param string $modelClass
     * @param string $localKey
     * @param string $foreignKey
     * @param string $fetchMode
     * @param boolean $forceReload
     * @param string $key
     * @param string $extraQuery
     * @param array $extraParams
     * @return ExtendedDataModel|ExtendedDataModel[]|array|self|self[]|static|static[]
     */
    protected function loadRelation($modelClass, $localKey, $foreignKey, $fetchMode = static::FETCH_MANY, $forceReload = false, $key = null, $extraQuery = null, array $extraParams = []) {
        $key = 'fk__' . ($key ?: $modelClass . $localKey . $foreignKey . $fetchMode);
        return $this->cachedOrCache($key, function() use($modelClass, $localKey, $foreignKey, $fetchMode, $extraQuery, $extraParams) {
                    return call_user_func_array([$modelClass, sql], ["SELECT * FROM :table WHERE {$foreignKey}=?" . ($extraQuery ? " {$extraQuery}" : ""), $fetchMode, array_merge([$this->$localKey], $extraParams)]);
                });
    }

}
