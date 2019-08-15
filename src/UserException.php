<?php

/**
 * @link     https://github.com/mirrorgdit/php-helper
 * @document https://github.com/mirrorgdit/php-helper
 * @contact  mirrorgdit@163.com
 * @license  https://github.com/mirrorgdit/php-helper/blob/master/README.md
 */

namespace mirrorgdit\helper;
/**
 * 用户异常类
 * Class UserException
 * @package mirrorgdit\helper
 */
class UserException extends \Exception
{
    /**
     * 系统错误
     * @var int
     */
    const ERROR_SYSTEM = 100;
    /**
     * 运行时错误
     * @var int
     */
    const ERROR_RUNTIME_ERROR = 101;
    /**
     * SESSION过期(未登录或登录超时)
     * @var int
     */
    const ERROR_SESSION_EXPIRED = 102;
    /**
     * 服务繁忙
     * @var int
     */
    const ERROR_SERVER_BUSY = 103;
    /**
     * 帐号被冻结
     * @var int
     */
    const ERROR_ACCOUNT_BLOCKED = 104;
    /**
     * 帐号重复登录
     * @var int
     */
    const ERROR_ACCOUNT_MULTI_LOGIN = 105;
    /**
     * 扩展未加载
     * @var int
     */
    const ERROR_EXTENSION_NOT_LOADED = 106;
    /**
     * memcache/memcached错误
     * @var int
     */
    const ERROR_MEMCACHE = 107;
    const ERROR_MEMCACHED = 108;
    /**
     * MySQL错误
     * @var int
     */
    const ERROR_MYSQL = 109;
    const ERROR_MYSQLI = 110;
    const ERROR_PDO = 111;
    /**
     * Redis错误
     * @var int
     */
    const ERROR_REDIS = 112;
    /**
     * CURL ERROR
     * @link http://curl.haxx.se/libcurl/c/libcurl-errors.html
     * @var int
     */
    const ERROR_CURL = 113;
    /**
     * SNS 配置数据错误
     * @var int
     */
    const ERROR_SNS_INVALID_CONFIG = 114;
    /**
     * SNS 参数错误
     * @var int
     */
    const ERROR_SNS_INVALID_PARAM = 115;
    /**
     * SNS session 过期
     * @var int
     */
    const ERROR_SNS_SESSION_EXPIRED = 116;
    /**
     * SNS API BAD RETURN
     * @var int
     */
    const ERROR_SNS_API_BAD_RETURN = 117;
    /**
     * SNS API FAIL
     * @var int
     */
    const ERROR_SNS_API_FAIL = 118;
    /**
     * 插件调用错误
     * @var int
     */
    const ERROR_PLUGIN = 119;
    /**
     * 事务日志错误
     * @var int
     */
    const ERROR_TRANS_LOG = 120;
    /**
     * 用户自定义错误
     * @var int
     */
    const ERROR_USER = 1000;

    /**
     * 构造函数
     * @param int $code 错误编号
     * @param string $format 错误消息格式字符串
     * @param mixed $arg1 ,$arg2,... 可变参数(格式参数)
     */
    public function __construct($code = -1, $format = 'Undefined error!')
    {
        // 获取参数数组
        $args = func_get_args();
        // 移去第一个参数
        array_shift($args);
        // 移去第二个参数
        array_shift($args);
        if (!empty($args)) {
            $msg = vsprintf($format, $args);
        } else {
            $msg = $format;
        }
        // 调用父类的构造函数
        parent::__construct($msg, $code);
    }
}