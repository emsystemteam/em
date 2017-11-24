<?php

/**
 *
 * 版权所有：liangc
 * 作    者：liangc
 * 日    期：2017-10-26
 * 版    本：1.0.0
 * 功能说明：短信、微信群发
 *
 **/
namespace Qwadmin\Controller;

class MessageController extends ComController {
	
	// 群发短信首页
	public function index() {
		$m = M ( 'em_smsmodel' );
		$authResult = M ( 'em_dictionary' )->where ( 'DICT_NAME=' . '"authResult"' )->order ( 'CREATE_TIME desc' )->select ();
		$holdtype = M ( 'em_dictionary' )->where ( 'DICT_NAME=' . '"householdStatus"' )->order ( 'CREATE_TIME desc' )->select ();
		$list = $m->where ( 'smstype=1 and status=1 and isapprove=1' )->order ( 'createtime desc' )->select ();
		$this->assign ( 'titlelist', $list );
		$this->assign ( 'authResults', $authResult );
		$this->assign ( 'holdtype', $holdtype );
		$this->assign ( 'page', $page );
		$this->display ();
	}
	
	// 群发图文消息
	public function sendAllByTag() {
		$msgtype = I ( 'msgtype' );
		if ($msgtype == '0') { // 发送文本
			$content = I ( 'content' );
			if (trim ( $content ) != "" && count ( $content ) <= 600) {
				$array = array (
						'filter' => array ( // 用于设定图文消息的接收者
								'is_to_all' => true, // 是否向全部用户发送，值为true或false，选择true该消息群发给所有用户，选择false可根据tag_id发送给指定群组的用户
								'tag_id' => ''  // 群发到的标签的tag_id，参加用户管理中用户分组接口，若is_to_all值为true，可不填写tag_id
						),
						'text' => array (
								'content' => $content 
						),
						'msgtype' => 'text'  // 群发的消息类型，图文消息为mpnews，文本消息为text，语音为voice，音乐为music，图片为image，视频为video，卡券为wxcard
				);
				$result = sendAllByTag ( $msgarray );
				if ($result->errcode == '0') { // 成功
					$this->addwechatlog ( 1, 0, $content );
					addlog ( '群发消息成功-文本' );
					$this->ajaxReturn ( array (
							'status' => 1,
							'message' => $result 
					) );
				} else {
					$this->ajaxReturn ( array (
							'status' => 0,
							'message' => $result->errmsg 
					) );
				}
			} else {
				$this->ajaxReturn ( array (
						'status' => 0,
						'message' => '文本内容不能为空，且不能超过600字' 
				) );
			}
		} else {
			$id = I ( 'id' );
			$new = M ( 'em_news' )->where ( 'id=' . $id )->find ();
			$array = array (
					'filter' => array ( // 用于设定图文消息的接收者
							'is_to_all' => true, // 是否向全部用户发送，值为true或false，选择true该消息群发给所有用户，选择false可根据tag_id发送给指定群组的用户
							'tag_id' => ''  // 群发到的标签的tag_id，参加用户管理中用户分组接口，若is_to_all值为true，可不填写tag_id
					),
					'mpnews' => array ( // 用于设定即将发送的图文消息
							'media_id' => $new ['media_id']  // 用于群发的消息的media_id
					),
					'msgtype' => 'mpnews'  // 群发的消息类型，图文消息为mpnews，文本消息为text，语音为voice，音乐为music，图片为image，视频为video，卡券为wxcard
			);
			$result = sendAllByTag ( $msgarray );
			if ($result->errcode == '0') { // 成功
				$this->addwechatlog ( 1, 0, $new ['newstitle'] );
				addlog ( '群发消息成功-图本' );
				$this->ajaxReturn ( array (
						'status' => 1,
						'message' => $result 
				) );
			} else {
				$this->ajaxReturn ( array (
						'status' => 0,
						'message' => $result->errmsg 
				) );
			}
		}
	}
	
