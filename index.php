<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/10
 * Time: 10:19
 * API 接口处理入口文件
 */
//自动加载函数
require_once(__DIR__ . '/autoload.php');
spl_autoload_register(["Autoload", "loadFile"]);

$config = require(__DIR__ . '/params.php');

//全局常量
//require_once(__DIR__ . '/commonParams.php');
CONST METHOD_GET = 'GET';//获取资源-返回要获取的单个或多个资源
CONST METHOD_POST = 'POST';//新建资源-返回新建的资源
CONST METHOD_PUT = 'PUT';//更新资源-返回更新后的资源
CONST METHOD_DELETE = 'DELETE';//删除资源-返回空文档

//返回结果信息及状态码
CONST SUCCESS_MSG_GET = '获取成功';
CONST SUCCESS_CODE_GET = 200;
CONST SUCCESS_MSG_CREATE = '添加成功';
CONST SUCCESS_CODE_CREATE = 201;
CONST SUCCESS_MSG_UPDATE = '更新成功';
CONST SUCCESS_CODE_UPDATE = 201;
CONST SUCCESS_MSG_DELETE = '删除成功';
CONST SUCCESS_CODE_DELETE = 204;
CONST COMMON_ERROR_CODE = 299;


class Restful{
    /**
     * 请求方法
     * @var string
     */
    private $_requestMethod;

    /**
     * 允许的请求方式
     * @var array
     */
    private $_allowRequestMethod = ['GET', 'POST', 'PUT', 'DELETE'];

    /**
     * 请求方式对应的操作方法前缀
     * @var array
     */
    private $methodAction = [
        METHOD_GET          => 'get',
        METHOD_POST         => 'create',
        METHOD_PUT          => 'update',
        METHOD_DELETE       => 'delete',
    ];

    /**
     * 请求的资源名称
     * @var string
     */
    private $_resourceName;

    /**
     * 请求的资源操作类型
     * @var string
     */
    private $_actionName;

    /**
     * 允许请求的资源列表
     * @var array
     */
    private $_allowResources = ["article",];

    public function __construct(){
        $this->setRequestMethod();
    }

    /**
     * 设置当前处理请求的请求方式
     */
    private function setRequestMethod(){
        $this->_requestMethod = $_SERVER['REQUEST_METHOD'];
        if (!in_array($this->_requestMethod, $this->_allowRequestMethod)) {
            //请求方式不在允许列表中
            throw new Exception('不允许的请求方法', 401);
        }
    }

    public function run(){
        try{
            //获取请求参数
            $reqData = $this->_resolveUrl();

            $respData = '';
            global $config;
            switch($this->_requestMethod){
                case METHOD_GET:
                    /**
                     * 获取文章
                     */

                    //获取数据库连接句柄
                    $dbInst = DB::getDBLink($config['db']['host'], $config['db']['username'], $config['db']['userpwd']);

                    $resInst = new $this->_resourceName($dbInst);
                    $resMethod = $this->_actionName;
                    $respData = $resInst->$resMethod($reqData);
                    break;
                case METHOD_POST:
                    /**
                     * 添加文章
                     */

                    //获取数据库连接句柄
                    $dbInst = DB::getDBLink($config['db']['host'], $config['db']['username'], $config['db']['userpwd']);

                    $resInst = new $this->_resourceName($dbInst);
                    $resMethod = $this->_actionName;
                    $respData = $resInst->$resMethod($reqData);
                    break;
                case METHOD_PUT:
                    /**
                     * 修改文章
                     */

                    //获取数据库连接句柄
                    $dbInst = DB::getDBLink($config['db']['host'], $config['db']['username'], $config['db']['userpwd']);

                    $resInst = new $this->_resourceName($dbInst);
                    $resMethod = $this->_actionName;
                    $respData = $resInst->$resMethod($reqData);
                    break;
                case METHOD_DELETE:
                    /**
                     * 删除文章
                     */
                    /**
                     * 暂时无法通过非url部分获取DELETE请求体， 因此将请求参数放于url中
                     */
                    //获取数据库连接句柄
                    $dbInst = DB::getDBLink($config['db']['host'], $config['db']['username'], $config['db']['userpwd']);

                    $resInst = new $this->_resourceName($dbInst);
                    $resMethod = $this->_actionName;
                    $respData = $resInst->$resMethod($reqData);
                    break;
            }
            header("Content-Type", "application/json;charset=UTF-8");
            echo $respData;
            die;
        } catch(Exception $e){
            echo "错误代码: " . $e->getCode() . "    ";
            echo $e->getMessage() ;
            die;
        }
    }

