<?php

namespace Microweber\Providers;

use Microweber\Utils\Adapters\Cache\LaravelCache;

/**
 * Cache class.
 *
 * These functions will allow you to save and get data from the MW cache system
 *
 * @category Cache
 * @desc     These functions will allow you to save and get data from the MW cache system
 */
class CacheManager
{
    /**
     * An instance of the Microweber Application class.
     *
     * @var
     */
    public $app;
    /**
     * An instance of the cache adapter to use.
     *
     * @var
     */
    public $adapter;

    public function __construct($app = null)
    {
        if (!is_object($this->app)) {
            if (is_object($app)) {
                $this->app = $app;
            } else {
                $this->app = mw();
            }
        }

        $this->adapter = new LaravelCache($app);
    }

    /**
     * Stores your data in the cache.
     * It can store any value that can be serialized, such as strings, array, etc.
     *
     * @param mixed  $data_to_cache
     *                              your data, anything that can be serialized
     * @param string $cache_id
     *                              id of the cache, you must define it because you will use it later to
     *                              retrieve the cached content.
     * @param string $cache_group
     *                              (default is 'global') - this is the subfolder in the cache dir.
     *
     * @return bool
     *
     * @example
     * <code>
     * //store custom data in cache
     * $data = array('something' => 'some_value');
     * $cache_id = 'my_cache_id';
     * $cache_content = mw()->cache_manager->save($data, $cache_id, 'my_cache_group');
     * </code>
     */
    public function save($data_to_cache, $cache_id, $cache_group = 'global', $expiration = false)
    {
        return $this->adapter->save($data_to_cache, $cache_id, $cache_group, $expiration);
    }

    /**
     *  Gets the data from the cache.
     *
     *  If data is not found it return false
     *     *
     *
     * @param string $cache_id    id of the cache
     * @param string $cache_group (default is 'global') - this is the subfolder in the cache dir.
     * @param bool   $timeout
     *
     * @return mixed returns array of cached data or false
     *
     * @example
     * <code>
     *
     * $cache_id = 'my_cache_'.crc32($sql_query_string);
     * $cache_content = mw()->cache_manager->get($cache_id, 'my_cache_group');
     *
     * </code>
     */
    public function get($cache_id, $cache_group = 'global', $timeout = false)
    {
        return $this->adapter->get($cache_id, $cache_group, $timeout);
    }

    /**
     * Deletes cache for given $cache_group recursively.
     *
     * @param string $cache_group
     *                            (default is 'global') - this is the subfolder in the cache dir.
     *
     * @return bool
     *
     * @example
     * <code>
     * //delete the cache for the content
     *  mw()->cache_manager->delete("content");
     *
     * //delete the cache for the content with id 1
     *  mw()->cache_manager->delete("content/1");
     *
     * //delete the cache for users
     *  mw()->cache_manager->delete("users");
     *
     * //delete the cache for your custom table eg. my_table
     * mw()->cache_manager->delete("my_table");
     * </code>
     */
    public function delete($cache_group = 'global')
    {
        $this->adapter->delete($cache_group);
    }

    /**
     * Clears all cache data.
     *
     * @example
     *          <code>
     *          //delete all cache
     *          mw()->cache_manager->clear();
     *          </code>
     *
     * @return bool
     */
    public function clear()
    {
        return $this->adapter->clear();
    }

    /**
     * Prints cache debug information.
     *
     * @return array
     *
     * @example
     * <code>
     * //get cache items info
     *  $cached_items = mw()->cache_manager->debug();
     * print_r($cached_items);
     * </code>
     */
    public function debug()
    {
        return $this->adapter->debug();
    }
}
