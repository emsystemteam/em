<?php
/**
 *
 * 作    者：xuluo
 * 日    期：2017-10-27
 * 版    本：1.0.0
 * 功能说明：小区管理。
 *
 **/

namespace Qwadmin\Controller;

class VotePaperController extends ComController
{
    public function edit()
    {
    	$vid = isset($_GET['vote_id']) ? intval($_GET['vote_id']) : false;
    	$prefix = C('DB_PREFIX');
    	$em_vote = M('em_vote')->field("{$prefix}em_vote.*")->where("{$prefix}em_vote.vote_id=$vid")->find();
    	$em_vote_paper = M('em_vote_paper')->field("{$prefix}em_vote_paper.*")->where("{$prefix}em_vote_paper.vote_id=$vid")->select();
    	foreach ($em_vote_paper as $k=>$v){
    		$votePaperId = $em_vote_paper[$k]['vote_paper_id'];
    		$em_vote_paper_option = M('em_vote_paper_option')->field("{$prefix}em_vote_paper_option.*")
    		->where("{$prefix}em_vote_paper_option.vote_paper_id=$votePaperId")->select();
    		$em_vote_paper[$k]['option'] = $em_vote_paper_option;
    	}
    	
    	$this->assign('emvote', $em_vote);
    	$this->assign('votePapers', $em_vote_paper);
//     	var_dump($em_vote_paper);
    	$this->display('form');
    }

    public function view(){
    	$vid = isset($_GET['vote_id']) ? intval($_GET['vote_id']) : false;
    	$prefix = C('DB_PREFIX');
    	$em_vote = M('em_vote')->field("{$prefix}em_vote.*")->where("{$prefix}em_vote.vote_id=$vid")->find();
    	$em_vote_paper = M('em_vote_paper')->field("{$prefix}em_vote_paper.*")->where("{$prefix}em_vote_paper.vote_id=$vid")->select();
    	foreach ($em_vote_paper as $k=>$v){
    		$votePaperId = $em_vote_paper[$k]['vote_paper_id'];
    		$em_vote_paper_option = M('em_vote_paper_option')->field("{$prefix}em_vote_paper_option.*")
    		->where("{$prefix}em_vote_paper_option.vote_paper_id=$votePaperId")->select();
    		$em_vote_paper[$k]['option'] = $em_vote_paper_option;
    	}
    	
    	$this->assign('emvote', $em_vote);
    	$this->assign('votePapers', $em_vote_paper);
    	$this->display('detail');
    }
    
    public function makeUp(){
    	$vid = isset($_GET['vote_id']) ? intval($_GET['vote_id']) : false;
    	
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
    	
    	//如果不是超级管理员，需要添加当前登录用户数据权限
    	if($uid != 1){
    		$currentVillage = $this->getCurrentVillage($uid);
    	}
    	
    	$prefix = C('DB_PREFIX');
    	$em_vote = M('em_vote')->field("{$prefix}em_vote.*")->where("{$prefix}em_vote.vote_id=$vid")->find();
    	$em_vote_paper = M('em_vote_paper')->field("{$prefix}em_vote_paper.*")->where("{$prefix}em_vote_paper.vote_id=$vid")->select();
    	foreach ($em_vote_paper as $k=>$v){
    		$votePaperId = $em_vote_paper[$k]['vote_paper_id'];
    		$em_vote_paper_option = M('em_vote_paper_option')->field("{$prefix}em_vote_paper_option.*")
    		->where("{$prefix}em_vote_paper_option.vote_paper_id=$votePaperId")->select();
    		$em_vote_paper[$k]['option'] = $em_vote_paper_option;
    	}
    	
    	if($currentVillage){
    		$searchMap[$prefix.'em_village.village_id'] = array('eq',$currentVillage);
    	}
    	$villages = M('em_village')->field("{$prefix}em_village.village_id,{$prefix}em_village.village_name")->where($searchMap)->select();
    	
    	$this->assign('villages',$villages);
    	$this->assign('emvote', $em_vote);
    	$this->assign('votePapers', $em_vote_paper);
    	$this->display('makeUp');
    }
    
    public function makeUpSave(){
    	
    }

    public function add()
    {
    	$vid = isset($_GET['vote_id']) ? intval($_GET['vote_id']) : false;
    	$prefix = C('DB_PREFIX');
    	$emVote = M('em_vote');
    	$em_vote = $emVote->field("{$prefix}em_vote.*")->where("{$prefix}em_vote.vote_id=$vid")->find();
    	$this->assign('emvote', $em_vote);
        $this->display('form');
    }
    
