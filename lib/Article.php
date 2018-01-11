<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/10
 * Time: 10:10
 * 文章model类
 */
//require_once(__DIR__ . 'db.php');

class Article{
    CONST ERROR_CONNECT_FAILED = '数据库连接失败';
    CONST ERROR_NONE_TABLE = '数据表不存在';
    CONST ERROR_PARAM = '参数错误';
    CONST ERROR_QUERY_FAILED = '操作失败';
    /*CONST SUCCESS_MSG_GET = '获取成功';
    CONST SUCCESS_CODE_GET = 200;
    CONST SUCCESS_MSG_CREATE = '添加成功';
    CONST SUCCESS_CODE_CREATE = 201;
    CONST SUCCESS_MSG_UPDATE = '更新成功';
    CONST SUCCESS_CODE_UPDATE = 201;
    CONST SUCCESS_MSG_DELETE = '删除成功';
    CONST SUCCESS_CODE_DELETE = 204;*/

    private $_db;
    public static $RESP_INFO;//返回结果

    //初始化时，需要 数据库操作句柄
    public function __construct(PDO $dbInstance){
        if (!$this->_db && $dbInstance instanceof PDO) {
            $this->_db = $dbInstance;
        }
    }

    /**
     * 获取当前类对应的数据库表名
     * @return string
     */
    public static function tableName(){
        return 'article';
    }

    /**
     * 资源操作执行前的检测
     */
    private function beforeAction(){
        if (!$this->_db) {
            return false;
        }
        return true;
    }


    /**
     * 查找文章，有参数则按参数查找，无参数则查找全部
     * @param array $queryParam
     */
    public function getArticle($queryParam = []){
        if (!$this->beforeAction()) {
            //返回通用错误编号
            self::$RESP_INFO['code'] = COMMON_ERROR_CODE;
            self::$RESP_INFO['msg'] = self::ERROR_CONNECT_FAILED;
            $this->afterAction();
        }
        //数据库连接已建立
        $tableName = self::tableName();
        $querySql = "select * from $tableName where 1=1";
        if (isset($queryParam['id']) && !empty($queryParam['id'])) {
            $querySql .= " and id = :id";
        }
        if (isset($queryParam['title']) && !empty($queryParam['title'])) {
            $querySql .= " and title like '%:title%'";
        }
        if (isset($queryParam['author']) && !empty($queryParam['author'])) {
            $querySql .= " and author like '%:author%'";
        }
        if (isset($queryParam['cate_id']) && !empty($queryParam['cate_id'])) {
            $querySql .= " and cate_id = :cateId";
        }
        if (isset($queryParam['is_valid']) && !empty($queryParam['is_valid'])) {
            $querySql .= " and is_valid = :isValid";
        }

        $preSql = $this->_db->prepare($querySql);
        $preSql->bindParam(":id", $articleId);
        $preSql->bindParam(":title", $title);
        $preSql->bindParam(":author", $author);
        $preSql->bindParam(":cate_id", $cateId);
        $preSql->bindParam(":is_valid", $isValid);
        $articleId = isset($queryParam['id']) ? intval($queryParam['id']) : 0;
        $title = isset($queryParam['title']) ? trim($queryParam['title']) : '';
        $author = isset($queryParam['author']) ? trim($queryParam['author']) : '';
        $cateId = isset($queryParam['cate_id']) ? intval($queryParam['cate_id']) : 0;
        $isValid = isset($queryParam['is_valid']) ? intval($queryParam['is_valid']) : 1;
        $preSql->execute();
        $preSql->setFetchMode(PDO::FETCH_ASSOC);
        $res = $preSql->fetchAll();

        $retData = [];
        //判断是否指定文章id，未指定返回全部，否则返回指定数据
        if (isset($queryParam['id']) && $articleId > 0) {
            $retData = !empty($res) && !empty($res[0]) ? $res[0] : [];
        } else if (isset($queryParam['id']) && $articleId <= 0){

        } else {
            $retData = $res;
        }
        self::$RESP_INFO['code'] = SUCCESS_CODE_GET;
        self::$RESP_INFO['msg'] = SUCCESS_MSG_GET;
        self::$RESP_INFO['data'] = $retData;
        return $this->afterAction();
    }

