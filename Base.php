<?php
namespace xiaobiji\model;
use PDO;
use PDOException;
class Base{
    //1.设置私有属性table ， 2.用来存储表名
    private $table;
    //1.设置静态私有属性$pdo, 2.用来存储链接数据库时返回的对象
    private static $pdo;
    private $where = '';
    //1.设置构造函数，2.让一实例化类就可以执行此方法，用来一进入页面就链接PDO。
    //1.里面参数为自动接收在Model里传入的参数值
    public function __construct($config,$table){
        //1.调用connect方法，2.设置自动执行函数 进行连接pdo
        $this->connect($config);
        //1.将调用此方法的表名存储 ；2就是要显示的表格内容
        $this->table = $table;
//        p($config);
    }
    //1.设置connect方法，2.连接数据库
    private function connect($config)
    {
//        p($config);
        //1.判断￥$pdo是否为空；2.如果不为空则说明已经链接PDO，那么直接返回，不要重复连接
        if(!is_null(self::$pdo)) return;
        //1.写try来捕捉异常错误；
        try {
            //1.组合连接PDO的主机地址用户名和密码；2.用来来连接PDO
            $dsn = "mysql:host=" . $config['db_host'] . ";dbname=" . $config['db_name'];
            $user = $config["db_user"];
            $password = $config["db_password"];
            $pdo = new PDO($dsn, $user, $password);
//            p($pdo);
//            $pdo = new PDO("mysql:host=127.0.0.1;")
            //1.设置捕错误类型；2.这样不是异常错误也能被捕捉到
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //1.设置字符集 2.根据使用版本显示编码
            $pdo->query("SET NAMES " .$config["db_charset"]);
            //1.将得到的PDO对象存储到静态属性；2.这样就可以在全局调用$pdo，用作结果集的使用
            self::$pdo = $pdo;
            //1.设置捕捉异常错误；2.将连接pdo的错误抓取
        } catch (PDOException $e) {
            //1.输出错误，2.并且结束代码运行
            exit($e->getMessage());
        }
    }
    //1.设置where方法；2.用作sql语句中的查询条件
    public function where($where){
        //1.将属性where赋值，2.使用时可以直接写出where后面加参数得到条件
        $this->where = "WHERE {$where}";
        //1.将对象返回；2.为了能链式调用
        return $this;
    }
    //1.设置get方法；2.能获取sql语句中表中的提取的数据
    public function get(){
        //1.设置sql语句，2.用来查询表中的所有数据
        $sql = "SELECT * FROM {$this->table}";
//        p($sql);
        //1.执行q（）方法，并将结果返回；2.调用时可以得到方法的结果集
        return $this->q($sql);
    }
    //1.设置q函数，2.调用时可以将sql语句中的结果显示出来，有结果集
    public function q($sql){
        try{
            //1.获得连接PDO得到的结果集，2.直接已经赋值的$PDO；
            $result = self::$pdo->query($sql);
//            p($sql);
            //1.提取数据，用得到的结果集提取想要的数组
            $data = $result->fetchAll(PDO::FETCH_ASSOC);
//            p($data);
            //1.将得到的结果返回；2.这样，调用此方法可以得到查询之后的数据
            return $data;
            //1.设置捕捉异常错误；2.将连接pdo的错误抓取
        }catch (PDOException $e){
            //1.输出错误，2.并且结束代码运行
            exit($e->getMessage());
        }
    }
    //1.设置q函数，2.调用时可以执行sql语句，无结果集
    public function e($sql){
//        p($sql);//输出sql语句
        try{
            //1.将exec执行的sql语句返回，2.因为没有结果返回所以直接return就行
            return self::$pdo->exec($sql);
            //1.设置捕捉异常错误；2.将连接pdo的错误抓取
        }catch (\PDOException $e){
            //1.设置捕捉异常错误；2.将连接pdo的错误抓取
            exit($e->getMessage());
        }
    }
    //1.设置find方法，里面参数$pri;2.可以获取当前表主键
    public function find($pri){
        //1.调用getPri()方法将得到主键；2.存到$priFiled中
        $priFiled = $this->getPri();
        //1.用where方法查询要找的主键
        $this->where("{$priFiled}={$pri}");
        //1.设置查询的sql语句；2.可以将想要查询的主键数据查出来
        $sql = "SELECT * FROM {$this->table} {$this->where}";
//        echo $sql;
        //1.使用q方法来执行sql语句，并将得到数据存入$data;2.查询表中的数据
        $data = $this->q($sql);
        //1.获取得到数据中的内容；2.$data是一个二维数组
        $data = current($data);
        //1.将得到的数据存进￥data中，2.$data就是我们想要查到的数据
        $this->data = $data;
        //1.将对象返回；2.为了能链式调用
        return $this;
    }
    //1.设置findArray方法；2.获取find方法中得到的数据data
    public function findArray($pri){
        //1.执行find方法，2.将结果存进$obj
        $obj = $this->find($pri);
        //1.将得到的结果返回；2.调用此方法是得到数据
//        p($obj);
        return $obj->data;
    }
    //1.设置getPri方法；2.用来根据表结构来查询主键
    public function getPri(){
        //1.查询表结构并将值传给￥desc
        $desc = $this->q("DESC {$this->table}");
        //p($desc);
        //1.设置$priField变量默认为空 2.用来存储得到的数据;
        $v['Field'] = '';
        //1.将得到的数据遍历循环；2.就能得到含有表结构的数组，根据下标找到主键
        foreach ($desc as $v){
//            p($v);
            //1.根据得到的数据下标找到，并且判断两者是否相等
            if($v['Key'] =='PRI'){
                //1.将$v['Field']存入 $v['Field']中
                $priField = $v['Field'];
                //结束代码运行
                break;
            }
        }
        //1.将得到的数据返回2.调用此方法可以接收数据
        return $priField;
    }
    //1.摄像count方法；2.用来获取列表某个部分的总数
    public function count($field='*'){
        //1.设置sql语句并且执行它
        $sql = "SELECT count{$field} as c FROM {$this->table} {$this->where}";
        //1.用q函数将sql语句执行；2.达到相要的总数结果
        $data = $this->q($sql);
//        p($data);
        //1.数据返回 ；2.调用时可以获取数据
        return $data[0]['c'];

    }
}