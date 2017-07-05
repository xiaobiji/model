<?php
//1.设置命名空间；2.让此文件能通过composer来进行自动载入本文件
namespace xiaobiji\model;
//1.创建Model类；2.可以获得表单名称和链接PDO的参数
class Model
{
    //1.设置私有静态属性$config。2.用来存储PDO中的参数
    private static $config;
//    private static $pdo;
    //1.设置构造函数，找不到方法时执行；2用于普通调用
    public function __call($name, $arguments)
    {
        //1.调用自己parseAction方法，并将结果输出；2.调用时可以将数据传出
        return self::parseAction($name, $arguments);

    }
    //1.设置构造函数，找不到方法时执行；2用于静态调用
    public static function __callStatic($name, $arguments)
    {
//        p($name);
//        p(self::$config);
        //1.调用自己parseAction方法，并将结果输出；2.调用时可以将数据传出
        return self::parseAction($name, $arguments);
//

    }
    //1.设置parseAction方法；2.用来获得链接数据库参数和数据库中调用此方法的表名
    private static function parseAction($name, $arguments)
    {
        //1.使用get_called_class()可以获得调用此方法的类名;2.得到一个路径得到表名
        $table = get_called_class();//获得：system\model\Article
        //1.利用函数将最终的路径得出，就是将表名取出；2.因为要设置目录所以将字母小写
        $table = strtolower(ltrim(strrchr($table, '\\'), '\\'));
//        p($table);
        //1.利用回调函数实力化Base类，2.这样可以让base中构造函数方法接受到穿的参数
        return call_user_func_array([new Base(self::$config, $table), $name], $arguments);
    }
    //1.设置setConfig方法；2.将传入的值赋值到本类的属性中，
    public static function setConfig($config){
        //1.将传入的值赋值到本类的属性中 2.可以全局使用
        self::$config = $config;
    }

}