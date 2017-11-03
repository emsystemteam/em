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
	
	// 微信模板管理
	public function wechatsmsindex() {
		$m = M ( 'em_smsmodel' );
		$holdtype = M ( 'em_dictionary' )->where ( 'DICT_NAME=' . '"住户身份"' )->order ( 'CREATE_TIME desc' )->select ();
		$list = $m->where ( 'smstype=2 and status=1 and isapprove=1' )->order ( 'createtime desc' )->select ();
		$this->assign ( 'titlelist', $list );
		$this->assign ( 'holdtype', $holdtype );
		$this->display ();
	}
	// 短信模板管理
	public function smsindex() {
		$m = M ( 'em_smsmodel' );
		$holdtype = M ( 'em_dictionary' )->where ( 'DICT_NAME=' . '"住户身份"' )->order ( 'CREATE_TIME desc' )->select ();
		$list = $m->where ( 'smstype=1 and status=1 and isapprove=1' )->order ( 'createtime desc' )->select ();
		$this->assign ( 'titlelist', $list );
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
				$result = sendSmsMessage ( $mobileArray, $smsmodel ['smscontent'] );
				$this->ajaxReturn ( array (
						'status' => 1,
						'message' => $result 
				) );
			}
		}
	}
	// 指定身份发送微信
	public function sendWechatSms() {
		$smsmodelid = I ( 'smsmodelid' ); // 短信模板
		$totalselect = I ( 'totalselect' ); // 选择楼宇
		$authresult = I ( 'authresult' ); // 住户状态
		$householdtype = I ( 'householdtype' ); // 住户身份
		if ($smsmodelid) {
			if ($totalselect && count ( $totalselect ) > 0) {
				if ($authresult && count ( $authresult ) > 0) {
					if ($householdtype && count ( $householdtype ) > 0) {
						$condition = array (); // 查询条件
						$condition ['HOUSE'] = array (
								'in',
								$totalselect 
						);
						$condition ['AUTH_RESULT'] = array (
								'in',
								$authresult 
						);
						$House = M ( 'em_household' );
						$result = $House->where ( $condition )->select ();
						if ($result) {
							// 查询短信模板
							$smsmodel = M ( 'em_smsmodel' )->where ( 'id=' . $smsmodelid )->find ();
							if ($smsmodel) {
								$mobileArray = array ();
								foreach ( $result as $v ) {
									array_push ( $mobileArray, $v ['TEL'] );
								}
								// 微信发送方法需要替换
								$smsresult = sendSmsMessage ( $mobileArray, $smsmodel ['smscontent'] );
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
										array_push ( $mobileArray, $v ['TEL'] );
									}
									$smsresult = sendSmsMessage ( $mobileArray, $smsmodel ['smscontent'] );
									// 保存发送短信日志
									$model = D ( 'em_smslog' );
									$model->create ();
									$model->createtime = date ( 'y-m-d H:i:s', time () );
									$model->creater = session ( 'uid' );
									$model->smscontent=$smsmodel ['smscontent'] ;
									$model->amount=count($mobileArray);
									$flag = $model->add ();
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
	
	//短信发送记录
	public function smslogindex(){
		$p = isset ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : '1';
		$pagesize = 10; // 每页数量
		$offset = $pagesize * ($p - 1); // 计算记录偏移量
		$m = M ( 'em_smslog' );
		$list = $m->limit ( $offset . ',' . $pagesize )->select ();
		$page = new \Think\Page ( $count, $pagesize );
		$page = $page->show ();
		$this->assign ( 'list', $list );
		$this->assign ( 'page', $page );
		$this->display ();
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
				$result = sendSmsMessage ( $mobileArray, $smsmodel ['smscontent'] );
				// 保存发送短信日志
				$model = D ( 'em_smslog' );
				$model->create ();
				$model->createtime = date ( 'y-m-d H:i:s', time () );
				$model->creater = session ( 'uid' );
				$model->smscontent=$smsmodel ['smscontent'] ;
				$model->amount=count($mobileArray);
				$flag = $model->add ();
				$this->ajaxReturn ( array (
						'status' => 1,
						'message' => $flag 
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
						'id' => 'p' . $list [$i] ['VILLAGE_ID'],
						'name' => $list [$i] ['VILLAGE_NAME'] 
				) );
				// 查找子节点
				$arry = $this->selectSon ( 'p' . $list [$i] ['VILLAGE_ID'], $list [$i] ['VILLAGE_ID'] );
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
		if (($info = $m->where ( "VILLAGE='$fid'" )->select ())) // 查找该父ID下的子ID
{
			$list = array ();
			for($i = 0; $i < count ( $info ); $i ++) {
				$data = array ();
				$data ["id"] = $info [$i] ['BUILDING_ID'];
				$data ["pId"] = $Pid;
				$data ["name"] = $info [$i] ['BUILDING_NAME'];
				array_push ( $list, $data ); // 加入子节点数组
			}
			;
			return $list; // 一次性返回子节点数组，他们成为同级子节点。
		} else {
			return null;
		}
	}
}
