<?php

/**
 * @link     https://github.com/mirrorgdit/php-helper
 * @document https://github.com/mirrorgdit/php-helper
 * @contact  mirrorgdit@163.com
 * @license  https://github.com/mirrorgdit/php-helper/blob/master/README.md
 */

namespace mirrorgdit\helper;
use mirrorgdit\helper\UserException;

/**
 * Class RedisHelper
 * @package mirrorgdit\helper
 */
class RedisHelper
{
    /**
     * 配置数组
     * @var array
     */
    private $_configArr = array();
    /**
     * 连接超时(秒)
     * @var float
     */
    private $_timeout = 3.0;
    /**
     * Redis的实例
     * @var Redis
     */
    private $_r;
    /**
     * 数据库索引
     * @var int
     */
    private $_dbindex = 0;

    /**
     * 构造函数
     * @param array $configArr 配置数组array($host, $port)
     */
    public function __construct($configArr)
    {
        $this->_configArr = array(
            'host' => $configArr[0],
            'port' => $configArr[1],
        );
    }

    /**
     * 获取配置数组
     * @return array
     */
    public function getConfigArr()
    {
        return $this->_configArr;
    }

    /**
     * 获取Redis的实例
     * @return Redis
     */
    public function getConn()
    {
        if (!isset($this->_r)) {
            // 检查扩展模块是否加载
            if (!extension_loaded('redis')) {
                throw new UserException(UserException::ERROR_EXTENSION_NOT_LOADED, 'Extension(%s) not loaded!', 'redis');
            }
            // 创建Redis的实例
            $this->_r = new \Redis();
            try {
                $this->_r->connect($this->_configArr['host'], $this->_configArr['port'], $this->_timeout);
            } catch (\RedisException $e) {
                throw new UserException(UserException::ERROR_REDIS, 'Cannot connect to redis server(%s)!', $e->getMessage());
            }
        }
        return $this->_r;
    }

    /**
     * Get the value related to the specified key
     * @param string $key
     * @return string/bool If key didn't exist, FALSE is returned. Otherwise, the value related to this key is returned.
     * @example $redis->get('key');
     */
    public function get($key)
    {
        return $this->getConn()->get($key);
    }

    /**
     * Get the values of all the specified keys. If one or more keys dont exist, the array will contain FALSE at the position of the key.
     * @param array $keyArr
     * @return array
     */
    public function mget($keyArr)
    {
        return $this->getConn()->mget($keyArr);
    }

    /**
     * Set the string value in argument as value of the key.
     * @param string $key
     * @param string $value
     * @return bool TRUE if the command is successful.
     * @example $redis->set('key', 'value');
     */
    public function set($key, $value)
    {
        return $this->getConn()->set($key, $value);
    }

    /**
     * Set the string value in argument as value of the key, with a time to live.
     * @param string $key
     * @param int $ttl
     * @param string $value
     * @return bool TRUE if the command is successful.
     * @example $redis->setex('key', 3600, 'value'); // sets key → value, with 1h TTL.
     */
    public function setEx($key, $ttl, $value)
    {
        return $this->getConn()->setEx($key, $ttl, $value);
    }

    /**
     * Set the string value in argument as value of the key if the key doesn't already exist in the database.
     * @param string $key
     * @param string $value
     * @return bool TRUE in case of success, FALSE in case of failure.
     */
    public function setNx($key, $value)
    {
        return $this->getConn()->setNx($key, $value);
    }

    /**
     * Sets a value and returns the previous entry at that key.
     * @param string $key
     * @param string $value
     * @return string the previous value located at this key.
     * @example $old = $redis->getSet('x', 'new');
     */
    public function getSet($key, $value)
    {
        return $this->getConn()->getSet($key, $value);
    }

    /**
     * Changes a single bit of a string.
     * @param string $key
     * @param int $offset start from 0
     * @param int $value 1/0
     * @return int 0 or 1, the value of the bit before it was set.
     * @example $redis->set('key', "*");    // ord("*") = 42 = 0x2f = "0010 1010"<br />
     *           $redis->setBit('key', 5, 1); // return 0<br />
     *           $redis->setBit('key', 7, 1); // return 1<br />
     *           $redis->get('key'); // chr(0x2f) = "/" = b("0010 1111")
     */
    public function setBit($key, $offset, $value)
    {
        return $this->getConn()->setBit($key, $offset, $value);
    }

    /**
     * Return a single bit out of a larger string
     * @param string $key
     * @param int $offset
     * @return int the bit value (0 or 1)
     * @example $redis->set('key', "\x7f"); // this is 0111 1111<br />
     *           $redis->getBit('key', 0); // 0<br />
     *           $redis->getBit('key', 1); // 1
     */
    public function getBit($key, $offset)
    {
        return $this->getConn()->getBit($key, $offset);
    }

    /**
     * Changes a substring of a larger string.
     * @param string $key
     * @param int $offset
     * @param string $value
     * @return int the length of the string after it was modified by the command.
     */
    public function setRange($key, $offset, $value)
    {
        return $this->getConn()->setRange($key, $offset, $value);
    }

