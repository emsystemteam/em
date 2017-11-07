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
			$m=M('em_notice');
			$count = $m->where ( 'stauts=1 and contentid='.$id )->count ();
			$list = $m->where ( 'stauts=1 and contentid='.$id )->order ( 'istop,createtime desc' )->limit ( $offset . ',' . $pagesize )->select ();
			if(!$list){
				$this->error('没有找到任何文章信息');
			}
			$page = new \Think\Page ( $count, $pagesize );
			$page = $page->show ();
			$content=M('em_contentmanager')->where('id='.$id)->find();
			$this->assign ( 'list', $list );
			$this->assign ( 'model', $content);
			$this->display ();
		}else{
			$this->error('没有找到任何文章信息');
		}
	}
}