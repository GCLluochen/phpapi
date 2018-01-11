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
        $requestURI = trim($serverPar['REQUEST_URI']);
        // 以 '/' 分隔该链接，得到请求的资源名称及参数
        $routeArr = explode('/', $requestURI);
        $uriArr = array_pop($routeArr);

        if (trim($uriArr) == 'index.php') {
            throw new Exception('请指定所请求的接口', COMMON_ERROR_CODE);
        }
        $reqData = [];//请求参数
        switch($this->_requestMethod){
            case METHOD_GET:
                /**
                 * 查询操作
                 */
                $nameQuery = [];
                if (false !== stripos($uriArr, '?')) {
                    //请求链接中包含参数，根据参数查找资源
                    $nameQuery = explode('?', $uriArr);
                    if (false !== stripos($nameQuery[0], 's')) {
                        //首先移除资源名称右侧的复数 s,然后首字符转为大写
                        $this->_resourceName = ucFirst(rtrim($nameQuery[0], 's'));
                    } else {
                        //将首字符转为大写
                        $this->_resourceName = ucFirst($nameQuery[0]);
                    }
                } else {
                    //不包含参数，获取当前所需资源全部数据
                    if (false !== stripos($uriArr, 's')) {
                        //首先移除资源名称右侧的复数 s,然后首字符转为大写
                        $this->_resourceName = ucFirst(rtrim($uriArr, 's'));
                    }
                }
                //设置需要调用的资源操作方法
                $this->_actionName = $this->methodAction[METHOD_GET] . $this->_resourceName;

                //需要传递给操作方法的查询参数 数组
                //查询字符串中的键值对数组
                $queryParam = [];
                if (!empty($nameQuery) && false !== stripos($nameQuery[1], '&')) {
                    $queryParam = explode('&', $nameQuery[1]);
                } else if (!empty($nameQuery) && false === stripos($nameQuery[1], '&')){
                    $queryParam[] = $nameQuery[1];
                }
                //将查询参数依次放入数组中
                foreach($queryParam as $v){
                    list($queryKey, $queryVal) = explode('=', $v);
                    $reqData[$queryKey] = $queryVal;
                }
                break;
            case METHOD_POST:
                //获取参数
                //$postData = isset($_POST["data_info"]) ? json_decode($_POST["data_info"], true) : [];
                $rawData = json_decode(file_get_contents("php://input"), true);
                $reqData = isset($rawData["data_info"]) ? $rawData["data_info"] : [];

                if (false === stripos($uriArr, '?')) {
                    //不包含参数，获取当前所需资源全部数据
                    if (false !== stripos($uriArr, 's')) {
                        //首先移除资源名称右侧的复数 s,然后首字符转为大写
                        $this->_resourceName = ucFirst(rtrim($uriArr, 's'));
                    }
                    //设置需要调用的资源操作方法
                    $this->_actionName = $this->methodAction[METHOD_POST] . $this->_resourceName;
                } else {
                    throw new Exception('接口地址错误', COMMON_ERROR_CODE);
                    //$respData = json_encode(['code' => COMMON_ERROR_CODE,'msg' => '接口地址错误'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case METHOD_PUT:
                /**
                 * 修改操作
                 */

                //获取修改 参数

                //$postData = isset($_POST["data_info"]) ? json_decode($_POST["data_info"], true) : [];
                $rawData = json_decode(file_get_contents("php://input"), true);
                $reqData = isset($rawData["data_info"]) ? $rawData["data_info"] : [];
                if (false === stripos($uriArr, '?')) {
                    //不包含参数，获取当前所需资源全部数据
                    if (false !== stripos($uriArr, 's')) {
                        //首先移除资源名称右侧的复数 s,然后首字符转为大写
                        $this->_resourceName = ucFirst(rtrim($uriArr, 's'));
                    }
                    //设置需要调用的资源操作方法
                    $this->_actionName = $this->methodAction[METHOD_PUT] . $this->_resourceName;
                } else {
                    throw new Exception('接口地址错误', COMMON_ERROR_CODE);
                }
                break;
            case METHOD_DELETE:
                /**
                 * 删除操作
                 */
                /**
                 * 暂时无法通过非url部分获取DELETE请求体， 因此将请求参数放于url中
                 */

                //获取文章删除参数
                /*$rawData = json_decode(file_get_contents("php://input"), true);
                $postData = isset($rawData["data_info"]) ? $rawData["data_info"] : [];*/

                if (false !== stripos($uriArr, '?')) {
                    //请求链接中包含参数，解析参数
                    $nameQuery = explode('?', $uriArr);
                    if (false !== stripos($nameQuery[0], 's')) {
                        //首先移除资源名称右侧的复数 s,然后首字符转为大写
                        $this->_resourceName = ucFirst(rtrim($nameQuery[0], 's'));
                    } else {
                        //将首字符转为大写
                        $this->_resourceName = ucFirst($nameQuery[0]);
                    }

                    //查询字符串中的键值对数组
                    $queryParam = [];
                    if (!empty($nameQuery) && false !== stripos($nameQuery[1], '&')) {
                        $queryParam = explode('&', $nameQuery[1]);
                    } else if (empty($nameQuery)){
                        throw new Exception('缺少参数', COMMON_ERROR_CODE);
                    } else {
                        $queryParam[] = $nameQuery[1];
                    }
                    //将查询参数依次放入数组中
                    foreach($queryParam as $v){
                        list($queryKey, $queryVal) = explode('=', $v);
                        $reqData[$queryKey] = $queryVal;
                    }
                } else {
                    // TODO
                    //请求url中不含？  以 url/参数名/参数值形式传递
                    //请求URL 为 http://api路径/参数名/参数值格式(仅支持单个参数)
                    //不包含参数，获取当前所需资源全部数据
                    if (false !== stripos($uriArr, 's')) {
                        //首先移除资源名称右侧的复数 s,然后首字符转为大写
                        $this->_resourceName = ucFirst(rtrim($uriArr, 's'));
                    }
                }
                //设置需要调用的资源操作方法
                $this->_actionName = $this->methodAction[METHOD_DELETE] . $this->_resourceName;
                break;
        }
        return $reqData;
    }

}
$restful = new Restful();
$restful->run();