    /**
     * Get a substring of the string stored at a key
     * @param string $key
     * @param int $start
     * @param int $end
     * @return string the substring
     */
    public function getRange($key, $start, $end)
    {
        return $this->getConn()->getRange($key, $start, $end);
    }

    /**
     * Returns a random key.
     * @return string an existing key in redis.
     */
    public function randomKey()
    {
        return $this->getConn()->randomKey();
    }

    /**
     * Renames a key.
     * @param string $srcKey
     * @param string $dstKey
     * @return bool TRUE in case of success, FALSE in case of failure.
     */
    public function renameKey($srcKey, $dstKey)
    {
        return $this->getConn()->renameKey($srcKey, $dstKey);
    }

    /**
     * Same as rename, but will not replace a key if the destination already exists. This is the same behaviour as setNx.
     * @param string $srcKey
     * @param string $dstKey
     * @return bool  TRUE in case of success, FALSE in case of failure.
     */
    public function renameNx($srcKey, $dstKey)
    {
        return $this->getConn()->renameNx($srcKey, $dstKey);
    }

    /**
     * Verify if the specified key exists.
     * @param string $key
     * @return bool If the key exists, return TRUE, otherwise return FALSE.
     */
    public function exists($key)
    {
        return $this->getConn()->exists($key);
    }

    /**
     * Remove specified keys
     * @param string/array $key An array of keys, or an undefined number of parameters, each a key: key1 key2 key3 ... keyN
     * @return int Number of keys deleted.
     * @example $redis->del('key1', 'key2');<br />
     *           $redis->del(array('key3', 'key4'));
     */
    public function del($key)
    {
        return call_user_func_array(array($this->getConn(), 'del'), func_get_args());
    }

    /**
     * Increment the number stored at key by one. If the second argument is filled, it will be used as the integer value of the increment.
     * @param string $key
     * @return int the new value
     * @example $redis->incr('key1');
     */
    public function incr($key)
    {
        return $this->getConn()->incr($key);
    }

    /**
     * Increment the number stored at key by one. If the second argument is filled, it will be used as the integer value of the increment.
     * @param string $key
     * @param int $value
     * @return int the new value
     * @example $redis->incrBy('key1', 10);
     */
    public function incrBy($key, $value)
    {
        return $this->getConn()->incrBy($key, $value);
    }

    /**
     * Decrement the number stored at key by one. If the second argument is filled, it will be used as the integer value of the decrement.
     * @param string $key
     * @return int the new value
     * @example $redis->decr('key1');
     */
    public function decr($key)
    {
        return $this->getConn()->decr($key);
    }

    /**
     * Decrement the number stored at key by one. If the second argument is filled, it will be used as the integer value of the decrement.
     * @param string $key
     * @param int $value
     * @return int the new value
     * @example $redis->decrBy('key1', 10);
     */
    public function decrBy($key, $value)
    {
        return $this->getConn()->decrBy($key, $value);
    }

    /**
     * Returns the type of data pointed by a given key.
     * @param string $key key
     * @return mixed Depending on the type of the data pointed by the key, this method will return the following value:<br />
     *          string: Redis::REDIS_STRING<br />
     *          set: Redis::REDIS_SET<br />
     *          list: Redis::REDIS_LIST<br />
     *          zset: Redis::REDIS_ZSET<br />
     *          hash: Redis::REDIS_HASH<br />
     *          other: Redis::REDIS_NOT_FOUND
     * @example $redis->type('key');
     */
    public function type($key)
    {
        return $this->getConn()->type($key);
    }

    /**
     * Append specified string to the string stored in specified key.
     * @param string $key
     * @param string $value
     * @return int Size of the value after the append
     */
    public function append($key, $value)
    {
        return $this->getConn()->append($key, $value);
    }

    /**
     * Get the length of a string value.
     * @param string $key
     * @return int
     */
    public function strLen($key)
    {
        return $this->getConn()->strLen($key);
    }

    /**
     * Returns the keys that match a certain pattern.
     * @param string $pattern using '*' as a wildcard.
     * @return array Array of STRING: The keys that match a certain pattern.
     * @example $allKeys = $redis->keys('*');    // all keys will match this.<br />
     *           $keyWithUserPrefix = $redis->keys('user*');
     */
    public function keys($pattern)
    {
        return $this->getConn()->keys($pattern);
    }

    /**
     * sort
     * @param string $key
     * @param array $options
     * @return array An array of values, or a number corresponding to the number of elements stored if that was used.
     * @example $redis->sort('s');<br />
     *           $redis->sort('s', array('sort' => 'desc'));<br />
     *           $redis->sort('s', array('sort' => 'asc', 'store' => 'out'));
     */
    public function sort($key, $options)
    {
        return $this->getConn()->sort($key, $options);
    }

    public function sortAsc()
    {

    }

    public function sortAscAlpha()
    {

    }

    public function sortDesc()
    {

    }

    public function sortDescAlpha()
    {

    }

