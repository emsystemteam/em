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
		$this->assign ( 'list', $list );
		$this->assign ( 'page', $page );
		$this->display ();
	}
	
	// 模块管理明细-文章列表
	public function detail() {
		if (isset ( $_GET ['id'] )) {
			$id = intval ( $_GET ['id'] );
			$p = isset ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : '1';
			$pagesize = 10; // 每页数量
			$offset = $pagesize * ($p - 1); // 计算记录偏移量
			$m = M ( 'em_notice' );
			$count = $m->where ( 'stauts=1 and contentid=' . $id )->count ();
			$list = $m->where ( 'stauts=1 and contentid=' . $id )->order ( 'istop,createtime desc' )->limit ( $offset . ',' . $pagesize )->select ();
			if (! $list) {
				$this->error ( '没有找到任何文章信息' );
			}
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
}