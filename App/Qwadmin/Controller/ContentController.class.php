<?php

/**
 *
 * 版权所有：乐助科技
 * 作    者：liangc
 * 日    期：2017-11-6
 * 版    本：1.0.0
 * 功能说明：公告通知
 *
 **/
namespace Qwadmin\Controller;

class ContentController extends ComController {
	// 内容管理首页
	public function index() {
		$p = isset ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : '1';
		$pagesize = 10; // 每页数量
		$offset = $pagesize * ($p - 1); // 计算记录偏移量
		$m = M ( 'em_contentmanager' );
		$count = $m->where ( 'status=1' )->count ();
		$list = $m->where ( 'status=1' )->order ( 'createtime desc' )->limit ( $offset . ',' . $pagesize )->select ();
		$page = new \Think\Page ( $count, $pagesize );
		$page = $page->show ();
		$contenttype = M ( 'em_dictionary' )->where ( 'dict_name=' . '"contenttype"' )->select ();
		$this->assign ( 'contenttype', $contenttype );
		$this->assign ( 'list', $list );
		$this->assign ( 'page', $page );
		$this->display ();
	}
	
	// 模块管理明细-文章列表
	public function detail() {
		if (isset ( $_GET ['id'] )) {
			$templates = M ( 'em_dictionary' )->where ( 'dict_name=' . '"template"' )->select ();
			$authResult = M ( 'em_dictionary' )->where ( 'DICT_NAME=' . '"authResult"' )->order ( 'CREATE_TIME desc' )->select ();
			$holdtype = M ( 'em_dictionary' )->where ( 'DICT_NAME=' . '"householdStatus"' )->order ( 'CREATE_TIME desc' )->select ();
			$data ['noticetitle'] = isset ( $_POST ['smstype'] ) ? trim ( $_POST ['smstype'] ) : 1; // 默认短信
			$id = intval ( $_GET ['id'] );
			$p = isset ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : '1';
			$pagesize = 10; // 每页数量
			$offset = $pagesize * ($p - 1); // 计算记录偏移量
			$m = M ( 'em_notice' );
			$condition ['stauts'] = 1;
			$condition ['contentid'] = $id;
			if (I ( 'noticetitle' )) {
				$condition ['noticetitle'] = array (
						'like',
						'%' . I ( 'noticetitle' ) . '%' 
				);
			}
			$count = $m->where ( $condition )->count ();
			$list = $m->where ( $condition )->order ( 'istop,createtime desc' )->limit ( $offset . ',' . $pagesize )->select ();
			$page = new \Think\Page ( $count, $pagesize );
			$page = $page->show ();
			$content = M ( 'em_contentmanager' )->where ( 'status=1 and id=' . $id )->find ();
			$this->assign ( 'list', $list );
			$this->assign ( 'model', $content );
			$this->assign ( 'page', $page );
			$this->assign ( 'templates', $templates );
			$this->assign ( 'authResults', $authResult );
			$this->assign ( 'holdtype', $holdtype );
			$this->display ();
		} else {
			$this->error ( '没有找到任何文章信息' );
		}
	}
	// 内容关联小区
	public function addvillagetocontent() {
		$totalselect = $_POST ['totalselect']; // 选择小区
		$contentid = $_POST ['contentid']; // 内容id
		if (isset ( $totalselect ) && isset ( $contentid )) {
			// 之前的关联信息删除
			$m = M ( 'em_contenttovillage' );
			$m->status = 0;
			$m->modifytime = date ( 'y-m-d H:i:s', time () );
			$m->modifier = session ( 'uid' );
			$m->where ( 'contentid=' . $contentid )->save ();
			// 添加新的关联信息
			$m->startTrans ();
			$result = true;
			foreach ( $totalselect as $villageid ) {
				$m->create ();
				$m->contentid = $contentid;
				$m->villageid = $villageid;
				$m->status = 1;
				$m->createtime = date ( 'y-m-d H:i:s', time () );
				$m->creater = session ( 'uid' );
				if ($m->add () == 0) {
					$result = false;
				}
			}
			if ($flag) {
				// 提交事务
				$m->commit ();
			} else {
				// 事务回滚
				$m->rollback ();
			}
			$this->ajaxReturn ( array (
					'status' => 1,
					'message' => '保存成功' 
			) );
		} else {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '参数传递错误' 
			) );
		}
	}
	// 获取文章内容
	public function getContentbyId() {
		$contentid = I ( 'contentid' ); // 内容id
		if ($contentid) {
			$content = M ( 'em_contentmanager' )->where ( 'status=1 and id=' . $contentid )->find ();
			if ($content) {
				$this->ajaxReturn ( array (
						'status' => 1,
						'message' => $content 
				) );
			} else {
				$this->ajaxReturn ( array (
						'status' => 0,
						'message' => '没有查到文章信息' 
				) );
			}
		} else {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '参数传递错误' 
			) );
		}
	}
	// 指定楼宇发送微信
	public function sendWechatSms() {
		$noticeid = I ( 'noticeid' ); // 文章内容
		$template = I ( 'template' ); // 微信模板消息
		$title = I ( 'title' ); // 标题
		$summary = I ( 'summary' ); // 简介
		$totalselect = I ( 'totalselect' ); // 选择楼宇
		$authresult = I ( 'authresult' ); // 住户状态
		$householdtype = I ( 'householdtype' ); // 住户身份
		if ($template && $noticeid) {
			if ($title &&$summary &&trim ( $title ) != "" &&trim ( $summary ) != "") {
				if ($totalselect && count ( $totalselect ) > 0) {
					$condition ['qw_em_house_household.house_id'] = array (
							'in',
							$totalselect 
					);
					$condition ['household_status'] = array (
							'in',
							$householdtype 
					);
					$condition ['auth_result'] = array (
							'in',
							$authresult 
					);
					$M = M ( 'member' )->where ( 'openid is not null' );
					$Member = $M->join ( 'qw_em_household on qw_em_household.tel=qw_member.phone', 'left' )
								->join ( 'qw_em_house_household on qw_em_house_household.household_id=qw_em_household.household_id', 'left' )
								->join ( 'qw_em_house on qw_em_house.house_id=qw_em_house_household.house_id', 'left' )
								->where ( $condition )->distinct ( true )
								->field ( 'qw_member.openid,qw_em_household.household_name' )
								->select ();
					if (count ( $Member ) == 0) {
						$this->ajaxReturn ( array (
								'status' => 0,
								'message' => '没有找到可以发送的微信信息' 
						) );
					} else {
						$error=0;
						foreach ( $Member as $row ) {
							$message = array (
									'touser' => $row [openid],
									'template_id' => $template,
									'url' => $_SERVER ['HTTP_HOST'] . __ROOT__ . '/mobile/detail/id/' . $noticeid . '.html',
									'topcolor' => '#7B68EE',
									'data' => array (
											'first' => array (
													'value' => $title,
													'color' => "#743A3A" 
											),
											'keyword1' => array (
													'value' => $summary,
													'color' => '#743A3A' 
											),
											'keyword2' => array (
													'value' => $row ['household_name'],
													'color' => '#743A3A' 
											),
											'remark' => array (
													'value' => '',
													'color' => '#743A3A' 
											) 
									) 
							);
							$result = send_wechat_template ( $message);
							if ($result != 0) {
								$error++;
								
							} 
						}
						if($error>0){
							$this->ajaxReturn ( array (
									'status' => 0,
									'message' => '有'.$error.'条发送失败'
							) );
						}else{
							$this->ajaxReturn ( array (
									'status' => 1,
									'message' => '发送成功'
							) );
						}
						addlog ( '微信发送文章成功，ID：' . $row ['household_name'] );
					}
				} else {
					$this->ajaxReturn ( array (
							'status' => 0,
							'message' => '没有关联楼宇' 
					) );
				}
			} else {
				$this->ajaxReturn ( array (
						'status' => 0,
						'message' => '标题或简介不能为空' 
				) );
			}
		} else {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '微信模板未选择或者文章没选择' 
			) );
		}
	}
	
	// 获取关联内容的小区列表
	public function getvillagetocontentbyid() {
		$contentid = $_POST ['contentid']; // 内容id
		if (isset ( $contentid )) {
			$content = M ( 'em_contentmanager' )->where ( 'id=' . $contentid )->find ();
			if (! $content) {
				$this->ajaxReturn ( array (
						'status' => 0,
						'message' => '没有找到内容信息' 
				) );
			} else {
				if ($content ['allowallvillage'] == 1) { // 默认绑定所有小区
					$list = M ( 'em_village' )->field ( 'qw_em_village.*,qw_em_village.village_id as villageid' )->select ();
					$this->ajaxReturn ( array (
							'status' => 1,
							'message' => $list,
							'allowallvillage' => 1 
					) );
				} else {
					$list = M ( 'em_contenttovillage' )->join ( ' LEFT JOIN qw_em_village ON qw_em_village.VILLAGE_ID = qw_em_contenttovillage.villageid' )->field ( 'qw_em_village.VILLAGE_NAME,qw_em_contenttovillage.*' )->where ( 'status=1 and contentid=' . $contentid )->select ();
					$this->ajaxReturn ( array (
							'status' => 1,
							'message' => $list,
							'allowallvillage' => 0 
					) );
				}
			}
		} else {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '参数传递错误' 
			) );
		}
	}
	
	// 删除关联文章的小区
	public function deleteVillage() {
		$id = $_POST ['id']; // 文章id
		if (isset ( $id )) {
			$m = M ( 'em_contenttovillage' );
			$m->status = 0;
			$m->modifytime = date ( 'y-m-d H:i:s', time () );
			$m->modifier = session ( 'uid' );
			$flag = $m->where ( 'id=' . $id )->save ();
			if ($flag) {
				$this->ajaxReturn ( array (
						'status' => 1,
						'message' => '保存成功' 
				) );
			} else {
				$this->ajaxReturn ( array (
						'status' => 0,
						'message' => '保存失败' 
				) );
			}
		} else {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '参数传递错误' 
			) );
		}
	}
	// 新增文章
	public function addnotice() {
		$id = isset ( $_GET ['contentid'] ) ? intval ( $_GET ['contentid'] ) : false; // 内容管理id-contentid
		if (! $id) {
			$this->error ( '参数错误！' );
		}
		$model = M ( 'em_contentmanager' )->where ( 'id=' . $id )->find ();
		$this->assign ( 'contentid', $id );
		$this->display ( 'form' );
	}
	// 编辑文章
	public function editnotice() {
		$id = isset ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : false;
		if (! $id) {
			$this->error ( '参数错误！' );
		}
		$model = M ( 'em_notice' )->where ( 'id=' . $id )->find ();
		$this->assign ( 'model', $model );
		$this->assign ( 'contentid', $_GET ['contentid'] );
		$this->display ( 'form' );
	}
	// 添加或更新文章
	public function updatenotice() {
		$model = D ( 'em_notice' );
		if (! empty ( $_POST )) {
			$model->create (); // 收集表单数据
		}
		if (! empty ( $model->id )) { // 更新
			$model->modifytime = date ( 'y-m-d H:i:s', time () );
			$model->modifier = session ( 'uid' );
			$flag = $model->save ();
			if ($flag) {
				$this->success ( "保存成功", 'detail/id/' . I ( 'contentid' ) );
				addlog ( '信息模板保存成功，ID：' . $model->id );
			} else {
				$this->success ( "保存失败" );
			}
		} else { // 新增
			$model->createtime = date ( 'y-m-d H:i:s', time () );
			$model->creater = session ( 'uid' );
			$model->status = 1;
			$flag = $model->add ();
			if ($flag) {
				$this->success ( "创建成功", 'detail/id/' . I ( 'contentid' ) );
				addlog ( '文章创建成功，ID：' . $model->id );
			} else {
				$this->error ( "创建失败" );
			}
		}
	}
	
	// 删除文章
	public function deletenotice() {
		$id = isset ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : false;
		if (! $id) {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '参数传递错误' 
			) );
		} else {
			$m = M ( 'em_notice' );
			$m->stauts = 0;
			$m->modifytime = date ( 'y-m-d H:i:s', time () );
			$m->modifier = session ( 'uid' );
			$flag = $m->where ( 'id=' . $id )->save ();
			if ($flag) {
				addlog ( '删除成功，ID：' . $m->id );
				$this->ajaxReturn ( array (
						'status' => 1,
						'message' => '删除成功' 
				) );
			} else {
				$this->ajaxReturn ( array (
						'status' => 0,
						'message' => '删除错误' 
				) );
			}
		}
	}
	
	// 删除内容
	public function deletecontent() {
		$id = isset ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : false;
		if (! $id) {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '参数传递错误' 
			) );
		} else {
			$m = M ( 'em_contentmanager' );
			$m->status = 0;
			$m->modifytime = date ( 'y-m-d H:i:s', time () );
			$m->modifier = session ( 'uid' );
			$flag = $m->where ( 'id=' . $id )->save ();
			if ($flag) {
				addlog ( '删除成功，ID：' . $m->id );
				$this->ajaxReturn ( array (
						'status' => 1,
						'message' => '删除成功' 
				) );
			} else {
				$this->ajaxReturn ( array (
						'status' => 0,
						'message' => '删除错误' 
				) );
			}
		}
	}
	
	// 保存内容
	public function updatecontent() {
		$model = D ( 'em_contentmanager' );
		if (! empty ( $_POST )) {
			$model->create (); // 收集表单数据
		}
		if ($model->contenttitile == "" || trim ( $model->contenttitile ) == "") {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '内容标题不为空' 
			) );
		} else {
			if (! empty ( $model->id )) { // 更新
				$model->modifytime = date ( 'y-m-d H:i:s', time () );
				$model->modifier = session ( 'uid' );
				$flag = $model->save ();
				if ($flag) {
					addlog ( '内容管理保存成功，ID：' . $model->id );
					$this->ajaxReturn ( array (
							'status' => 1,
							'message' => '保存内容成功' 
					) );
				} else {
					$this->ajaxReturn ( array (
							'status' => 0,
							'message' => '保存内容失败' 
					) );
				}
			} else { // 新增
				$model->createtime = date ( 'y-m-d H:i:s', time () );
				$model->creater = session ( 'uid' );
				$model->status = 1;
				$flag = $model->add ();
				if ($flag) {
					addlog ( '创建内容成功，ID：' . $model->id );
					$this->ajaxReturn ( array (
							'status' => 1,
							'message' => '创建内容成功' 
					) );
				} else {
					$this->ajaxReturn ( array (
							'status' => 0,
							'message' => '创建内容失败' 
					) );
				}
			}
		}
	}
	
	/**
	 * webuploader 上传文件
	 */
	public function ajax_upload() {
		// 根据自己的业务调整上传路径、允许的格式、文件大小
		$resulut = ajax_upload ( '/Upload/image/' );
		if (count ( $resulut ) > 0) {
			$this->ajaxReturn ( array (
					'status' => 1,
					'message' => $resulut 
			) );
		} else {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '附件上传失败' 
			) );
		}
	}
}