    /**
     * Adds the string value to the head (left) of the list. Creates the list if the key didn't exist. If the key exists and is not a list, FALSE is returned.
     * @param string $key
     * @param string $value
     * @return int The new length of the list in case of success, FALSE in case of Failure.
     */
    public function lPush($key, $value)
    {
        return $this->getConn()->lPush($key, $value);
    }

    /**
     * Adds the string value to the tail (right) of the list. Creates the list if the key didn't exist. If the key exists and is not a list, FALSE is returned.
     * @param string $key
     * @param string $value
     * @return int The new length of the list in case of success, FALSE in case of Failure.
     */
    public function rPush($key, $value)
    {
        return $this->getConn()->rPush($key, $value);
    }

    /**
     * Adds the string value to the head (left) of the list if the list exists.
     * @param string $key
     * @param string $value
     * @return int The new length of the list in case of success, FALSE in case of Failure.
     */
    public function lPushx($key, $value)
    {
        return $this->getConn()->lPushx($key, $value);
    }

    /**
     * Adds the string value to the tail (right) of the list if the ist exists. FALSE in case of Failure.
     * @param string $key
     * @param string $value
     * @return int The new length of the list in case of success, FALSE in case of Failure.
     */
    public function rPushx($key, $value)
    {
        return $this->getConn()->rPushx($key, $value);
    }

    /**
     * Return and remove the first element of the list.
     * @param string $key
     * @return string if command executed successfully BOOL FALSE in case of failure (empty list)
     */
    public function lPop($key)
    {
        return $this->getConn()->lPop($key);
    }

    /**
     * Returns and removes the last element of the list.
     * @param string $key
     * @return string if command executed successfully BOOL FALSE in case of failure (empty list)
     */
    public function rPop($key)
    {
        return $this->getConn()->rPop($key);
    }

    /**
     * Is a blocking lPop(rPop) primitive. If at least one of the lists contains at least one element, the element will be popped from the head of the list and returned to the caller. Il all the list identified by the keys passed in arguments are empty, blPop will block during the specified timeout until an element is pushed to one of those lists. This element will be popped.
     * @param array $keyArr Array containing the keys of the lists
     * @param int $timeout
     * @return array
     */
    public function blPop($keyArr, $timeout)
    {
        return call_user_func_array(array($this->getConn(), 'blPop'), func_get_args());
    }

    /**
     * Is a blocking lPop(rPop) primitive. If at least one of the lists contains at least one element, the element will be popped from the head of the list and returned to the caller. Il all the list identified by the keys passed in arguments are empty, blPop will block during the specified timeout until an element is pushed to one of those lists. This element will be popped.
     * @param array $keyArr Array containing the keys of the lists
     * @param int $timeout
     * @return array
     */
    public function brPop($keyArr, $timeout)
    {
        return call_user_func_array(array($this->getConn(), 'brPop'), func_get_args());
    }

    /**
     * Removes the first count occurences of the value element from the list. If count is zero, all the matching elements are removed. If count is negative, elements are removed from tail to head.
     * @param string $key
     * @param string $value
     * @param int $count
     * @return int/bool LONG the number of elements to remove.BOOL FALSE if the value identified by key is not a list.
     */
    public function lRem($key, $value, $count)
    {
        return $this->getConn()->lRem($key, $value, $count);
    }

    /**
     * Trims an existing list so that it will contain only a specified range of elements.
     * @param string $key
     * @param int $start
     * @param int $stop
     * @return bool return FALSE if the key identify a non-list value.
     */
    public function lTrim($key, $start, $stop)
    {
        return $this->getConn()->lTrim($key, $start, $stop);
    }

    /**
     * Return the specified element of the list stored at the specified key. 0 the first element, 1 the second ... -1 the last element, -2 the penultimate ... Return FALSE in case of a bad index or a key that doesn't point to a list.
     * @param string $key
     * @param int $index
     * @return string/bool String the element at this index.Bool FALSE if the key identifies a non-string data type, or no value corresponds to this index in the list Key.
     */
    public function lIndex($key, $index)
    {
        return $this->getConn()->lIndex($key, $index);
    }

    /**
     * Returns the specified elements of the list stored at the specified key in the range [start, end]. start and stop are interpretated as indices: 0 the first element, 1 the second ... -1 the last element, -2 the penultimate ...
     * @param string $key
     * @param int $start
     * @param int $end
     * @return array Array containing the values in specified range.
     */
    public function lRange($key, $start, $end)
    {
        return $this->getConn()->lRange($key, $start, $end);
    }

    /**
     * Set the list at index with the new value.
     * @param string $key
     * @param int $index
     * @param string $value
     * @return bool TRUE if the new value is setted. FALSE if the index is out of range, or data type identified by key is not a list.
     */
    public function lSet($key, $index, $value)
    {
        return $this->getConn()->lSet($key, $index, $value);
    }

