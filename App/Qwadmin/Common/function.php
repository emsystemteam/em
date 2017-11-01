<?php
/**
 * 增加日志
 * @param $log
 * @param bool $name
 */
function addlog($log, $name = false) {
	$Model = M ( 'log' );
	if (! $name) {
		session_start ();
		$uid = session ( 'uid' );
		if ($uid) {
			$user = M ( 'member' )->field ( 'user' )->where ( array (
					'uid' => $uid 
			) )->find ();
			$data ['name'] = $user ['user'];
		} else {
			$data ['name'] = '';
		}
	} else {
		$data ['name'] = $name;
	}
	$data ['t'] = time ();
	$data ['ip'] = $_SERVER ["REMOTE_ADDR"];
	$data ['log'] = $log;
	$Model->data ( $data )->add ();
}

/**
 * 获取用户信息
 */
function member($uid, $field = false) {
	$model = M ( 'Member' );
	if ($field) {
		return $model->field ( $field )->where ( array (
				'uid' => $uid 
		) )->find ();
	} else {
		return $model->where ( array (
				'uid' => $uid 
		) )->find ();
	}
}

/***
 * 发送短信
 * @param  $mobileArray 电话号码集合
 * @param  $content 短信内容
 * @return 返回null则代表传入数据有问题，否则返回数组，对应每条电话记录是否发送成功{'130000000'->'0','18900000000'->1},0代表成功
 */
function sendSmsMessage($mobileArray, $content) {
	if (! empty ( $content ) && is_array ( $mobileArray ) && count ( $mobileArray ) > 0) { // 判断传值是否正确
		$resultArray = array (); // 返回信息格式：
		foreach ( $mobileArray as $mobile ) {
			$post_data = array ();
			$post_data ['sn'] = 'SDK_AAA_00227'; // 序列号
			$post_data ['password'] = '852172777'; // 密码
			$post_data ['content'] = $content;
			$post_data ['mobile'] = $mobile; // 手机号
			$post_data ['sendTime'] = date ( "YYYYMMDDHHmmss", time () ); // 定时发送，输入格式YYYYMMDDHHmmss的日期值
			$post_data ['ext'] = ''; // 接入码
			$url = 'http://123.56.233.239:8080/msg-core-web/msg/sendMsg';
			$o = '';
			foreach ( $post_data as $k => $v ) {
				$o .= "$k=" . urlencode ( $v ) . '&';
			}
			$post_data = substr ( $o, 0, - 1 );
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_HEADER, 0 );
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
			$result = curl_exec ( $ch );
			$jsonObject=json_decode($result);
			$resultArray[$mobile]=$jsonObject->status->code; 
		}
		return $resultArray;
	} else {
		return  null;
	}
	
}
/**
 * *
 * 生成随机数
 *
 * @param $length 生成随机数	
 * @return 随机数
 */
function getRandNumber($length) {
	$a = range ( 0, 9 );
	for($i = 0; $i < $length; $i ++) {
		$b [] = array_rand ( $a );
	}
	return join ( "", $b );
}



/***
 * 创建uuid
 * @return string
 */
function guid() {
	mt_srand ( ( double ) microtime () * 10000 );
	$charid = strtoupper ( md5 ( uniqid ( rand (), true ) ) );
	$hyphen = chr ( 45 ); // "-"
	$uuid = substr ( $charid, 0, 8 ) . $hyphen . substr ( $charid, 8, 4 ) . $hyphen . substr ( $charid, 12, 4 ) . $hyphen . substr ( $charid, 16, 4 ) . $hyphen . substr ( $charid, 20, 12 );
	return $uuid;
}