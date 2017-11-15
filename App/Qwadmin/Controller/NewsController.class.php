<?php

/**
 *
 * 版权所有：乐助科技
 * 作    者：liangc
 * 日    期：2017-11-14
 * 版    本：1.0.0
 * 功能说明：公告通知
 *
 **/
namespace Qwadmin\Controller;

class NewsController extends ComController {
	
	// 内容管理首页
	public function index() {
		$p = isset ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : '1';
		$pagesize = 10; // 每页数量
		$offset = $pagesize * ($p - 1); // 计算记录偏移量
		$m = M ( 'em_news' );
		$count = $m->where ( 'status=1' )->count ();
		$list = $m->where ( 'status=1' )->order ( 'createtime desc' )->limit ( $offset . ',' . $pagesize )->select ();
		$page = new \Think\Page ( $count, $pagesize );
		$page = $page->show ();
		$this->assign ( 'list', $list );
		$this->assign ( 'page', $page );
		$this->display ();
	}
	
	// 添加图文素材
	public function add() {
		$this->display ( 'form' );
	}
	
	// 模块管理明细-文章列表
	public function edit() {
		if (isset ( $_GET ['id'] )) {
			$id = intval ( $_GET ['id'] );
			$m = M ( 'em_news' );
			$condition ['id'] = $id;
			$model= $m->where ( 'id=' . $id )->find();
			$this->assign ( 'model', $model );
			$this->display ( 'form' );
		} else {
			$this->error ( '没有找到任何图文素材信息' );
		}
	}
	
	// 添加或更新图文
	public function updatenews() {
		$model = D ( 'em_news' );
		$rule = array (
				array (
						'newstitle',
						'require',
						'图文标题不能为空！' 
				),
				array (
						'newspicture',
						'require',
						'图文图片不能为空！' 
				),
				array (
						'newssummary',
						'require',
						'图文简介不能为空！' 
				),
				array (
						'newscontent',
						'require',
						'图文正文不能为空！' 
				) 
		);
		if (! $model->validate ( $rules )->create ()) {
			exit ( $model->getError () );
		} else {
			if (! empty ( $model->id )) { // 更新
				$model->modifytime = date ( 'y-m-d H:i:s', time () );
				$model->modifier = session ( 'uid' );
				$flag = $model->save ();
				if ($flag) {
					$this->success ( "保存成功", 'index' );
					addlog ( '图文素材创建成功，ID：' . $model->id );
				} else {
					$this->success ( "保存失败" );
				}
			} else { // 新增
				$model->createtime = date ( 'y-m-d H:i:s', time () );
				$model->creater = session ( 'uid' );
				$model->status = 1;
				$flag = $model->add ();
				if ($flag) {
					$this->success ( "创建成功", 'index' );
					addlog ( '图文素材保存成功，ID：' . $model->id );
				} else {
					$this->error ( "创建失败" );
				}
			}
		}
	}
	
	// 删除文章
	public function deletenews() {
		$id = isset ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : false;
		if (! $id) {
			$this->ajaxReturn ( array (
					'status' => 0,
					'message' => '参数传递错误' 
			) );
		} else {
			$m = M ( 'em_news' );
			$m->stauts = 0;
			$m->modifytime = date ( 'y-m-d H:i:s', time () );
			$m->modifier = session ( 'uid' );
			$flag = $m->where ( 'id=' . $id )->save ();
			if ($flag) {
				addlog ( '删除图文素材成功，ID：' . $m->id );
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