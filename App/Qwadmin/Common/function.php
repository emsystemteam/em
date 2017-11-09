<?php

/**
 * 增加日志
 * @param $log
 * @param bool $name
 */
function addlog($log, $name = false)
{
    $Model = M('log');
    if (! $name) {
        session_start();
        $uid = session('uid');
        if ($uid) {
            $user = M('member')->field('user')
                ->where(array(
                'uid' => $uid
            ))
                ->find();
            $data['name'] = $user['user'];
        } else {
            $data['name'] = '';
        }
    } else {
        $data['name'] = $name;
    }
    $data['t'] = time();
    $data['ip'] = $_SERVER["REMOTE_ADDR"];
    $data['log'] = $log;
    $Model->data($data)->add();
}

/**
 * 获取用户信息
 */
function member($uid, $field = false)
{
    $model = M('Member');
    if ($field) {
        return $model->field($field)
            ->where(array(
            'uid' => $uid
        ))
            ->find();
    } else {
        return $model->where(array(
            'uid' => $uid
        ))->find();
    }
}

/**
 * *
 * 发送短信
 *
 * @param $mobileArray 电话号码集合
 * @param $content 短信内容
 * @return 返回null则代表传入数据有问题，否则返回数组，对应每条电话记录是否发送成功{'130000000'->'0','18900000000'->1},0代表成功
 */
function sendSmsMessage($mobileArray, $content)
{
    if (! empty($content) && is_array($mobileArray) && count($mobileArray) > 0) { // 判断传值是否正确
        $resultArray = array(); // 返回信息格式：
        foreach ($mobileArray as $mobile) {
            $post_data = array();
            $post_data['sn'] = 'SDK_AAA_00227'; // 序列号
            $post_data['password'] = '852172777'; // 密码
            $post_data['content'] = $content;
            $post_data['mobile'] = $mobile; // 手机号
            $post_data['sendTime'] = date("YYYYMMDDHHmmss", time()); // 定时发送，输入格式YYYYMMDDHHmmss的日期值
            $post_data['ext'] = ''; // 接入码
            $url = 'http://123.56.233.239:8080/msg-core-web/msg/sendMsg';
            $o = '';
            foreach ($post_data as $k => $v) {
                $o .= "$k=" . urlencode($v) . '&';
            }
            $post_data = substr($o, 0, - 1);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 如果需要将结果直接返回到变量里，那加上这句。
            $result = curl_exec($ch);
            $jsonObject = json_decode($result);
            $resultArray[$mobile] = $jsonObject->status->code;
        }
        return $resultArray;
    } else {
        return null;
    }
}

/**
 * *
 * 生成随机数
 *
 * @param $length 生成随机数
 * @return 随机数
 */
function getRandNumber($length)
{
    $a = range(0, 9);
    for ($i = 0; $i < $length; $i ++) {
        $b[] = array_rand($a);
    }
    return join("", $b);
}

/**
 * *
 * 创建uuid
 *
 * @return string
 */
function guid()
{
    mt_srand((double) microtime() * 10000);
    $charid = strtoupper(md5(uniqid(rand(), true)));
    $hyphen = chr(45); // "-"
    $uuid = substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12);
    return $uuid;
}

/**
 * 发送微信消息
 *
 * @param array $phones
 *            微信账号数组
 * @param string $message
 *            消息
 * @return number 发送结果（0-成功,-1系统繁忙,-2参数为空,-3访问令牌为空,-4未知错误,其他错误代码详见：http://qydev.weixin.qq.com/wiki/index.php?title=%E5%85%A8%E5%B1%80%E8%BF%94%E5%9B%9E%E7%A0%81%E8%AF%B4%E6%98%8E）
 */
function send_wechat_message($openids, $message)
{
    if (! is_array($openids) || count($openids) == 0 || empty($message))
        return - 2;
    $appid = "wx8c9d50dc3aea1225";
    $secret = "bb7e6700ecb5fa8a384b2d119910b2f3";
    $access_token = get_access_token($appid, $secret);
    if (empty($access_token))
        return - 3;
    $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token={$access_token}";
    $parameters = array();
    $parameters['touser'] = $openids;
    $parameters['msgtype'] = 'text';
    $parameters['text']['content'] = $message;
    $res = http_call($url, json_encode($parameters, JSON_UNESCAPED_UNICODE));
    $json = json_decode($res);
    if (isset($json->errcode)) {
        return $json->errcode;
    }
    return - 4;
}

/**
 * 获取访问令牌
 *
 * @param string $appid
 *            应用ID
 * @param string $secret
 *            应用密钥
 * @return string|NULL
 */
function get_access_token($appid, $secret)
{
    if (empty($appid) || empty($secret))
        return null;
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
    $res = http_call($url, '');
    $json = json_decode($res);
    if (isset($json->access_token)) {
        return $json->access_token;
    }
    return null;
}

/**
 * Http请求
 *
 * @param string $url
 *            目标地址
 * @param array $parameters
 *            JSON对象
 * @return mixed 请求结果
 */
function http_call($url, $parameters)
{
    $ci = curl_init();
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ci, CURLOPT_TIMEOUT, 30);
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ci, CURLOPT_POSTFIELDS, $parameters);
    curl_setopt($ci, CURLOPT_POST, true);
    curl_setopt($ci, CURLOPT_URL, $url);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    $response = curl_exec($ci);
    curl_close($ci);
    return $response;
}



/**
 * 上传文件类型控制 此方法仅限ajax上传使用
 * @param  string   $path    字符串 保存文件路径示例： /Upload/image/
 * @param  string   $format  文件格式限制
 * @param  integer  $maxSize 允许的上传文件最大值 5242880
 * @return booler   返回ajax的json格式数据
 */
function ajax_upload($path='file',$format='empty',$maxSize='5242880'){
	ini_set('max_execution_time', '0');
	// 去除两边的/
	$path=trim($path,'/');
	// 添加Upload根目录
	$path=strtolower(substr($path, 0,6))==='upload' ? ucfirst($path) : 'Upload/'.$path;
	// 上传文件类型控制
	$ext_arr= array(
			'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp')/* ,
			'photo' => array('jpg', 'jpeg', 'png'),
			'flash' => array('swf', 'flv'),
			'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
			'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2','pdf') */
	);
	if(!empty($_FILES)){
		// 上传文件配置
		$config=array(
				'maxSize'   =>  $maxSize,               // 上传文件最大为5M
				'rootPath'  =>  './',                   // 文件上传保存的根路径
				'savePath'  =>  './'.$path.'/',         // 文件上传的保存路径（相对于根路径）
				'saveName'  =>  array('uniqid',''),     // 上传文件的保存规则，支持数组和字符串方式定义
				'autoSub'   =>  true,                   // 自动使用子目录保存上传文件 默认为true
				'exts'      =>    isset($ext_arr[$format])?$ext_arr[$format]:'',
		);
		// 实例化上传
		$upload=new \Think\Upload($config);
		// 调用上传方法
		$info=$upload->upload();
		$data=array();
		$result=array();
		if(!$info){
			// 返回错误信息
			$error=$upload->getError();
			$data['error_info']=$error;
		}else{
			// 返回成功信息
			foreach($info as $file){
				$data['name']=trim($file['savepath'].$file['savename'],'.');
				array_push($result, $data['name']);
			}
		}
		return $result;
	}
}