	// 预览图文消息(开发使用)
	public function preview() {
		$users = M ( 'member' )->where ( 'openid is not null' )->select ();
		$msgtype = I ( 'msgtype' );
		if ($msgtype == '0') { // 发送文本
			$content = I ( 'content' );
			if (trim ( $content ) != "" && count ( $content ) <= 600) {
				foreach ( $users as $row ) {
					$msgarray = array (
							'touser' => $row ['openid'],
							'msgtype' => 'text',
							'text' => array (
									'content' => $content 
							) 
					);
					$result = preview ( $msgarray );
				}
				// 保存发送微信日志
				$this->addwechatlog ( 1, 0, $content );
				addlog ( '群发消息成功-文本（开发用）' );
				$this->ajaxReturn ( array (
						'status' => 1,
						'message' => '发送成功' 
				) );
			} else {
				
				$this->ajaxReturn ( array (
						'status' => 0,
						'message' => '文本内容不能为空，且不能超过600字' 
				) );
			}
		} else { // 发送图文
			$id = I ( 'id' );
			$new = M ( 'em_news' )->where ( 'id=' . $id )->find ();
			foreach ( $users as $row ) {
				$msgarray = array (
						'touser' => $row ['openid'],
						'msgtype' => 'mpnews',
						'mpnews' => array (
								'media_id' => $new ['media_id'] 
						) 
				);
				$result = preview ( $msgarray );
			}
			$this->addwechatlog ( 1, 0, $new ['newstitle'] );
			addlog ( '群发消息成功-图本（开发用）' );
			$this->ajaxReturn ( array (
					'status' => 1,
					'message' => '发送成功' 
			) );
		}
	}
	
	/**
	 * 记录微信群发日志
	 * 
	 * @param 0短信，1微信 $msgtype        	
	 * @param 发送数量 $amount        	
	 * @param 发送内容 $content        	
	 */
	private function addwechatlog($msgtype, $amount = 0, $content) {
		// 保存发送微信日志
		$model = D ( 'em_smslog' );
		$model->createtime = date ( 'y-m-d H:i:s', time () );
		$model->creater = session ( 'uid' );
		$model->smscontent = $content;
		$model->msgtype = $msgtype;
		$model->amount = $amount;
		$model->add ();
	}
	
