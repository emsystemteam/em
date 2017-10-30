<?php

/**
 *
 * 版权所有：liangc
 * 作    者：liangc
 * 日    期：2017-10-26
 * 版    本：1.0.0
 * 功能说明：短信、微信管理模块
 *
 **/
namespace Qwadmin\Controller;

class SmsController extends ComController {
	// 短信模板管理
	public function smsindex() {
		$data ['smstype'] = isset ( $_POST ['smstype'] ) ? trim ( $_POST ['smstype'] ) : 1; // 默认短信
		$p = isset ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : '1';
		$pagesize = 10; // 每页数量
		$offset = $pagesize * ($p - 1); // 计算记录偏移量
		$m = M ( 'em_smsmodel' );
		$count = $m->where ( 'smstype=' . $data ['smstype'] )->count ();
		$list = $m->where ( 'smstype=' . $data ['smstype'] )->order('createtime desc')->limit ( $offset . ',' . $pagesize )->select ();
		$page = new \Think\Page ( $count, $pagesize );
		$page = $page->show ();
		$this->assign ( 'list', $list );
		$this->assign ( 'smstype', $data ['smstype'] );
		$this->assign ( 'page', $page );
		$this->display ();
	}
	// 编辑短信
	public function edit() {
		$id = isset ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : false;
		if (! $id) {
			$this->error ( '参数错误！' );
		}
		$model = M ( 'em_smsmodel' )->where ( 'id=' . $id )->find ();
		$this->assign ( 'model', $model );
		$this->display ( 'form' );
	}
	
	// 添加或更新
	public function update() {
		$model = D ( 'em_smsmodel' );
		if (! empty ( $_POST )) {
			$model->create (); // 收集表单数据
		}
		if (! empty ( $model->id )) { // 更新
			$model->modifytime = date ( 'y-m-d H:i:s', time () );
			$model->modifier = session ( 'uid' );
			$model->status = I ( 'post.status', '', 'intval' );
			$flag = $model->save ();
			if ($flag) {
				$this->success ( "保存成功" );
				addlog ( '信息模板保存成功，ID：' . $model->id);
			} else {
				$this->success ( "保存失败" );
			}
		} else { // 新增
			$model->createtime = date ( 'y-m-d H:i:s', time () );
			$model->creater = session ( 'uid' );
			$model->status = 1;
			$flag = $model->add ();
			if ($flag) {
				$this->success ( "创建成功" );
				addlog ( '信息模板创建成功，ID：' . $model->id);
			} else {
				$this->success ( "创建失败" );
			}
		}
	}
	/**
	 * 添加短信模板
	 */
	public function add() {
		$model = M ( 'em_smsmodel' )->create ();
		$model ['status'] = 1;
		$model ['smstype'] = 1;
		$this->assign ( 'model', $model );
		$this->display ( 'form' );
	}
	
	/**
	 * 审核短信列表
	 */
	public function approveindex() {
		$p = isset ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : '1';
		$pagesize = 10; // 每页数量
		$offset = $pagesize * ($p - 1); // 计算记录偏移量
		$m = M ( 'em_smsmodel' );
		$count = $m->where ( 'status=1 and isapprove=0' )->count ();
		$list = $m->where ( 'status=1 and isapprove=0' )->limit ( $offset . ',' . $pagesize )->select ();
		$page = new \Think\Page ( $count, $pagesize );
		$page = $page->show ();
		$this->assign ( 'list', $list );
		$this->assign ( 'page', $page );
		$this->display ();
	}
	
	/**
	 * *
	 * 审核通过
	 */
	public function approve() {
		$id = isset ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : false;
		if ($id) {
			$model = M ( 'em_smsmodel' );
			$model->isapprove=1;
			$model->where ( 'id=' . $id )->save ();
			addlog ( '信息模板审核通过，ID：' . $id );
			die('1');
		} else {
			die ( '0' );
		}
	}
}
