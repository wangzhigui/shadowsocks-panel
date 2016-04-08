<?php
/**
 * Project: shadowsocks-panel
 * Author: Sendya <18x@loacg.com>
 * Time: 2016/4/6 22:32
 */


namespace Model;

use Core\Database as DB;
use Core\Error;
use Core\Model;

/**
 * Class User
 * @table member
 * @package Model
 */
class User extends Model {

    const ENCRYPT_TYPE_DEFAULT = 0;
    const ENCRYPT_TYPE_ENHANCE = 1;

    private $primaryKey = 'uid';// 定义主键

    public $uid;// (主键)
    public $email;//电子邮件
    public $nickname;//昵称,没卵用
    protected $password = 'default';//Fuck password
    public $sspwd;// ss连接密码
    public $port;// ss端口
    public $flow_up = 0;//上传流量
    public $flow_down = 0;//下载流量
    public $transfer;//总流量
    public $plan = 'A';//账户类型
    public $enable = 1;//是否启用SS 0不启用 1启用
    public $money = 0;//狗屁用都没的 $
    public $invite = '';//注册所用的邀请码
    public $invite_num = 0;//用户拥有的邀请码
    public $regDateLine = 0;//注册时间
    public $lastConnTime = 0;//上次使用时间
    public $lastCheckinTime = 0;//上次签到时间
    public $lastFindPasswdTime = 0;//上次找回密码时间 (找回密码时间和次数仅用作限制3次或?次后禁止找回)
    public $lastFindPasswdCount = 0;//找回密码次数
    public $forgePwdCode; // 找回密码次数
    public $payTime; // 上次支付时间
    public $expireTime; // 到期时间
    /** @ignore */
    public $lastActive = TIMESTAMP;
    /** @ignore */
    private $isAdmin = false;

    /**
     * Get current user object
     * @return User
     */
    public static function getCurrent() {
        /** @var User $user */
        $user = $_SESSION['currentUser'];
        if ($user && TIMESTAMP - $user->lastActive > 600) {
            $userObj = self::getUserByUserId($user->uid);
            if (!$userObj) {
                $user = null;
            } elseif ($user->password != $userObj->password) {
                // Password changed
                $user = null;
            } else {
                $userObj->lastActive = TIMESTAMP;
                $user = $userObj;
            }
            $_SESSION['currentUser'] = $user;
        }
        return $user;
    }

    /**
     * @param $email
     * @return User
     */
    public static function getUserByEmail($email) {
        $statement = DB::getInstance()->prepare('SELECT * FROM `member` WHERE email = ?');
        $statement->bindValue(1, $email);
        $statement->execute();
        return $statement->fetchObject(__CLASS__);
    }

    /**
     * @param $userId
     * @return User
     */
    public static function getUserByUserId($userId) {
        $statement = DB::getInstance()->prepare('SELECT t1.*, IF(t2.id>0,1,0) as `isAdmin` FROM `member` t1 LEFT JOIN `admin` t2 ON t1.uid=t2.uid WHERE t1.uid = ?');
        $statement->bindValue(1, $userId, DB::PARAM_INT);
        $statement->execute();
        return $statement->fetchObject(__CLASS__);
    }

    public static function getUserList() {
        $statement = DB::getInstance()->prepare('SELECT * FROM `member` ORDER BY uid');
        $statement->execute();
        return $statement->fetchAll(DB::FETCH_CLASS, __CLASS__);
    }

    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    public function isAdmin() {
        return $this->isAdmin;
    }

}