    /**
     * Insert value in the list before or after the pivot value. the parameter options specify the position of the insert (before or after). If the list didn't exists, or the pivot didn't exists, the value is not inserted.
     * @param string $key
     * @param int $position Redis::BEFORE | Redis::AFTER
     * @param string $pivot
     * @param string $value
     * @return int The number of the elements in the list, -1 if the pivot didn't exists.
     */
    public function lInsert($key, $position, $pivot, $value)
    {
        return $this->getConn()->lInsert($key, $position, $pivot, $value);
    }

    /**
     * Adds a value to the set value stored at key. If this value is already in the set, FALSE is returned.
     * @param string $key
     * @param string $value
     * @return bool TRUE if value didn't exist and was added successfully, FALSE if the value is already present.
     */
    public function sAdd($key, $value)
    {
        return $this->getConn()->sAdd($key, $value);
    }

    /**
     * Returns the cardinality of the set identified by key.
     * @param string $key
     * @return int the cardinality of the set identified by key, 0 if the set doesn't exist.
     */
    public function sCard($key)
    {
        return $this->getConn()->sSize($key);
    }

    /**
     * Removes the specified member from the set value stored at key.
     * @param string $key
     * @param string $member
     * @return bool TRUE if the member was present in the set, FALSE if it didn't.
     */
    public function sRem($key, $member)
    {
        return $this->getConn()->sRem($key, $member);
    }

    /**
     * Moves the specified member from the set at srcKey to the set at dstKey.
     * @param string $srcKey
     * @param string $dstKey
     * @param string $member
     * @return bool If the operation is successful, return TRUE. If the srcKey and/or dstKey didn't exist, and/or the member didn't exist in srcKey, FALSE is returned.
     */
    public function sMove($srcKey, $dstKey, $member)
    {
        return $this->getConn()->sMove($srcKey, $dstKey, $member);
    }

    /**
     * Removes and returns a random element from the set value at Key.
     * @param string $key
     * @return string/bool String "popped" value.Bool FALSE if set identified by key is empty or doesn't exist.
     */
    public function sPop($key)
    {
        return $this->getConn()->sPop($key);
    }

    /**
     * Returns a random element from the set value at Key, without removing it.
     * @param string $key
     * @return string/bool String value from the set.Bool FALSE if set identified by key is empty or doesn't exist.
     */
    public function sRandMember($key)
    {
        return $this->getConn()->sRandMember($key);
    }

    /**
     * Checks if value is a member of the set stored at the key key.
     * @param string $key
     * @param string $value
     * @return bool TRUE if value is a member of the set at key key, FALSE otherwise.
     */
    public function sIsMember($key, $value)
    {
        return $this->getConn()->sContains($key, $value);
    }

    /**
     * Returns the contents of a set.
     * @param string $key
     * @return array An array of elements, the contents of the set.
     * @example $redis->sMembers('s');
     */
    public function sMembers($key)
    {
        return $this->getConn()->sMembers($key);
    }

    /**
     * Returns the members of a set resulting from the intersection of all the sets held at the specified keys. If just a single key is specified, then this command produces the members of this set. If one of the keys is missing, FALSE is returned.
     * @param ... key1, key2, keyN: keys identifying the different sets on which we will apply the intersection.
     * @return array Array, contain the result of the intersection between those keys. If the intersection beteen the different sets is empty, the return value will be empty array.
     * @example $redis->sInter('key1', 'key2', 'key3');
     */
    public function sInter()
    {
        return call_user_func_array(array($this->getConn(), 'sInter'), func_get_args());
    }

    /**
     * Performs a sInter command and stores the result in a new set.
     * @param string $dstKey the key to store the diff into.
     * @param ... key1, key2... keyN. key1..keyN are intersected as in sInter.
     * @return int The cardinality of the resulting set, or FALSE in case of a missing key.
     * @example $redis->sInterStore('dst', 's0', 's1');
     */
    public function sInterStore()
    {
        return call_user_func_array(array($this->getConn(), 'sInterStore'), func_get_args());
    }

    /**
     * Performs the union between N sets and returns it.
     * @param ... key1, key2, ... , keyN: Any number of keys corresponding to sets in redis.
     * @return array Array of strings: The union of all these sets.
     * @example $redis->sUnion('s0', 's1', 's2');
     */
    public function sUnion()
    {
        return call_user_func_array(array($this->getConn(), 'sUnion'), func_get_args());
    }

    /**
     * Performs the same action as sUnion, but stores the result in the first key
     * @param string $dstKey Key: dstkey, the key to store the diff into.
     * @param ... key1, key2, ... , keyN: Any number of keys corresponding to sets in redis.
     * @return int The cardinality of the resulting set, or FALSE in case of a missing key.
     * @example $redis->sUnionStore('dst', 's0', 's1', 's2');
     */
    public function sUnionStore($dstKey)
    {
        return call_user_func_array(array($this->getConn(), 'sUnionStore'), func_get_args());
    }

    /**
     * Performs the difference between N sets and returns it.
     * @param ... key1, key2, ... , keyN: Any number of keys corresponding to sets in redis.
     * @return array The difference of the first set will all the others.
     * @example $redis->sDiff('s0', 's1', 's2');
     */
    public function sDiff()
    {
        return call_user_func_array(array($this->getConn(), 'sDiff'), func_get_args());
    }

