<?php
/**
 * PDOHelper类
 * @link http://php.net/manual/en/book.pdo.php
 * @author mirrorgdit@163.com
 * @license  https://github.com/mirrorgdit/php-helper/blob/master/README.md
 */
namespace mirrorgdit\helper;
use mirrorgdit\helper\UserException;
/**
 * Class PDOHelper
 * @package mirrorgdit\helper
 */
class PDOHelper {
    /**
     * 配置数组
     * @var array
     */
    private $_configArr = array();
    /**
     * PDO实例
     * @var PDO
     */
    private $_pdo;
    /**
     * 事务是否开启
     */
    private $_transStarted = false;

    /**
     * 构造函数
     * @param array $configArr 配置数组array($host, $port, $username, $password, $dbname, $charset)
     * @param string $type
     */
    public function __construct($configArr, $type = 'mysql') {
        $this->_configArr = array(
            'type' => $type, // 数据库类型
            'host' => $configArr[0], // 主机
            'port' => $configArr[1], // 端口
            'username' => $configArr[2], // 用户名
            'password' => $configArr[3], // 密码
            'dbname' => $configArr[4], // 数据库
            'charset' => $configArr[5], // 字符集(注意MySQL当中utf-8需写成utf8)
        );
    }

    /**
     * 建立到数据库服务器的连接
     */
    public function connect() {
        try {
            $this->_pdo = new \PDO(
                $this->_configArr['type'] . ':host=' . $this->_configArr['host'] . ';port=' . $this->_configArr['port'] . ';dbname=' . $this->_configArr['dbname'],
                $this->_configArr['username'],
                $this->_configArr['password'],
                array(
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                )
            );
        } catch (\PDOException $e) {
            $this->error('PDO::__construct', $e->getMessage());
        }

        // 设置字符集
        if ($this->_configArr['charset']) {
            $this->query('SET NAMES ' . $this->_configArr['charset']);
        }
    }

    /**
     * 选择指定数据库作为活动数据库
     * @param string $databaseName
     * @return true
     */
    public function selectDB($databaseName) {
        return $this->query('USE ' . $databaseName);
    }

    /**
     * 执行指定查询语句，并返回结果集
     * @param string $sql SQL语句
     * @return PDOStatement
     */
    public function query($sql) {
        // 检查连接是否建立
        if (!isset($this->_pdo)) {
            $this->connect();
        }

        // 检查是否需要添加到事务队列当中
        if (!$this->_transStarted ) {
            $this->start();
//            App::joinInTrans($this);
        }

        // 执行查询
        try {
            return $this->_pdo->query($sql);
        } catch (\PDOException $e) {
            $this->error('PDO::query', $sql);
        }
    }

    /**
     * 从指定PDOStatement对象中获取一行作为关联数组、数字数组或二者兼有
     * @param PDOStatement $stmt PDOStatement对象
     * @param int $fetchStyle 结果类型(PDO::FETCH_ASSOC/PDO::FETCH_NUM/PDO::FETCH_BOTH)
     * @return array
     */
    public function fetchArray($stmt, $fetchStyle = PDO::FETCH_BOTH) {
        return $stmt->fetch($fetchStyle);
    }

    /**
     * 从指定PDOStatement对象中获取一行作为关联数组
     * @param PDOStatement $stmt PDOStatement对象
     * @return array
     */
    public function fetchAssoc($stmt) {
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 从指定PDOStatement对象中获取一行作为枚举数组
     * @param PDOStatement $stmt PDOStatement对象
     * @return array
     */
    public function fetchRow($stmt) {
        return $stmt->fetch(\PDO::FETCH_NUM);
    }

    /**
     * 获取查询记录的条数
     * @param string $sql SQL语句
     * @return string
     */
    public function count($sql) {
        $stmt = $this->query($sql);
        $row = $this->fetchRow($stmt);
        return $row[0];
    }

    /**
     * 执行指定查询语句并从结果集中获取一条记录,以一维数组形式返回
     * @param string $sql SQL语句
     * @param int $fetchStyle 结果类型(PDO::FETCH_ASSOC/PDO::FETCH_NUM/PDO::FETCH_BOTH)
     * @return array
     */
    public function getOne($sql, $fetchStyle = \PDO::FETCH_ASSOC) {
        $stmt = $this->query($sql);
        return $stmt->fetch($fetchStyle);
    }

    /**
     * 执行指定查询语句并从结果集中获取所有记录，以二维数组形式返回
     * @param string $sql SQL语句
     * @param int $fetchStyle 结果类型(PDO::FETCH_ASSOC/PDO::FETCH_NUM/PDO::FETCH_BOTH)
     * @return array
     */
    public function getAll($sql, $fetchStyle = \PDO::FETCH_ASSOC) {
        $stmt = $this->query($sql);
        return $stmt->fetchAll($fetchStyle);
    }

    /**
     * 返回给定的连接中上一步INSERT查询中产生的AUTO_INCREMENT的ID号;如果上一查询没有产生AUTO_INCREMENT的值,则返回0
     * @return int
     */
    public function insertID() {
        return $this->_pdo ? intval($this->_pdo->lastInsertId()) : 0;
    }

    /**
     * 开启事务
     */
    public function start() {
        if (!$this->_transStarted) {
            $this->_transStarted = true; // 这一行要放前面,否则会进入死循环
            $this->query('START TRANSACTION');
        }
    }

    /**
     * 提交事务
     */
    public function commit() {
        if ($this->_transStarted) {
            $this->query('COMMIT');
            $this->_transStarted = false;
        }
    }

    /**
     * 事务回滚
     */
    public function rollback() {
        if ($this->_transStarted) {
            $this->query('ROLLBACK');
            $this->_transStarted = false;
        }
    }

    /**
     * 错误处理函数
     * @param string $where
     * @param string $sql
     */
    public function error($where, $sql = '') {
        $errorInfoArr = $this->_pdo ? $this->_pdo->errorInfo() : array('', 'PDO Error', 0);
        throw new UserException(UserException::ERROR_PDO, "Where:%s\nSQL:%s\nError:%s\nErrno:%d", $where, $sql, $errorInfoArr[2], $errorInfoArr[1]);
    }
}