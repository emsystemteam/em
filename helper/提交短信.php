<?php //�ύ����
$post_data = array();
$post_data['sn'] = '';���к�
$post_data['password'] = '';����
$post_data['content'] = ''; 
$post_data['mobile'] = '';�ֻ���
$post_data['sendTime'] = ''; //��ʱ���ͣ������ʽYYYYMMDDHHmmss������ֵ
$post_data['ext']='';������
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
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //�����Ҫ�����ֱ�ӷ��ص�������Ǽ�����䡣
$result = curl_exec($ch);
?>