    /**
     * Performs the same action as sDiff, but stores the result in the first key
     * @param string $dstKey dstkey, the key to store the diff into.
     * @param ... key1, key2, ... , keyN: Any number of keys corresponding to sets in redis
     * @return int The cardinality of the resulting set, or FALSE in case of a missing key.
     * @example $redis->sDiffStore('dst', 's0', 's1', 's2');
     */
    public function sDiffStore($dstKey)
    {
        return call_user_func_array(array($this->getConn(), 'sDiffStore'), func_get_args());
    }

    /**
     * Performs a synchronous save.
     * @return bool TRUE in case of success, FALSE in case of failure. If a save is already running, this command will fail and return FALSE.
     * @example $redis->save();
     */
    public function save()
    {
        return $this->getConn()->save();
    }

    /**
     * Performs a background save.
     * @return bool TRUE in case of success, FALSE in case of failure. If a save is already running, this command will fail and return FALSE.
     * @example $redis->bgSave();
     */
    public function bgSave()
    {
        return $this->getConn()->bgSave();
    }

    /**
     * Returns the timestamp of the last disk save.
     * @return int timestamp
     * @example $redis->lastSave();
     */
    public function lastSave()
    {
        return $this->getConn()->lastSave();
    }

    /**
     * Removes all entries from the current database.
     * @return bool Always TRUE.
     * @example $redis->flushDB();
     */
    public function flushDB()
    {
        throw new UserException(UserException::ERROR_REDIS, 'Call disabled function(%s)!', __FUNCTION__);
    }

    /**
     * Removes all entries from all databases.
     * @return bool Always TRUE.
     * @example $redis->flushAll();
     */
    public function flushAll()
    {
        throw new UserException(UserException::ERROR_REDIS, 'Call disabled function(%s)!', __FUNCTION__);
    }

    /**
     * Returns the current database's size.
     * @return int DB size, in number of keys.
     * @example $count = $redis->dbSize();<br />
     *           echo "Redis has $count keys\n";
     */
    public function dbSize()
    {
        return $this->getConn()->dbSize();
    }

    /**
     * Authenticate the connection using a password. Warning: The password is sent in plain-text over the network.
     * @param string $password
     * @return bool TRUE if the connection is authenticated, FALSE otherwise.
     * @example $redis->auth('foobared');
     */
    public function auth($password)
    {
        return $this->getConn()->auth($password);
    }

    /**
     * Returns the time to live left for a given key, in seconds. If the key doesn't exist, FALSE is returned.
     * @param string $key
     * @return int/bool Long, the time left to live in seconds.
     * @example $redis->ttl('key');
     */
    public function ttl($key)
    {
        return $this->getConn()->ttl($key);
    }

    /**
     * Remove the expiration timer from a key.
     * @param string $key key
     * @return bool TRUE if a timeout was removed, FALSE if the key didn’t exist or didn’t have an expiration timer.
     * @example $redis->persist('key');
     */
    public function persist($key)
    {
        return $this->getConn()->persist($key);
    }

    /**
     * Returns an associative array of strings and integers, with the following keys:
     * redis_version<br />
     * arch_bits<br />
     * uptime_in_seconds<br />
     * uptime_in_days<br />
     * connected_clients<br />
     * connected_slaves<br />
     * used_memory<br />
     * changes_since_last_save<br />
     * bgsave_in_progress<br />
     * last_save_time<br />
     * total_connections_received<br />
     * total_commands_processed<br />
     * role<br />
     */
    public function info()
    {
        return $this->getConn()->info();
    }

    /**
     * Switches to a given database.
     * @param int $dbindex the database number to switch to.
     * @return bool TRUE in case of success, FALSE in case of failure.
     * @example $redis->select(1);
     */
    public function select($dbindex)
    {
        if ($this->_dbindex == $dbindex) {
            return true;
        }
        $ret = $this->getConn()->select($dbindex);
        if ($ret) {
            $this->_dbindex = $dbindex;
            return true;
        }
        // 不能选择DB,直接抛异常
        throw new UserException(UserException::ERROR_REDIS, 'Cannot select db(%d)!', $dbindex);
    }

    /**
     * Moves a key to a different database.
     * @param string $key the key to move.
     * @param int $dbindex the database number to move the key to.
     * @return bool TRUE in case of success, FALSE in case of failure.
     * @example $redis->move('x', 1) // move to DB 1
     */
    public function move($key, $dbindex)
    {
        return $this->getConn()->move($key, $dbindex);
    }

    /**
     * Starts the background rewrite of AOF (Append-Only File)
     * @return bool TRUE in case of success, FALSE in case of failure.
     */
    public function bgRewriteAof()
    {
        return $this->getConn()->bgRewriteAof();
    }

    /**
     * Changes the slave status
     * @param string $host Either host (string) and port (int), or no parameter to stop being a slave.
     * @param int $port
     * @return bool TRUE in case of success, FALSE in case of failure.
     * @example $redis->slaveof('10.0.1.7',6379);<br />
     *           $redis->salveof();
     */
    public function slaveof($host, $port)
    {
        return $this->getConn()->slaveof($host, $port);
    }

