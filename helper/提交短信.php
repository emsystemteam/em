<?php //提交短信
$post_data = array();
$post_data['sn'] = '';序列号
$post_data['password'] = '';密码
$post_data['content'] = ''; 
$post_data['mobile'] = '';手机号
$post_data['sendTime'] = ''; //定时发送，输入格式YYYYMMDDHHmmss的日期值
$post_data['ext']='';接入码
$url='http://123.56.233.239:8080/msg-core-web/msg/sendMsg';
$o='';
foreach ($post_data as $k=>$v)
{
   $o.="$k=".urlencode($v).'&';
}
$post_data=substr($o,0,-1);
$ch = curl_init();
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
$result = curl_exec($ch);
?>