    /**
     * 根据请求方式解析需要传递的参数
     */
    private function _resolveUrl(){
        //从 SERVER 信息中判断当前需要请求哪个接口
        $serverPar = $_SERVER;

        //获取除host外的链接 ** http://learngit.me/api/index.php/articles?name=donghan
        $requestURI = trim($serverPar['DOCUMENT_URI']);//host之后不含querystring的 字符串,如"/api/index.php/articles"

        // 以 '/' 分隔该链接，得到请求的资源名称及参数
        $routeArr = explode('/', $requestURI);
        $resourceName = array_pop($routeArr);//需要操作的资源名称，复数形式

        if (trim($resourceName) == 'index.php') {
            throw new Exception('请指定所请求的接口', COMMON_ERROR_CODE);
        } else {
            //判断资源名称是否包含 's'
            if (false !== stripos($resourceName, 's')) {
                //首先移除资源名称右侧的复数 s,然后首字符转为大写
                $this->_resourceName = ucFirst(rtrim($resourceName, 's'));
            } else {
                //将首字符转为大写
                $this->_resourceName = ucFirst($resourceName);
            }
        }

        $reqData = [];//请求参数
        switch($this->_requestMethod){
            case METHOD_GET:
                /**
                 * 查询操作
                 */
                //设置需要调用的资源操作方法
                $this->_actionName = $this->methodAction[METHOD_GET] . $this->_resourceName;

                $queryString = trim($_SERVER['QUERY_STRING']);
                //需要传递给操作方法的查询参数 array
                //查询字符串中的键值对数组
                $queryParam = [];
                if (!empty($queryString) && count($queryString) > 0) {
                    //请求链接中包含参数，根据参数查找资源
                    $queryParam = explode('&', $queryString);
                }

                //将查询字符串依次放入参数数组中
                foreach($queryParam as $v){
                    list($queryKey, $queryVal) = explode('=', $v);
                    $reqData[$queryKey] = $queryVal;
                }
                break;
            case METHOD_POST:
                /**
                 * 添加操作
                 */
                //设置需要调用的资源操作方法
                $this->_actionName = $this->methodAction[METHOD_POST] . $this->_resourceName;
                //获取参数
                //$postData = isset($_POST["data_info"]) ? json_decode($_POST["data_info"], true) : [];
                $rawData = json_decode(file_get_contents("php://input"), true);
                $reqData = isset($rawData["data_info"]) ? $rawData["data_info"] : [];

                if (!preg_match("/[a-zA-z]+/", $resourceName)) {
                    throw new Exception('接口地址错误', COMMON_ERROR_CODE);
                    //$respData = json_encode(['code' => COMMON_ERROR_CODE,'msg' => '接口地址错误'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case METHOD_PUT:
                /**
                 * 修改操作
                 */
                //设置需要调用的资源操作方法
                $this->_actionName = $this->methodAction[METHOD_PUT] . $this->_resourceName;

                //获取修改 参数

                //$postData = isset($_POST["data_info"]) ? json_decode($_POST["data_info"], true) : [];
                $rawData = json_decode(file_get_contents("php://input"), true);
                $reqData = isset($rawData["data_info"]) ? $rawData["data_info"] : [];

                if (!preg_match("/[a-zA-z_-]+/", $resourceName)) {
                    throw new Exception('接口地址错误', COMMON_ERROR_CODE);
                    //$respData = json_encode(['code' => COMMON_ERROR_CODE,'msg' => '接口地址错误'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case METHOD_DELETE:
                /**
                 * 删除操作
                 */
                //设置需要调用的资源操作方法
                $this->_actionName = $this->methodAction[METHOD_DELETE] . $this->_resourceName;

                /**
                 * 暂时无法通过非url部分获取DELETE请求体， 因此将请求参数放于url中
                 */

                //获取文章删除参数
                /*$rawData = json_decode(file_get_contents("php://input"), true);
                $postData = isset($rawData["data_info"]) ? $rawData["data_info"] : [];*/

                if (!preg_match("/[a-zA-z]+/", $resourceName)) {
                    throw new Exception('接口地址错误', COMMON_ERROR_CODE);
                    //$respData = json_encode(['code' => COMMON_ERROR_CODE,'msg' => '接口地址错误'], JSON_UNESCAPED_UNICODE);
                }

                $queryString = trim($_SERVER['QUERY_STRING']);
                //需要传递给操作方法的查询参数 array
                //查询字符串中的键值对数组
                $queryParam = [];
                if (!empty($queryString) && count($queryString) > 0) {
                    //请求链接中包含参数，根据参数查找资源
                    $queryParam = explode('&', $queryString);
                }

                //将查询字符串依次放入参数数组中
                foreach($queryParam as $v){
                    list($queryKey, $queryVal) = explode('=', $v);
                    $reqData[$queryKey] = $queryVal;
                }
                break;
        }
        return $reqData;
    }

}
$restful = new Restful();
$restful->run();