    /**
     * Sets multiple key-value pairs in one atomic command
     * @param array $arr array(key => value, ...)
     * @return bool Bool TRUE in case of success, FALSE in case of failure.
     */
    public function mset($arr)
    {
        return $this->getConn()->mset($arr);
    }

    /**
     * Sets multiple key-value pairs in one atomic command
     * @param array $arr array(key => value, ...)
     * @return bool Bool returns TRUE if all the keys were set
     */
    public function msetnx($arr)
    {
        return $this->getConn()->msetnx($arr);
    }

    /**
     * Pops a value from the tail of a list, and pushes it to the front of another list. Also return this value.
     * @param string $srcKey
     * @param string $dstKey
     * @return string/false STRING The element that was moved in case of success, FALSE in case of failure.
     */
    public function rpoplpush($srcKey, $dstKey)
    {
        return $this->getConn()->rpoplpush($srckey, $dstKey);
    }

    /**
     * Adds the specified member with a given score to the sorted set stored at key.
     * @param string $key
     * @param float $score
     * @param string $value
     * @return int 1 if the element is added. 0 otherwise.
     * @example $redis->zAdd('key', 1, 'val1');
     */
    public function zAdd($key, $score, $value)
    {
        return $this->getConn()->zAdd($key, $score, $value);
    }

    /**
     * Deletes a specified member from the ordered set.
     * @param string $key
     * @param string $member
     * @return int 1 on success, 0 on failure.
     * @example $redis->zDelete('key', 'val2');
     */
    public function zDelete($key, $member)
    {
        return $this->getConn()->zDelete($key, $member);
    }

    /**
     * Returns a range of elements from the ordered set stored at the specified key, with values in the range [start, end]. start and stop are interpreted as zero-based indices: 0 the first element, 1 the second ... -1 the last element, -2 the penultimate ...
     * @param int $key
     * @param float $start
     * @param float $end
     * @param bool $withscores
     * @return array Array containing the value in specified range.
     * @example $redis->zRange('key1', 0, -1, true);
     */
    public function zRange($key, $start, $end, $withscores = false)
    {
        return $this->getConn()->zRange($key, $start, $end, $withscores);
    }

    /**
     * Returns the elements of the sorted set stored at the specified key in the range [start, end] in reverse order. start and stop are interpretated as zero-based indices: 0 the first element, 1 the second ... -1 the last element, -2 the penultimate ...
     * @param int $key
     * @param float $start
     * @param float $end
     * @param bool $withscores
     * @return array Array containing the values in specified range.
     * @example $redis->zReverseRange('key', 0, -1, true);
     */
    public function zReverseRange($key, $start, $end, $withscores = false)
    {
        return $this->getConn()->zReverseRange($key, $start, $end, $withscores);
    }

    /**
     * Returns the elements of the sorted set stored at the specified key which have scores in the range [start,end]. Adding a parenthesis before start or end excludes it from the range. +inf and -inf are also valid limits.
     * @param string $key
     * @param float $start
     * @param float $end
     * @param array $options array('withscores' => TRUE) or array('limit' => array($offset, $count))
     * @return array Array containing the values in specified range.
     * @example $redis->zRangeByScore('key', 0, 3);<br />
     *           $redis->zRangeByScore('key', 0, 3, array('withscores' => TRUE));<br />
     *           $redis->zRangeByScore('key', 0, 3, array('limit' => array(1, 1)));<br />
     *           $redis->zRangeByScore('key', 0, 3, array('withscores' => TRUE, 'limit' => array(1, 1)));
     */
    public function zRangeByScore($key, $start, $end, $options)
    {
        return $this->getConn()->zRangeByScore($key, $start, $end, $options);
    }

    /**
     * Returns the number of elements of the sorted set stored at the specified key which have scores in the range [start,end]. Adding a parenthesis before start or end excludes it from the range. +inf and -inf are also valid limits.
     * @param string $key
     * @param float $start
     * @param float $end
     * @return int the size of a corresponding zRangeByScore.
     */
    public function zCount($key, $start, $end)
    {
        return $this->getConn()->zCount($key, $start, $end);
    }

    public function zDeleteRangeByScore()
    {
        return $this->getConn()->zDeleteRangeByScore();
    }

    /**
     * Returns the cardinality of an ordered set.
     * @param string $key
     * @return int the set's cardinality
     */
    public function zCard($key)
    {
        return $this->getConn()->zCard($key);
    }

    /**
     * Returns the score of a given member in the specified sorted set.
     * @param string $key
     * @param string $member
     * @return float the item's score.
     * @example $redis->zScore('key', 'val2');
     */
    public function zScore($key, $member)
    {
        return $this->getConn()->zScore($key, $member);
    }