	// 微信发送日志
	public function wechatlog() {
		$p = isset ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : '1';
		$pagesize = 10; // 每页数量
		$offset = $pagesize * ($p - 1); // 计算记录偏移量
		$m = M ( 'em_smslog' );
		$count = $m->where ( 'msgtype=1' )->count ();
		$list = $m->where ( 'msgtype=1' )->order ( 'createtime desc' )->limit ( $offset . ',' . $pagesize )->select ();
		$page = new \Think\Page ( $count, $pagesize );
		$page = $page->show ();
		$this->assign ( 'list', $list );
		$this->assign ( 'page', $page );
		$this->display ();
	}
	// 微信模板管理
	public function wechatsmsindex() {
		// 图文模板
		$m = M ( 'em_news' );
		$list = $m->where ( 'status=1' )->order ( 'createtime desc' )->select ();
		$this->assign ( 'list', $list );
		$this->display ();
	}
	// 短信模板管理
	public function smsindex() {
		$m = M ( 'em_smsmodel' );
		$authResult = M ( 'em_dictionary' )->where ( 'DICT_NAME=' . '"authResult"' )->order ( 'CREATE_TIME desc' )->select ();
		$holdtype = M ( 'em_dictionary' )->where ( 'DICT_NAME=' . '"householdStatus"' )->order ( 'CREATE_TIME desc' )->select ();
		$list = $m->where ( 'smstype=1 and status=1 and isapprove=1' )->order ( 'createtime desc' )->select ();
		$this->assign ( 'titlelist', $list );
		$this->assign ( 'authResults', $authResult );
		$this->assign ( 'holdtype', $holdtype );
		$this->display ();
	}
	// 指定号码发送微信
	public function sendWechatSmsByPhoneNumber() {
		$smsmodelid = I ( 'smsmodelid' );
		$phonenumbers = I ( 'phonenumbers' );
		if ($smsmodelid == '') {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '未选择短信模板' 
			) );
		} else {
			if ($phonenumbers == '') {
				$this->ajaxReturn ( array (
						'status' => 0,
						'message' => '没有手机号' 
				) );
			} else {
				$smsmodel = M ( 'em_smsmodel' )->where ( "id='$smsmodelid'" )->find ();
				$mobileArray = explode ( ",", $phonenumbers );
				// 后期替换为微信发送方法
				// 根据号码查询openid号
				$condition ['phone'] = array (
						'in',
						$mobileArray 
				);
				$arr = M ( 'member' )->where ( $condition )->select ();
				if ($arr) {
					$msgarray = array ();
					foreach ( $arr as $row ) {
						$msgarray ['touser'] = $row [openid];
						$msgarray ['msgtype'] = 'text';
						$msgarray ['text'] ['content'] = $smsmodel ['smscontent'];
						/*
						 * 图文类型
						 * $msgarray ['touser'] = $row[openid];
						 * $msgarray ['msgtype'] = 'news';
						 * $msgarray ['news'] ['articles'] = array (
						 * array (
						 * 'title' => $smsmodel ['smstitle'],
						 * 'description' => $smsmodel ['smscontent'],
						 * 'picurl' => 'http://www.bontion.com/em//Upload/image/2017-11-10/5a05a4f85dba5.png',
						 * 'url' => 'http://www.bontion.com/em/Content/editnotice/contentid/3/id/4.html'
						 * ),
						 * array (
						 * 'title' => $smsmodel ['smstitle'],
						 * 'description' => $smsmodel ['smscontent'],
						 * 'picurl' => 'http://www.bontion.com/em//Upload/image/2017-11-10/5a05a4f85dba5.png',
						 * 'url' => 'http://www.bontion.com/em/Content/editnotice/contentid/3/id/4.html'
						 * )
						 * );
						 */
						$result = send_wechat_custommsg ( $msgarray );
						if ($result == 0) { // 成功
							$this->ajaxReturn ( array (
									'status' => 1,
									'message' => '微信发送成功' 
							) );
						} else {
							$this->ajaxReturn ( array (
									'status' => 0,
									'message' => '微信发送失败,失败码:' . $result 
							) );
						}
						reset ( $msgarray );
					}
				} else {
					$this->ajaxReturn ( array (
							'status' => 0,
							'message' => '没有任何手机号关注过公众号' 
					) );
				}
			}
		}
	}
	
	// 指定身份发送短信
	public function sendSms() {
		$smsmodelid = I ( 'smsmodelid' ); // 短信模板
		$totalselect = I ( 'totalselect' ); // 选择楼宇
		$authresult = I ( 'authresult' ); // 住户状态
		$householdtype = I ( 'householdtype' ); // 住户身份
		$wechatlog = I ( 'wechatlog' ); // 微信登录记录
		if ($smsmodelid) {
			if ($totalselect && count ( $totalselect ) > 0) {
				if ($authresult && count ( $authresult ) > 0) {
					if ($householdtype && count ( $householdtype ) > 0) {
						if ($wechatlog) {
							$condition = array (); // 查询条件
							$condition ['HOUSE'] = array (
									'in',
									$totalselect 
							);
							$condition ['HOUSEHOLD_STATUS'] = array (
									'in',
									$householdtype 
							);
							$condition ['AUTH_RESULT'] = array (
									'in',
									$authresult 
							);
							if (count ( $wechatlog ) < 2) {
								// 有微信记录
								if ($wechatlog [0] == '0') {
									$condition ['openid'] = array (
											'exp',
											' is NULL' 
									);
								} else {
									$condition ['openid'] = array (
											'exp',
											' is not NULL' 
									);
								}
							}
							
							$House = M ( 'em_household' );
							$result = $House->join ( ' LEFT JOIN qw_member ON qw_em_household.TEL = qw_member.phone' )->field ( 'qw_member.openid,qw_em_household.*' )->where ( $condition )->select ();
							if ($result) {
								// 查询短信模板
								$smsmodel = M ( 'em_smsmodel' )->where ( 'id=' . $smsmodelid )->find ();
								if ($smsmodel) {
									
									$mobileArray = array ();
									foreach ( $result as $v ) {
										array_push ( $mobileArray, $v ['tel'] );
									}
									$smsresult = sendSmsMessage ( $mobileArray, $smsmodel ['smscontent'] . '【' . $smsmodel ['signname'] . '】' );
									$amount = 0;
									foreach ( $smsresult as $key => $value ) {
										if ($value == '0') { // 成功的记录
											$amount ++;
										}
									}
									if ($amount > 0) {
										// 保存发送短信日志
										$this->addwechatlog ( 0, $amount, $smscontent );
									}
									
									$this->ajaxReturn ( array (
											'status' => 1,
											'message' => $smsresult 
									) );
								}
							} else {
								$this->ajaxReturn ( array (
										'status' => 0,
										'message' => '没有查询到楼宇中符合发送条件的住户信息' 
								) );
							}
							
							// $condition['AUTH_RESULT']=array('in',$wechatlog);
						} else {
							$this->ajaxReturn ( array (
									'status' => 0,
									'message' => '没有选择微信登录记录' 
							) );
						}
					} else {
						$this->ajaxReturn ( array (
								'status' => 0,
								'message' => '没有选择住户身份' 
						) );
					}
				} else {
					$this->ajaxReturn ( array (
							'status' => 0,
							'message' => '没有选择住户状态' 
					) );
				}
			} else {
				$this->ajaxReturn ( array (
						'status' => 0,
						'message' => '没有选择楼宇' 
				) );
			}
		} else {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '没有选择短信模板' 
			) );
		}
	}
	
	// 短信发送记录
	public function smslogindex() {
		$p = isset ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : '1';
		$pagesize = 10; // 每页数量
		$offset = $pagesize * ($p - 1); // 计算记录偏移量
		$m = M ( 'em_smslog' );
		$count = $m->where ( 'msgtype=0' )->count ();
		$list = $m->where ( 'msgtype=0' )->order ( 'createtime desc' )->limit ( $offset . ',' . $pagesize )->select ();
		$page = new \Think\Page ( $count, $pagesize );
		$page = $page->show ();
		$data = $this->getMessageSurplus ();
		$this->assign ( 'data', $data );
		$this->assign ( 'list', $list );
		$this->assign ( 'page', $page );
		$this->display ();
	}
	// 获取短信剩余条目数
	private function getMessageSurplus() {
		$post_data = array ();
		$post_data ['sn'] = 'SDK_AAA_00227'; // 序列号
		$post_data ['password'] = '852172777'; // 密码
		$url = 'http://123.56.233.239:8080/msg-core-web/msg/balance';
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
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 ); // 如果需要将结果直接返回到变量里，那加上这句。
		$result = curl_exec ( $ch );
		$jsonObject = json_decode ( $result );
		return $jsonObject->data;
	}
	
	// 指定号码发送短信
	public function sendSmsByPhoneNumber() {
		$smsmodelid = I ( 'smsmodelid' );
		$phonenumbers = I ( 'phonenumbers' );
		if ($smsmodelid == '') {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '未选择短信模板' 
			) );
		} else {
			if ($phonenumbers == '') {
				$this->ajaxReturn ( array (
						'status' => 0,
						'message' => '没有手机号' 
				) );
			} else {
				$smsmodel = M ( 'em_smsmodel' )->where ( "id='$smsmodelid'" )->find ();
				$mobileArray = explode ( ",", $phonenumbers );
				$result = sendSmsMessage ( $mobileArray, $smsmodel ['smscontent'] . '【' . $smsmodel ['signname'] . '】' );
				
				$amount = 0;
				foreach ( $result as $key => $value ) {
					if ($value == "0") { // 成功的记录
						$amount ++;
					}
				}
				if ($amount > 0) {
					// 保存发送短信日志
					$smscontent = $smsmodel ['smscontent'] . '【' . $smsmodel ['signname'] . '】';
					$this->addwechatlog ( 0, $amount, $smscontent );
				}
				
				$this->ajaxReturn ( array (
						'status' => 1,
						'message' => $v2 
				) );
			}
		}
	}
	
	// 选择楼宇树
	public function buildingselected() {
		$list = M ( 'em_village' )->select ();
		if ($list) {
			$treeArray = array ();
			for($i = 0; $i < count ( $list ); $i ++) {
				$main = array ();
				array_push ( $main, array (
						'id' => 'p' . $list [$i] ['village_id'],
						'name' => $list [$i] ['village_name'] 
				) );
				// 查找子节点
				$arry = $this->selectSon ( 'p' . $list [$i] ['village_id'], $list [$i] ['village_id'] );
				$merge = array_merge ( $main, $arry );
				$treeArray = array_merge ( $treeArray, $merge );
			}
			;
			$this->ajaxReturn ( array (
					'status' => 1,
					'message' => $treeArray 
			) );
		} else {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '无数据' 
			) );
		}
	}
	
	// 查找子节点 Pid=父节点ID (楼宇id),2个表id可能重复，所有根目录id前加p
	private function selectSon($Pid, $fid) {
		$m = M ( 'em_building' );
		$select = ($info = $m->where ( "village='$fid'" )->select ());
		if ($select) { // 查找该父ID下的子ID{
			$list = array ();
			for($i = 0; $i < count ( $info ); $i ++) {
				$data = array ();
				$data ["id"] = $info [$i] ['building_id'];
				$data ["pId"] = $Pid;
				$data ["name"] = $info [$i] ['building_name'];
				array_push ( $list, $data ); // 加入子节点数组
			}
			;
			return $list; // 一次性返回子节点数组，他们成为同级子节点。
		} else {
			return $select;
		}
	}
}
