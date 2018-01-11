<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/10
 * Time: 10:28
 */

/**
 * 自动加载处理类
 * Class Autoload
 */
class Autoload{
    public static function loadFile($filename){
        $filePath = __DIR__ .'/lib/'. $filename . '.php';
        $filePath2 = __DIR__ . '/lib/'.strtolower($filename) . '.php';;
        $filePath3 = __DIR__ . '/lib/'.strtoupper($filename) . '.php';;

        //判断当前路径下是否有该文件
        if (file_exists($filePath)) {
            include $filePath;
        } else if(file_exists($filePath2)){
            include $filePath2;
        } else if(file_exists($filePath3)){
            include $filePath3;
        } else {
            throw new Exception('ERROR: Class ' . $filename . ' doesn\'t exists');
            exit();
        }
    }
}