    /**
     * 添加文章
     * @param array $articleParam 参数=>值数组
     */
    public function createArticle(Array $articleParam){
        if (!$this->beforeAction()) {
            //返回通用错误编号
            self::$RESP_INFO['code'] = COMMON_ERROR_CODE;
            self::$RESP_INFO['msg'] = self::ERROR_CONNECT_FAILED;
            $this->afterAction();
        }
        //数据库连接已建立
        $tableName = self::tableName();
        $curTime = time();
        $querySql = "insert into $tableName(`title`,`author`,`content`,`cate_id`,`read_times`,`create_time`,`update_time`) VALUES(:title, :author, :content, :cateId, :readTimes, $curTime, $curTime)";

        if (!isset($articleParam['title']) || !isset($articleParam['author']) || !isset($articleParam['content']) || !isset($articleParam['cate_id'])) {
            self::$RESP_INFO['code'] = COMMON_ERROR_CODE;
            self::$RESP_INFO['msg'] = self::ERROR_PARAM;
            return $this->afterAction();
        }
        $preSql = $this->_db->prepare($querySql);
        $bindArr = [];
        if (array_key_exists('title', $articleParam)) {
            //$preSql->bindParam(":title", $articleTitle);
            $bindArr[":title"] = trim($articleParam['title']);
        }
        if (array_key_exists('author',$articleParam)) {
            //$preSql->bindParam(":author", $articleAuthor);
            $bindArr[":author"] = trim($articleParam['author']);
        }
        if (array_key_exists('content',$articleParam)) {
            //$preSql->bindParam(":content", $articleContent);
            $bindArr[":content"] = trim($articleParam['content']);
        }
        if (array_key_exists('cate_id',$articleParam)) {
            //$preSql->bindParam(":cateId", $articleCateId);
            $bindArr[":cateId"] = trim($articleParam['cate_id']);
        }

        //$preSql->bindParam(":readTimes", $articleReadTimes);
        $bindArr[":readTimes"] = isset($articleParam['read_times']) ? intval($articleParam['read_times']) : 1;



        /*$preSql->bindParam(":id", $articleId);
        $preSql->bindParam(":title", $title);
        $preSql->bindParam(":author", $author);
        $preSql->bindParam(":cate_id", $cateId);
        $preSql->bindParam(":is_valid", $isValid);
        $articleTitle = trim($queryParam['title']);
        $articleAuthor = trim($queryParam['author']);
        $articleContent = trim($queryParam['author']);
        $articleCateId = intval($queryParam['cate_id']);*/

        //$articleReadTimes = isset($queryParam['read_times']) ? intval($queryParam['read_times']) : 1;
        $res = $preSql->execute($bindArr);//执行sql语句
        if (false !== $res) {
            //插入成功，返回插入的数据
            //query 执行结果为 PDO 对象，调用对象的 fetchAll方法即可获取结果
            $insertRes = $this->_db->query("select * from $tableName where id=" . $this->_db->lastInsertId(), PDO::FETCH_ASSOC);
            $newArticleInfo = $insertRes->fetchAll();

            self::$RESP_INFO['code'] = SUCCESS_CODE_CREATE;
            self::$RESP_INFO['msg'] = SUCCESS_MSG_CREATE;
            self::$RESP_INFO['data'] = $newArticleInfo[0];
        } else {
            self::$RESP_INFO['code'] = COMMON_ERROR_CODE;
            self::$RESP_INFO['msg'] = self::ERROR_QUERY_FAILED;
        }

        return $this->afterAction();
    }


    /**
     * 修改文章
     * @param array $articleParam 参数=>值数组
     */
    public function updateArticle(Array $articleParam){
        if (!$this->beforeAction()) {
            //返回通用错误编号
            self::$RESP_INFO['code'] = COMMON_ERROR_CODE;
            self::$RESP_INFO['msg'] = self::ERROR_CONNECT_FAILED;
            $this->afterAction();
        }
        //修改文章时，必须传入文章ID
        if (!isset($articleParam['id'])) {
            self::$RESP_INFO['code'] = COMMON_ERROR_CODE;
            self::$RESP_INFO['msg'] = self::ERROR_PARAM;
        } else {
            //数据库连接已建立
            $tableName = self::tableName();
            $curTime = time();
            $querySql = "UPDATE $tableName SET ";

            if (count($articleParam) < 2) {
                self::$RESP_INFO['code'] = COMMON_ERROR_CODE;
                self::$RESP_INFO['msg'] = self::ERROR_PARAM;
                return $this->afterAction();
            }

            $bindArr = [];
            if (array_key_exists('title',$articleParam)) {
                $querySql .= "`title` = :title,";
                $bindArr[":title"] = trim($articleParam['title']);
            }
            if (array_key_exists('author',$articleParam)) {
                $querySql .= "`author` = :author,";
                $bindArr[":author"] = trim($articleParam['author']);
            }
            if (array_key_exists('content',$articleParam)) {
                $querySql .= "`content` = :content,";
                $bindArr[":content"] = trim($articleParam['content']);
            }
            if (array_key_exists('cate_id',$articleParam)) {
                $querySql .= "`cate_id` = :cateId,";
                $bindArr[":cateId"] = intval($articleParam['cate_id']);
            }
            if (array_key_exists('is_valid',$articleParam)) {
                $querySql .= "`is_valid` = :isValid,";
                $bindArr[":isValid"] = intval($articleParam['is_valid']);
            }
            //移除最后一个',' 号
            //$querySql = rtrim($querySql, ',') . ') ' . (rtrim($valuePart, ',')) . ');';

            //自动更新时间
            $querySql .= "`update_time` = :updateTime WHERE id = :articleId;";
            $bindArr[':updateTime'] = $curTime;
            $bindArr[':articleId'] = intval($articleParam['id']);

            $preSql = $this->_db->prepare($querySql);
            $res = $preSql->execute($bindArr);//执行sql语句
            if (false !== $res) {
                //插入成功，返回修改后的文章
                //query 执行结果为 PDO 对象，调用对象的 fetchAll方法即可获取结果
                $insertRes = $this->_db->query("select * from $tableName where id=" . $articleParam['id'], PDO::FETCH_ASSOC);
                $newArticleInfo = $insertRes->fetchAll();

                self::$RESP_INFO['code'] = SUCCESS_CODE_UPDATE;
                self::$RESP_INFO['msg'] = SUCCESS_MSG_UPDATE;
                self::$RESP_INFO['data'] = $newArticleInfo[0];
            } else {
                self::$RESP_INFO['code'] = COMMON_ERROR_CODE;
                self::$RESP_INFO['msg'] = self::ERROR_QUERY_FAILED;
            }
        }
        return $this->afterAction();
    }


