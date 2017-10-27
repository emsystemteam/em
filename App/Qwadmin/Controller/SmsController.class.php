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
	//短信模板管理
	public function smsindex() {
		$data ['smstype'] = isset ( $_POST ['smstype'] ) ? trim ( $_POST ['smstype'] ) : 0; // 默认短信
		$list = M ( 'em_smsmodel' )->where ( 'smstype=' . $data ['smstype'] )->select ();
		$this->assign('smstype',$data['smstype']);
		$this->assign ( 'list', $list );
		$this->display ();
	}
	//编辑短信
	public function edit(){
		$id = isset($_GET['id']) ? intval($_GET['id']) : false;
		if (!$id) {
			$this->error('参数错误！');
		}
		
		$model=M('em_smsmodel')->where('id='.$id)->find();
		$this->assign('model', $model);
		$this->display('form');
	}
	
	//添加或更新
	public function update()
	{
		$model=D('em_smsmodel');
		if(!empty($_POST)){
			$model->create();//收集表单数据
		}
		dump(I('post.status', '', 'intval'));
		/* 
		if (isset ( $model->id )) { // 更新
			$model->modifytime =date('y-m-d H:i:s',time());
			$model->modifier = session ( 'uid' );
			$flag=$model->save ();
			if($flag){
				$this->success("保存成功");
			}else{
				$this->success("保存失败");
			}
		} else { // 新增
			$model->createtime = date('y-m-d H:i:s',time());
			$model->creater = session ( 'uid' );
			$flag=$model->add ();
			if($flag){
				$this->success("创建成功");
			}else{
				$this->success("创建失败");
			}
		} */
	}
	
	
	public function add()
	{
		
		$option = M('auth_rule')->order('o ASC')->select();
		$option = $this->getMenu($option);
		$this->assign('option', $option);
		$this->display('form');
	}
}
