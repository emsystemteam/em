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
	// 短信模板管理
	public function smsindex() {
		$m = M ( 'em_smsmodel' );
		$list = $m->where ( 'smstype=1 and status=1' )->order('createtime desc')->select();
		$this->assign ( 'titlelist', $list);
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
	
	//选择楼宇树
	public function buildingselected(){
		$list = M ( 'em_village' )->select();
		if($list){
			$treeArray=array();
			for ($i=0; $i < count($list) ; $i++)
			{
				$main=array();
				array_push($main,array('id'=>'p'.$list[$i]['VILLAGE_ID'],'name'=>$list[$i]['VILLAGE_NAME']));
				 //查找子节点
				$arry=$this->selectSon('p'.$list[$i]['VILLAGE_ID'],$list[$i]['VILLAGE_ID']);
				$merge=array_merge($main,$arry);
				$treeArray=array_merge($treeArray,$merge);
			};
			$this->ajaxReturn(array(
					'status' =>1,
					'message'=>$treeArray
			));
		}else{
			$this->ajaxReturn(array(
					'status' =>0,
					'message'=>'无数据'
			));
		}
	}
	
	//查找子节点        Pid=父节点ID (楼宇id),2个表id可能重复，所有根目录id前加p
	private function selectSon($Pid,$fid){
		$m=M('em_building'); 
		if(($info=$m->where("VILLAGE='$fid'")->select())) //查找该父ID下的子ID
		{
			$list=array();
			for ($i=0; $i < count($info) ; $i++)
			{
				$data=array();
				$data["id"]=$info[$i]['BUILDING_ID'];
				$data["pId"]=$Pid;
				$data["name"]=$info[$i]['BUILDING_NAME'];
				array_push($list, $data);//加入子节点数组
			};
			return $list;//一次性返回子节点数组，他们成为同级子节点。
		}
		else
		{
			return null;
		} 
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
