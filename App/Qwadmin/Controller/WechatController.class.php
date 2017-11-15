<?php
/**
 *
 * 版权：CopyRight (C) talver 2017
 * 作者：talver (talver@126.com)
 * 日期：Oct 26, 2017
 * 版本：1.0.0
 * 说明：微信登录授权
 *
 **/
namespace Qwadmin\Controller;

use Think\Controller;
use Think\Exception;

class WechatController extends Controller
{

    private $app_id = 'wx8c9d50dc3aea1225';

    private $app_secret = 'bb7e6700ecb5fa8a384b2d119910b2f3';

    private $redirect_uri = '';

    private $return_url = '';

    public function index()
    {
        $this->return_url = I("get.url");
        $this->redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . U('wechat/authorize') . '?url=' . $this->return_url;
        $state = md5(time() + getRandNumber(10));
        redirect($this->get_authorize_url($state));
        // redirect($this->redirect_uri . "?code=1231321&state={$state}");
    }

    public function authorize()
    {
        $this->return_url = I("get.url");
        $code = I("get.code");
        $state = I("get.state");
        $openid = $this->get_openid($code); // 获取openid
        if (strlen($openid)) {
            $model = M("Member");
            $user = $model->field('uid,user')
                ->where(array(
                'openid' => $openid
            ))
                ->find();
            
            if ($user) {
                if (strlen($this->return_url) > 0) {
                    redirect(U('mobile/profile', array(
                        'id' => $openid
                    )));
                } else {
                    $uid = $user['uid'];
                    $name = $user['user'];
                    $this->login($uid, $name);
                }
            } else {
                $this->assign('openid', $openid);
                $this->display("authorize");
            }
        } else {
            $this->error("获取微信登录授权失败！", U('login/index'));
        }
    }

    public function send()
    {
        $result = array();
        if (IS_AJAX) {
            $phone = I("post.phone");
            $model = M("Member");
            $user = $model->field('uid,user')
                ->where(array(
                'phone' => $phone
            ))
                ->find();
            
            if (! $user) {
                $result['status'] = - 1;
                $result['message'] = "该手机号码没有注册，请先注册账号！";
                $this->ajaxReturn($result, 'json');
            } else {
                if ($this->check_rule($phone)) {
                    $code = getRandNumber(6);
                    $res = $this->send_code($phone, $code);
                    if ($res) {
                        $this->save_db($phone, $code); // 发送成功保存结果到数据库
                        $result['status'] = 0;
                        $result['message'] = "success";
                        $this->ajaxReturn($result, 'json');
                    } else {
                        $result['status'] = - 1;
                        $result['message'] = "验证码发送失败，请稍后再试！";
                        $this->ajaxReturn($result, 'json');
                    }
                } else {
                    $result['status'] = - 1;
                    $result['message'] = "发送过于频繁，2分钟后再试！";
                    $this->ajaxReturn($result, 'json');
                }
            }
            $result['status'] = - 1;
            $result['message'] = "操作失败！";
            $this->ajaxReturn($result, 'json');
        } else {
            $result['status'] = - 1;
            $result['message'] = "操作失败！";
            $this->ajaxReturn($result, 'json');
        }
    }

    // 校验验证码
    public function check()
    {
        $phone = I("post.phone");
        $smscode = I("post.smscode");
        $openid = I("post.openid");
        $t = time();
        $model = M("smslog");
        $check = $model->field('phone')
            ->where("phone='%s' and code='%s' and %d-t<=300", array(
            $phone,
            $smscode,
            $t
        ))
            ->find();
        
        if ($check) {
            if (strlen($this->return_url) > 0) {
                redirect($this->return_url . '?id=' . $openid);
            } else {
                $model = M("Member");
                // 绑定openid
                $data['openid'] = $openid;
                $model->data($data)
                    ->where(array(
                    'phone' => $phone
                ))
                    ->save();
                // 登录
                $user = $model->field('uid,user')
                    ->where(array(
                    'phone' => $phone
                ))
                    ->find();
                $uid = $user['uid'];
                $name = $user['user'];
                $this->login($uid, $name);
            }
        } else {
            $this->assign('phone', $phone);
            $this->assign('smscode', $smscode);
            $this->assign('openid', $openid);
            $this->assign('error', '验证码错误或过期！');
            $this->display("authorize");
        }
    }

    function save_db($phone, $code)
    {
        $data['t'] = time();
        $data['phone'] = $phone;
        $data['code'] = $code;
        M("smslog")->data($data)->add();
    }

    function check_rule($phone)
    {
        $t = time();
        $model = M("smslog");
        $rule = $model->field('phone')
            ->where("phone='%s' and %d-t<=120", array(
            $phone,
            $t
        ))
            ->find();
        if ($rule) {
            return false;
        }
        return true;
    }

    // 登录成功
    function login($uid, $name)
    {
        $salt = C("COOKIE_SALT");
        $ip = get_client_ip();
        $ua = $_SERVER['HTTP_USER_AGENT'];
        session_start();
        session('uid', $uid);
        // 加密cookie信息
        $auth = password($uid . $name . $ip . $ua . $salt);
        if ($remember) {
            cookie('auth', $auth, 3600 * 24 * 365); // 记住我
        } else {
            cookie('auth', $auth);
        }
        addlog('登录成功。');
        $url = U('index/index');
        header("Location: $url");
        exit(0);
    }

    function send_code($phone, $code)
    {
        try {
            $content = sprintf("尊敬的客户，您的验证码为%s,1分钟内有效，您正在进行微信登录身份认证操作。【乐助科技】", $code);
            $post_data['sn'] = 'SDK_AAA_00227'; // 序列号
            $post_data['password'] = '852172777'; // 密码
            $post_data['content'] = $content;
            $post_data['mobile'] = $phone; // 手机号
            $post_data['sendTime'] = date("YYYYMMDDHHmmss", time()); // 定时发送，输入格式YYYYMMDDHHmmss的日期值
            $post_data['ext'] = ''; // 接入码
            $url = 'http://123.56.233.239:8080/msg-core-web/msg/sendMsg';
            $post_data = http_build_query($post_data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            $result = curl_exec($ch);
            $json = json_decode($result);
            if ($json->status->code == 0) {
                return true;
            }
        } catch (Exception $ex) {}
        return false;
    }

    function get_openid($code)
    {
        if (strlen($code) == 0)
            return "";
        $token = $this->get_access_token($code);
        if (strlen($token) == 0)
            return "";
        return $token;
    }

    /**
     * 获取微信授权链接
     *
     * @param string $redirect_uri
     *            跳转地址
     * @param mixed $state
     *            参数
     */
    function get_authorize_url($state)
    {
        $redirect_uri = urlencode($this->redirect_uri);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_base&state={$state}#wechat_redirect";
    }

    /**
     * 获取授权token
     *
     * @param string $code
     *            通过get_authorize_url获取到的code
     */
    function get_access_token($code)
    {
        if (strlen($code) == 0)
            return "";
        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secret}&code={$code}&grant_type=authorization_code";
        $token_data = $this->http($token_url);
        $json = json_decode($token_data);
        if (isset($json->openid)) {
            return $json->openid;
        }
        return "";
    }

    function http($url)
    {
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_POST, true);
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ci);
        curl_close($ci);
        return $response;
    }
}