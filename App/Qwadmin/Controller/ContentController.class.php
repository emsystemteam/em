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
		$contenttype=M('em_dictionary')->where('dict_name=' . '"contenttype"' )->select();
		$this->assign ( 'contenttype', $contenttype);
		$this->assign ( 'list', $list );
		$this->assign ( 'page', $page );
		$this->display ();
	}
	
	// 模块管理明细-文章列表
	public function detail() {
		if (isset ( $_GET ['id'] )) {
			
			$data ['noticetitle'] = isset ( $_POST ['smstype'] ) ? trim ( $_POST ['smstype'] ) : 1; // 默认短信
			$id = intval ( $_GET ['id'] );
			$p = isset ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : '1';
			$pagesize = 10; // 每页数量
			$offset = $pagesize * ($p - 1); // 计算记录偏移量
			$m = M ( 'em_notice' );
			$condition['stauts'] = 1;
			$condition['contentid'] = $id;
			if(I('noticetitle')){
				$condition['noticetitle']=I('noticetitle');
			}
			$count = $m->where ($condition)->count ();
			$list = $m->where ($condition)->order ( 'istop,createtime desc' )->limit ( $offset . ',' . $pagesize )->select ();
			$page = new \Think\Page ( $count, $pagesize );
			$page = $page->show ();
			$content = M ( 'em_contentmanager' )->where ( 'status=1 and id=' . $id )->find ();
			$this->assign ( 'list', $list );
			$this->assign ( 'model', $content );
			$this->display ();
		} else {
			$this->error ( '没有找到任何文章信息' );
		}
	}
	// 文章关联小区
	public function addvillagetonotice() {
		$totalselect = $_POST ['totalselect']; // 选择小区
		$noticeid = $_POST ['noticeid']; // 文章id
		if (isset ( $totalselect ) && isset ( $noticeid )) {
			// 之前的关联信息删除
			$m = M ( 'em_noticetovillage' );
			$m->status = 0;
			$m->modifytime = date ( 'y-m-d H:i:s', time () );
			$m->modifier = session ( 'uid' );
			$m->where ( 'noticeid=' . $noticeid )->save ();
			// 添加新的关联信息
			$m->startTrans ();
			$result = true;
			foreach ( $totalselect as $villageid ) {
				$m->create ();
				$m->noticeid = $noticeid;
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
	
	// 获取关联文章的小区列表
	public function getvillagetonoticebyid() {
		$noticeid = $_POST ['noticeid']; // 文章id
		if (isset ( $noticeid )) {
			$list = M ( 'em_noticetovillage' )->join ( ' LEFT JOIN qw_em_village ON qw_em_village.VILLAGE_ID = qw_em_noticetovillage.villageid' )->field ( 'qw_em_village.VILLAGE_NAME,qw_em_noticetovillage.*' )->where ( 'status=1 and noticeid=' . $noticeid )->select ();
			$this->ajaxReturn ( array (
					'status' => 1,
					'message' => $list 
			) );
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
			$m = M ( 'em_noticetovillage' );
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
		$this->assign ( 'contentid',$id);
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
		$this->assign ( 'contentid',$_GET ['contentid']);
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
				$this->success ( "保存成功",'detail/id/'.I('contentid'));
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
				$this->success ( "创建成功" ,'detail/id/'.I('contentid'));
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
			$m->stauts =0;
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
	
	//保存内容
	public function updatecontent(){
		$model = D ( 'em_contentmanager' );
		if (! empty ( $_POST )) {
			$model->create (); // 收集表单数据
		}
		if (! empty ( $model->id )) { // 更新
			$model->modifytime = date ( 'y-m-d H:i:s', time () );
			$model->modifier = session ( 'uid' );
			$model->contenttitile= I('contenttitile');
			$model->contenttype = I('contenttype');
			$flag = $model->save ();
			if ($flag) {
				addlog ( '内容管理保存成功，ID：' . $model->id );
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
		} else { // 新增
			$model->createtime = date ( 'y-m-d H:i:s', time () );
			$model->creater = session ( 'uid' );
			$model->status = 1;
			$model->contenttitile= I('contenttitile');
			$model->contenttype = I('contenttype');
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