    /**
     * 删除文章
     * @param array $articleParam 参数=>值数组
     */
    public function deleteArticle(Array $articleParam){
        if (!$this->beforeAction()) {
            //返回通用错误编号
            self::$RESP_INFO['code'] = COMMON_ERROR_CODE;
            self::$RESP_INFO['msg'] = self::ERROR_CONNECT_FAILED;
            $this->afterAction();
        }
        //删除文章时，必须传入文章ID/title/author/cate_id/is_valid/create_time中至少一个参数
        //if (!isset($articleParam['id']) && !isset($articleParam['title']) && !isset($articleParam['author']) && !isset($articleParam['cate_id']) && !isset($articleParam['is_valid']) && !isset($articleParam['create_time'])) {

        if (count($articleParam) < 1) {
            self::$RESP_INFO['code'] = COMMON_ERROR_CODE;
            self::$RESP_INFO['msg'] = self::ERROR_PARAM;
        } else {
            //数据库连接已建立
            $tableName = self::tableName();
            //$curTime = time();
            $querySql = "DELETE FROM $tableName WHERE ";

            $bindArr = [];
            if (array_key_exists('id',$articleParam)) {
                $querySql .= " `id` = :articleId AND ";
                $bindArr[":articleId"] = trim($articleParam['id']);
            }
            if (array_key_exists('title',$articleParam)) {
                $querySql .= " `title` = :title AND ";
                $bindArr[":title"] = trim($articleParam['title']);
            }
            if (array_key_exists('author',$articleParam)) {
                $querySql .= " `author` = :author AND ";
                $bindArr[":author"] = trim($articleParam['author']);
            }
            if (array_key_exists('content',$articleParam)) {
                $querySql .= " `content` = :content AND ";
                $bindArr[":content"] = trim($articleParam['content']);
            }
            if (array_key_exists('cate_id',$articleParam)) {
                $querySql .= " `cate_id` = :cateId AND ";
                $bindArr[":cateId"] = intval($articleParam['cate_id']);
            }
            if (array_key_exists('is_valid',$articleParam)) {
                $querySql .= " `is_valid` = :isValid AND ";
                $bindArr[":isValid"] = intval($articleParam['is_valid']);
            }
            //移除最后一个'AND'
            //首先移除两端空格
            $querySql = trim($querySql);
            $querySql = trim(rtrim($querySql, 'AND')) . ';';

            $preSql = $this->_db->prepare($querySql);
            $preSql->execute($bindArr);//执行sql语句

            if ($preSql->rowCount() > 0) {
                //删除成功，返回空数据
                self::$RESP_INFO['code'] = SUCCESS_CODE_DELETE;
                self::$RESP_INFO['msg'] = SUCCESS_MSG_DELETE;
                //self::$RESP_INFO['data'] = $newArticleInfo[0];
            } else {
                self::$RESP_INFO['code'] = COMMON_ERROR_CODE;
                self::$RESP_INFO['msg'] = self::ERROR_QUERY_FAILED;
            }
        }
        return $this->afterAction();
    }


    /**
     * 处理请求后对要返回的数据进行处理
     * @param $data 返回数据
     */
    private function afterAction(){
        if (!is_array(self::$RESP_INFO)) {
            throw new Exception('数据格式错误', COMMON_ERROR_CODE);
        } else if (!isset(self::$RESP_INFO['code'])){
            throw new Exception('缺少结果代码(例: 299)', COMMON_ERROR_CODE);
        }
        /*return json_encode(['code' => 299, 'msg' => '不管你错没错，反正这个就是不能给你', 'data' => [
            'java' => 'Tomcat',
            'php' => 'Nginx',
            'node' => 'v8',
        ]]);*/
        //返回 JSON 格式的数据
        return json_encode(self::$RESP_INFO);
    }

}