    public function save()
    {
    	M()->startTrans();
    	$vid = isset($_REQUEST['vote_id']) ? $_REQUEST['vote_id'] : false;
    	$timu = isset($_REQUEST['timu']) ? $_REQUEST['timu'] : false;
    	$list = isset($_REQUEST['list']) ? $_REQUEST['list'] : false;
		
//     	var_dump($timu);
//     	var_dump($list);
    	
    	if(!$vid){
    		$this->error("投票ID为空！");
    	}
    	
    	$prefix = C('DB_PREFIX');
    	//先删除原来的
    	$em_vote_paper = M('em_vote_paper')->field("{$prefix}em_vote_paper.*")->where("{$prefix}em_vote_paper.vote_id=$vid")->select();
    	
    	$paperCount = count($em_vote_paper);
    	if($paperCount >　0){
    		//如果投票下的问卷不为空，那么删除原来的问卷
    		foreach ($em_vote_paper as $k=>$v){
    			$votePaperId = $em_vote_paper[$k]['vote_paper_id'];
    			$map['vote_paper_id'] = array('eq', $votePaperId);
    			//如果投票下的问卷不为空，那么删除原来的问卷
    			$votePaper = M('em_vote_paper_option')->where("vote_paper_id = $votePaperId")->find();
    			if($votePaper){
    				if(M('em_vote_paper_option')->where($map)->delete()){
    					addlog('删除投票，ID：' . $vid . '，问卷ID：' . $votePaperId . '下的所有选项');
    				}else{
    					addlog('删除投票，ID：' . $vid . '，问卷ID：' . $votePaperId . '下的所有选项异常');
    					M()->rollback();
    					$this->error('修改问卷异常!');
    				}
    			}
    		}
    		$map2['vote_id'] = array('eq', $vid);
    		if(M('em_vote_paper')->where($map2)->delete()){
    			addlog('删除投票，ID：' . $vid . '下的所有问卷');
    		}else{
    			addlog('删除投票，ID：' . $vid . '下的所有问卷异常');
    			M()->rollback();
    			$this->error('修改问卷异常!');
    		}
    	}else{
    		//如果原来投票下的问卷为空，那么认为是新增问卷，修改投票状态为未开始
    		$voteStatus = M('em_dictionary')->where("dict_name = 'voteStatus' and dict_key = '未开始'")->find();
    		if($voteStatus){
    			$data['vote_status'] = $voteStatus['dict_value'];
    			if(!M('em_vote')->data($data)->where("vote_id=$vid")->save()){
    				addlog('修改投票状态为未开始状态异常');
    				M()->rollback();
    				$this->error('修改问卷异常!');
    			}
    		}else{
    			addlog('查询未开始的投票状态异常');
    			M()->rollback();
    			$this->error('修改问卷异常!');
    		}
    	}
    	
    	if(!$timu){
    		//如果题目为空，那么认为清空了所有问卷，修改投票状态为未录入
    		$voteStatus1 = M('em_dictionary')->where("dict_name = 'voteStatus' and dict_key = '未录入'")->find();
    		if($voteStatus1){
    			$data['vote_status'] = $voteStatus1['dict_value'];
    			if(!M('em_vote')->data($data)->where("vote_id=$vid")->save()){
    				addlog('修改投票状态为未录入状态异常');
    				M()->rollback();
    				$this->error('修改问卷异常!');
    			}
    		}else{
    			addlog('查询未录入的投票状态异常');
    			M()->rollback();
    			$this->error('修改问卷异常!');
    		}
    	}
    	
    	//再添加新的
    	foreach ($timu as $k=>$v){
    		$v['vote_id'] = $vid;
    		$votePaperId = M('em_vote_paper')->data($v)->add();
    		if(!$votePaperId){
    			M()->rollback();
    			addlog('添加问卷异常!');
    			$this->error('添加问卷异常!');
    		}
    		$paperType = $v['question_type'];
    		if($paperType != 3){
	    		$paperOption = $list[$k];
	    		foreach ($paperOption as $k2=>$v2){
	    			$options = explode('|_|', $v2);
	    			$paperOptions['option_titile'] = $options[0];
	    			$paperOptions['option_desc'] = $options[1];
	    			$paperOptions['option_pic'] = $options[2];
	    			$paperOptions['vote_paper_id'] = $votePaperId;
	    			$votePaperOptionId = M('em_vote_paper_option')->data($paperOptions)->add();
	    			if(!$votePaperOptionId){
	    				M()->rollback();
	    				addlog('添加问卷选项异常!');
	    				$this->error('添加选项异常!');
	    			}
	    		}
    		}
    	}
    	//$em_vote = $emVote->field("{$prefix}em_vote.*")->where("{$prefix}em_vote.vote_id=$vid")->find();
    	M()->commit();
    	$this->success('操作成功！','/em/vote/index');
    }
    
    /**
     * 小区、楼宇级联，根据选择的小区查找楼宇
     * @param string $villageId 小区id
     *
     */
    public function changeVillage($villageId= FALSE)
    {
    	$emBuilding = M('em_building');
    	$thisBuilding = $emBuilding->field("building_id,building_name")->where("village = $villageId")->select();
    	$this->ajaxReturn($thisBuilding);
    }
    
    /**
     * 楼宇、单元级联，根据选择的楼宇查找单元
     * @param string $buildingId 楼宇id
     *
     */
    public function changeBuilding($buildingId= FALSE)
    {
    	$thisUnit = M('em_unit')->field("unit_id,unit_name")->where("building = $buildingId")->select();
    	$this->ajaxReturn($thisUnit);
    }
    
    /**
     * 楼宇、单元级联，根据选择的楼宇查找单元
     * @param string $buildingId 楼宇id
     *
     */
    public function changeUnit($unitId= FALSE)
    {
    	$thisHouse = M('em_house')->field("house_id,house_name")->where("unit = $unitId")->select();
    	$this->ajaxReturn($thisHouse);
    }
    
    public function changeHouse($houseId= FALSE)
    {
    	$prefix = C('DB_PREFIX');
    	$thisHousehold = M('em_household')->field("{$prefix}em_household.household_id,{$prefix}em_household.household_name")
	    	->join("left join {$prefix}em_house_household hh ON {$prefix}em_household.household_id = hh.household_id")
	    	->where("hh.house_id = $houseId")->select();
    	$this->ajaxReturn($thisHousehold);
    }
    
    public function changeHousehold($householdId= FALSE)
    {
    	$prefix = C('DB_PREFIX');
    	$thisHousehold = M('em_household')->field("{$prefix}em_household.household_id,{$prefix}em_household.TEL")
    	->where("{$prefix}em_household.household_id = $householdId")->select();
    	$this->ajaxReturn($thisHousehold);
    }
}
