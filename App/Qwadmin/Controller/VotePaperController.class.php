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
    
    /**
     * 投票补录
     */
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
   
    /**
     * 投票补录保存
     */
    public function makeUpSave(){
    	M()->startTrans();
    	
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
    	
    	$prefix = C('DB_PREFIX');
    	$vote_id = isset($_POST['vote_id']) ? trim($_POST['vote_id']) : false;
    	if(!$vote_id){
    		$this->error('投票不能为空！');
    	}
    	
    	$village = isset($_POST['village']) ? trim($_POST['village']) : false;
    	if(!$village){
    		$this->error('所属小区不能为空！');
    	}
    	$villageName =  M('em_village')->field("{$prefix}em_village.village_name")->where("village_id = $village")->find();
    	
    	$building = isset($_POST['building']) ? trim($_POST['building']) : false;
    	if(!$building){
    		$this->error('所属楼宇不能为空！');
    	}
    	$buildingName =  M('em_building')->field("{$prefix}em_building.building_name")->where("building_id = $building")->find();
    	
    	
    	$unit = isset($_POST['unit']) ? trim($_POST['unit']) : false;
    	if(!$unit){
    		$this->error('所属单元不能为空！');
    	}
    	$unitName =  M('em_unit')->field("{$prefix}em_unit.unit_name")->where("unit_id = $unit")->find();
    	
    	
    	$house = isset($_POST['house']) ? trim($_POST['house']) : false;
    	if(!$house){
    		$this->error('所属房屋不能为空！');
    	}
    	$houseName =  M('em_house')->field("{$prefix}em_house.house_name")->where("house_id = $house")->find();
    	
    	
    	$household = isset($_POST['household']) ? trim($_POST['household']) : false;
    	if(!$household){
    		$this->error('住户不能为空！');
    	}
    	$householdName =  M('em_household')->field("{$prefix}em_household.household_name")->where("household_id = $household")->find();
    	
    	$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    	if(!$phone){
    		$this->error('手机号不能为空！');
    	}
    	
    	$member =  M('member')->field("{$prefix}member.uid,{$prefix}member.user")->where("phone = $phone")->find();
    	$data['user_id'] = $member['uid'];
    	$data['user_name'] = $member['user'];
    	$data['village'] = $villageName['village_name'];
    	$data['building'] = $buildingName['building_name'];
    	$data['unit'] = $unitName['unit_name'];
    	$data['house'] = $houseName['house_name'];
    	$data['household'] = $householdName['household_name'];
    	$data['tel'] = $phone;
    	
    	$data['modify_time'] = date('Y-m-d H:i:s',time());
    	$data['vote_type'] = 2;//补录
    	$data['vote_id'] = $vote_id;
    	$data['operator'] = $uid;
    	$time = isset($_POST['tx_time']) ? trim($_POST['tx_time']) : null;
    	if(!empty($time)){
    		$data['create_time'] = $time;
    	}
    	
    	$ans = isset($_REQUEST['ans']) ? $_REQUEST['ans'] : false;
    	foreach($ans as $k=>$v){
    		$paperId = $k;
    		$data['vote_paper_id'] = $paperId;
    		if(is_array($v)){
	    		$paperOptions = $v;
	    		$optionValue = '';
	    		foreach($paperOptions as $k1=>$v1){
	    			$optionValue = $optionValue . ',' . $v1;
	    		}
	    		$optionValue = substr($optionValue, 1);
	    		$data['option_value'] = $optionValue;
	    		$result = M('em_vote_result')->data($data)->add();
	    		if(!$result){
	    			M()->rollback();
	    		}
    		}else{
    			$data['option_value'] = '';
    			$data['answer'] = $v;
    			$result = M('em_vote_result')->data($data)->add();
    			if(!$result){
    				M()->rollback();
    			}
    		}
    	}
    	M()->commit();
    	$this->success('补录成功！','/em/vote/index');
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
    		var_dump($voteStatus);
    		if($voteStatus){
    			$data['vote_status'] = $voteStatus['dict_value'];
    			$result = M('em_vote')->data($data)->where("vote_id=$vid")->save();
    			var_dump(M('em_vote')->getLastSql());
    			if(!$result){
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
//     	$this->success('操作成功！','/em/vote/index');
    	$this->redirect("view",array('vote_id'=>$vid));
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
    
    /**
     * 切换房屋
     * @param string $houseId 房屋ID
     */
    public function changeHouse($houseId= FALSE)
    {
    	$prefix = C('DB_PREFIX');
    	$thisHousehold = M('em_household')->field("{$prefix}em_household.household_id,{$prefix}em_household.household_name")
	    	->join("left join {$prefix}em_house_household hh ON {$prefix}em_household.household_id = hh.household_id")
	    	->where("hh.house_id = $houseId")->select();
    	$this->ajaxReturn($thisHousehold);
    }
    
    /**
     * 切换住户
     * @param string $householdId
     */
    public function changeHousehold($householdId= FALSE)
    {
    	$prefix = C('DB_PREFIX');
    	$thisHousehold = M('em_household')->field("{$prefix}em_household.household_id,{$prefix}em_household.TEL")
    	->where("{$prefix}em_household.household_id = $householdId")->select();
    	$this->ajaxReturn($thisHousehold);
    }
}