    /**
     * Returns the rank of a given member in the specified sorted set, starting at 0 for the item with the smallest score.
     * @param string $key
     * @param string $member
     * @return int the item's rank.
     * @example $redis->zRank('key', 'one');
     */
    public function zRank($key, $member)
    {
        return $this->getConn()->zRank($key, $member);
    }

    /**
     * Returns the rank of a given member in the specified sorted set, starting at 0 for the item with the largest score.
     * @param string $key
     * @param string $member
     * @return int the item's rank.
     * @example $redis->zRevRank('key', 'one');
     */
    public function zRevRank($key, $member)
    {
        return $this->getConn()->zRevRank($key, $member);
    }

    /**
     * Creates an intersection of sorted sets given in second argument. The result of the union will be stored in the sorted set defined by the first argument. The third optionnel argument defines weights to apply to the sorted sets in input. In this case, the weights will be multiplied by the score of each element in the sorted set before applying the aggregation. The forth argument defines the AGGREGATE option which specify how the results of the union are aggregated.
     * @param string $keyOutput
     * @param array $arrayZSetKeys
     * @param array $arrayWeights
     * @return int The number of values in the new sorted set.
     * @example $redis->zInter('ko3', array('k1', 'k2'), array(5, 1));
     */
    public function zInter($keyOutput, $arrayZSetKeys, $arrayWeights)
    {
        return $this->getConn()->zInter($keyOutput, $arrayZSetKeys, $arrayWeights);
    }

    /**
     * Creates an union of sorted sets given in second argument. The result of the union will be stored in the sorted set defined by the first argument. The third optionnel argument defines weights to apply to the sorted sets in input. In this case, the weights will be multiplied by the score of each element in the sorted set before applying the aggregation. The forth argument defines the AGGREGATE option which specify how the results of the union are aggregated.
     * @param string $keyOutput
     * @param array $arrayZSetKeys
     * @param array $arrayWeights
     * @return int The number of values in the new sorted set.
     */
    public function zUnion($keyOutput, $arrayZSetKeys, $arrayWeights)
    {
        return $this->getConn()->zUnion($keyOutput, $arrayZSetKeys, $arrayWeights);
    }

    /**
     * Increments the score of a member from a sorted set by a given amount.
     * @param string $key
     * @param float $value (double) value that will be added to the member's score
     * @param string $member
     * @return float the new value
     */
    public function zIncrBy($key, $value, $member)
    {
        return $this->getConn()->zIncrBy($key, $value, $member);
    }

    /**
     * Sets an expiration date (a timestamp) on an item.
     * @param string $key The key that will disappear.
     * @param int $timestamp Unix timestamp. The key's date of death, in seconds from Epoch time.
     * @return bool TRUE in case of success, FALSE in case of failure.
     */
    public function expireAt($key, $timestamp)
    {
        return $this->getConn()->expireAt($key, $timestamp);
    }

    /**
     * Gets a value from the hash stored at key. If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     * @param string $key
     * @param string $hashKey
     * @return string/bool STRING The value, if the command executed successfully, BOOL FALSE in case of failure
     * @example $redis->hGet('h', 'key1');
     */
    public function hGet($key, $hashKey)
    {
        return $this->getConn()->hGet($key, $hashKey);
    }

    /**
     * Adds a value to the hash stored at key.
     * @param string $key
     * @param string $hashKey
     * @param string $value
     * @return int 1 if value didn't exist and was added successfully, 0 if the value was already present and was replaced, FALSE if there was an error.
     * @example $redis->hSet('h', 'key1', 'hello');
     */
    public function hSet($key, $hashKey, $value)
    {
        return $this->getConn()->hSet($key, $hashKey, $value);
    }

    /**
     * Adds a value to the hash stored at key only if this field isn't already in the hash.
     * @param string $key
     * @param string $hashKey
     * @param string $value
     * @return bool TRUE if the field was set, FALSE if it was already present.
     * @example $redis->hSetNx('h', 'key1', 'hello');
     */
    public function hSetNx($key, $hashKey, $value)
    {
        return $this->getConn()->hSetNx($key, $hashKey, $value);
    }

    /**
     * Removes a value from the hash stored at key. If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     * @param string $key
     * @param string $hashKey
     * @return bool TRUE in case of success, FALSE in case of failure.
     */
    public function hDel($key, $hashKey)
    {
        return $this->getConn()->hDel($key, $hashKey);
    }

    /**
     * Returns the length of a hash, in number of items
     * @param string $key
     * @return int LONG the number of items in a hash, FALSE if the key doesn't exist or isn't a hash.
     * @example $redis->hLen('h');
     */
    public function hLen($key)
    {
        return $this->getConn()->hLen($key);
    }

    /**
     * Returns the keys in a hash, as an array of strings.
     * @param string $key
     * @return array An array of elements, the keys of the hash. This works like PHP's array_keys().
     * @example $redis->hKeys('h');
     */
    public function hKeys($key)
    {
        return $this->getConn()->hKeys($key);
    }

    /**
     * Returns the values in a hash, as an array of strings.
     * @param string $key
     * @return array An array of elements, the values of the hash. This works like PHP's array_values().
     * @example $redis->hVals('h');
     */
    public function hVals($key)
    {
        return $this->getConn()->hVals($key);
    }

