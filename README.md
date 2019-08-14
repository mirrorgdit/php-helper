# php-helper
```
PHP各种辅助类、 CMEM、Memcache、Memcached、Redis、Mysql、Mysqli、PDO
```

## 安装

```
composer require mirrorgdit/php-helper

```
## PDO使用
```
require_once './vendor/autoload.php';
use mirrorgdit\helper\PDOHelper;

//配置数组
//$configArr = [$host, $port, $username, $password, $dbname, $charset];
$configArr = ['localhost','3306','root','123456','mysql','utf8'];
$db = new PDOHelper($configArr);
$res = $db->getAll("select Host,User from user");
var_dump($res);
```
## Memcached使用
```
require_once './vendor/autoload.php';
use mirrorgdit\helper\MemcachedHelper;

//Redis使用
$mem = new MemcachedHelper(['127.0.0.1', '6379']);
$mem->set('key', 'value', $expiration = 0);//存储
echo $mem->get('key');//获取
```
## Redis使用
```
require_once './vendor/autoload.php';
use mirrorgdit\helper\RedisHelper;

//Redis使用
$redis = new RedisHelper(['127.0.0.1', '6379']);
$redis->set('key', 'value');//存储
echo $redis->get('key');//获取
```
### 问题反馈

在使用中有任何问题，欢迎反馈给我，可以用以下联系方式跟我交流

mirrorgdit@163.com

