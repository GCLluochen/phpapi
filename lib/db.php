<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/10
 * Time: 10:51
 */

class DB{
    private static $_db;//数据库连接句柄
    private $_host;     //数据库连接 host
    //private $_dbName;
    private $_userName; //数据库连接用户名
    private $_userPwd;  //数据库连接用户的密码
    public $_tblprefix;//表前缀

    /**
     * 初始化 DB 操作句柄，
     * DB constructor.
     * @param $host ** mysql:host=xxx.xx.xx.xxx;dbname=name;
     * @param $db
     * @param $userName
     * @param $userPwd
     */

    private function __construct(){

    }

    public static function getDBLink($host, $userName, $userPwd){
        if (!self::$_db) {
            self::$_db = new PDO($host, $userName, $userPwd);
            self::$_db->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
        }
        return self::$_db;
    }

    /**
     * 断开数据库连接
     * @param DB $inst 连接句柄
     */
    public static function breakDBLink(DB &$inst){
        $inst = null;
    }


}