    /**
     * Returns the whole hash, as an array of strings indexed by strings.
     * @param string $key
     * @return array An array of elements, the contents of the hash.
     * @example $redis->hGetAll('h');
     */
    public function hGetAll($key)
    {
        return $this->getConn()->hGetAll($key);
    }

    /**
     * Verify if the specified member exists in a key.
     * @param string $key
     * @param string $memberKey
     * @return bool If the member exists in the hash table, return TRUE, otherwise return FALSE.
     * @example $redis->hExists('h', 'a');
     */
    public function hExists($key, $memberKey)
    {
        return $this->getConn()->hExists($key, $memberKey);
    }

    /**
     * Increments the value of a member from a hash by a given amount.
     * @param string $key
     * @param string $memeber
     * @param int $value value that will be added to the member's value
     * @return int the new value
     * @example $redis->hIncrBy('h', 'x', 2);
     */
    public function hIncrBy($key, $memeber, $value)
    {
        return $this->getConn()->hIncrBy($key, $memeber, $value);
    }

    /**
     * Fills in a whole hash. Non-string values are converted to string, using the standard (string) cast. NULL values are stored as empty strings.
     * @param string $key
     * @param array $members key → value array
     * @return bool
     * @example $redis->hMset('user:1', array('name' => 'Joe', 'salary' => 2000));
     */
    public function hMset($key, $members)
    {
        return $this->getConn()->hMset($key, $members);
    }

    /**
     * Retirieve the values associated to the specified fields in the hash.
     * @param string $key
     * @param array $memberKeys
     * @return array An array of elements, the values of the specified fields in the hash, with the hash keys as array keys.
     * @example $redis->hMget('h', array('field1', 'field2'));
     */
    public function hMget($key, $memberKeys)
    {
        return $this->getConn()->hMget($key, $memberKeys);
    }

    /**
     * Enter transactional mode.
     * @param int $mode Redis::MULTI/Redis::PIPELINE
     * @return Redis returns the Redis instance and enters multi-mode. Once in multi-mode, all subsequent method calls return the same object until exec() is called.
     */
    public function multi($mode = Redis::MULTI)
    {
        return $this->getConn()->multi($mode);
    }

    /**
     * @return true
     */
    public function discard()
    {
        return $this->getConn()->discard();
    }

    /**
     * @return array
     */
    public function exec()
    {
        return $this->getConn()->exec();
    }

    /**
     * Watches a key for modifications by another client. If the key is modified between WATCH and EXEC, the MULTI/EXEC transaction will fail (return FALSE). unwatch cancels all the watching of all keys by this client.
     * @param string $key
     * @return true
     */
    public function watch($key)
    {
        return $this->getConn()->watch($key);
    }

    /**
     * Watches a key for modifications by another client. If the key is modified between WATCH and EXEC, the MULTI/EXEC transaction will fail (return FALSE). unwatch cancels all the watching of all keys by this client.
     * @return true
     */
    public function unwatch()
    {
        return $this->getConn()->unwatch();
    }

    /**
     * Publish messages to channels. Warning: this function will probably change in the future.
     * @param string $channel a channel to publish to
     * @param string $message
     */
    public function publish($channel, $message)
    {
        return $this->getConn()->publish($channel, $message);
    }

    /**
     * Subscribe to channels. Warning: this function will probably change in the future.
     * @param array $channels an array of channels to subscribe to
     * @param string/array $callback either a string or an array($instance, 'method_name'). The callback function receives 3 parameters: the redis instance, the channel name, and the message.
     * @example $redis->subscribe(array('chan-1', 'chan-2', 'chan-3'), 'f'); // subscribe to 3 chans
     */
    public function subscribe($channels, $callback)
    {
        return $this->getConn()->subscribe($channels, $callback);
    }

    public function unsubscribe()
    {

    }

    /**
     * Return the length of the List value at key
     * @param string $key
     * @return int
     */
    public function lLen($key)
    {
        return $this->getConn()->lLen($key);
    }

    /**
     * Sets an expiration date (a timeout) on an item.
     * @param string $key The key that will disappear.
     * @param int $ttl The key's remaining Time To Live, in seconds.
     * @return bool TRUE in case of success, FALSE in case of failure.
     */
    public function expire($key, $ttl)
    {
        return $this->getConn()->expire($key, $ttl);
    }

    public function zunionstore()
    {

    }

    public function zinterstore()
    {

    }

    /**
     * Deletes a specified member from the ordered set.
     * @param string $key
     * @param string $member
     * @return int 1 on success, 0 on failure.
     * @example $redis->zRemove('key', 'val2');
     */
    public function zRemove($key, $member)
    {
        return $this->getConn()->zRemove($key, $member);
    }

    public function zRemoveRangeByScore()
    {

    }

    /**
     * Returns the cardinality of an ordered set.
     * @param string $key
     * @return int the set's cardinality
     */
    public function zSize($key)
    {
        return $this->getConn()->zSize($key);
    }
}