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

class VoteController extends ComController
{
    public function index()
    {
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
        
    	//如果不是超级管理员，需要添加当前登录用户数据权限
    	if($uid != 1){
    		$currentVillage = $this->getCurrentVillage($uid);
    		if(!$currentVillage){
    			$this->error('您不属于任何小区，没有投票权限！');
    		}
    	}
    	
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $field = isset($_GET['field']) ? $_GET['field'] : '';
        $keyword = isset($_GET['keyword']) ? htmlentities($_GET['keyword']) : '';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

        $prefix = C('DB_PREFIX');
        if ($order == 'asc') {
            $order = "{$prefix}em_vote.create_time asc";
        } elseif (($order == 'desc')) {
        	$order = "{$prefix}em_vote.create_time desc";
        } else {
        	$order = "{$prefix}em_vote.vote_id asc";
        }
        if ($keyword <> '') {
            if ($field == 'vote_name') {
            	$where[$prefix.'em_vote.vote_name'] = array('like','%'.$keyword.'%');
            }
            if ($field == 'village_name') {
            	$where['vg.village_name'] = array('like','%'.$keyword.'%');
            }
        }


        $emVote = M('em_vote');
        $pagesize = 10;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        
        if($currentVillage){
        	$where[$prefix.'em_vote.village_id'] = array('eq',$currentVillage);
        }
        
        $count = $emVote->field("{$prefix}em_vote.*")
            ->order($order)
            ->where($where)
            ->join("left join {$prefix}em_vote_village vv ON {$prefix}em_vote.vote_id = vv.vote_id")
            ->join("left join {$prefix}em_village vg ON vv.village_id = vg.village_id")
        	->count();

        $list = $emVote->field("{$prefix}em_vote.*")
            ->order($order)
            ->where($where)
            ->join("left join {$prefix}em_vote_village vv ON {$prefix}em_vote.vote_id = vv.vote_id")
            ->join("left join {$prefix}em_village vg ON vv.village_id = vg.village_id")
       		->limit($offset . ',' . $pagesize)
            ->select();
        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }

    public function del()
    {
    	$voteIds = isset($_REQUEST['voteIds']) ? $_REQUEST['voteIds'] : false;
    	if (is_array($voteIds)) {
    		foreach ($voteIds as $k => $v) {
    			$voteIds[$k] = intval($v);
            }
            if (!$voteIds) {
                $this->error('参数错误！');
                $voteIds = implode(',', $voteIds);
            }
        }
        $map['vote_id'] = array('in', $voteIds);
        /* if (is_array($voteIds)) {
        	for ($i=0;$i<count($voteIds);$i++){
        		$voteId = $voteIds[$i];
        		$emVotePaper = M('em_vote_paper')->where("vote_id = $voteId")->select();
        		if(!empty($emVotePaper)){
        			$this->error('投票：' . $voteId. '下包含问卷信息，不能删除!');
        		}
        	}
        }else{
        	$emVotePaper = M('em_vote_paper')->where("vote_id = $voteIds")->select();
        	if(!empty($emVotePaper)){
        		$this->error('投票：' . $voteIds. '下包含问卷信息，不能删除!');
        	}
        } */
        
        
        if (M('em_vote')->where($map)->delete()) {
        	addlog('删除投票，ID：' . $voteIds);
            $this->success('恭喜，投票删除成功！');
        } else {
            $this->error('参数错误！');
        }
    }

    public function edit()
    {

        $vid = isset($_GET['vote_id']) ? intval($_GET['vote_id']) : false;
        if ($vid) {
            //$member = M('member')->where("uid='$uid'")->find();
            $prefix = C('DB_PREFIX');
            $emVote = M('em_vote');
            $em_vote = $emVote->field("{$prefix}em_vote.*")->where("{$prefix}em_vote.vote_id=$vid")->find();
            $authResultDicts = M('em_dictionary')->where("dict_name = 'houseAuthResult'")->select();
            $hoseholdStatusDicts = M('em_dictionary')->where("dict_name = 'householdStatus'")->select();
        } else {
            $this->error('参数错误！');
        }
        $this->assign('authResultDicts', $authResultDicts);
        $this->assign('hoseholdStatusDicts', $hoseholdStatusDicts);
        $this->assign('em_vote', $em_vote);
        $this->display('form');
    }

    public function update($ajax = '')
    {
        $voteId = isset($_POST['vote_id']) ? intval($_POST['vote_id']) : false;
    	$data['vote_name'] = isset($_POST['vote_name']) ? trim($_POST['vote_name']) : '';
    	if ($data['vote_name']== '') {
    		$this->error('投票名称不能为空！');
    	}
    	
    	$data['description'] = isset($_POST['description']) ? trim($_POST['description']) : '';
    	if ($data['description']== '') {
    		$this->error('投票描述不能为空！');
    	}
    	
        $votePic = I('post.vote_pic', '', 'strip_tags');
        $data['vote_pic'] = $votePic? $votePic: '';
        $data['start_time'] = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
        if ($data['start_time']== '') {
        	$this->error('投票开始时间不能为空！');
        }
        
        $data['end_time'] = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';
        if ($data['end_time']== '') {
        	$this->error('投票截止	时间不能为空！');
        }
        
        $openVote = isset($_POST['open_vote_result']) ? trim($_POST['open_vote_result']) : '';
        if($openVote == 'on'){
        	$openVoteOn = M('em_dictionary')->field("dict_key,dict_value")
        	->where("dict_name = 'isOpenVote' and dict_key='公开'")
        	->find();
        	$data['open_vote_result'] = $openVoteOn['dict_value'];
        }else{
        	$openVoteOff = M('em_dictionary')->field("dict_key,dict_value")
        	->where("dict_name = 'isOpenVote' and dict_key='不公开'")
        	->find();
        	$data['open_vote_result'] = $openVoteOff['dict_value'];
        }
        
        $data['confirm_note'] = isset($_POST['confirm_note']) ? trim($_POST['confirm_note']) : '';
        //住户状态
        $authResults = isset($_REQUEST['authResults']) ? $_REQUEST['authResults'] : false;
        if (is_array($authResults)) {
        	$authResults = implode(',', $authResults);
        }
        $data['household_auth_result'] = $authResults;
        //住户身份
        $householdStatus = isset($_REQUEST['householdStatus']) ? $_REQUEST['householdStatus'] : false;
        if (is_array($householdStatus)) {
        	$householdStatus= implode(',', $householdStatus);
        }
        $data['household_status'] = $householdStatus;
		
        $data['vote_status'] = 1;
        
        $data['operator'] = session('uid');
        
        $timenow=date('Y-m-d H:i:s',time());
        if (!$voteId) {
        	$data['create_time'] = $timenow;
        	$data['modify_time'] = $timenow;
        	$voteId = M('em_vote')->data($data)->add();
        	addlog('新增投票，投票ID：' . $voteId);
        } else {
        	$data['modify_time'] = $timenow;
        	M('em_vote')->data($data)->where("vote_id=$voteId")->save();
        	addlog('编辑投票信息，投票ID：' . $voteId);

        }
//         $this->success('操作成功！','index');
        $this->redirect("VotePaper/add",array());
    }


    public function add()
    {
    	$prefix = C('DB_PREFIX');
    	$authResultDicts = M('em_dictionary')->where("dict_name = 'houseAuthResult'")->select();
    	$hoseholdStatusDicts = M('em_dictionary')->where("dict_name = 'householdStatus'")->select();
    	$this->assign('authResultDicts', $authResultDicts);
    	$this->assign('hoseholdStatusDicts', $hoseholdStatusDicts);
        $this->display('form');
    }
    
    public function result()
    {
    	$vid = isset($_GET['vote_id']) ? intval($_GET['vote_id']) : false;
    	
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
    	
    	$prefix = C('DB_PREFIX');
    	$em_vote = M('em_vote')->field("{$prefix}em_vote.*")->where("{$prefix}em_vote.vote_id=$vid")->find();
    	$em_vote_paper = M('em_vote_paper')->field("{$prefix}em_vote_paper.*")->where("{$prefix}em_vote_paper.vote_id=$vid")->select();
    	foreach ($em_vote_paper as $k=>$v){
    		$votePaperId = $em_vote_paper[$k]['vote_paper_id'];
    		
    		$allCount = M('em_vote_result')->field("{$prefix}em_vote_result.*")
    		->where("{$prefix}em_vote_result.vote_paper_id = $votePaperId")->count();
    		$em_vote_paper[$k]['allCount'] = $allCount;
    		
    		$em_vote_paper_option = M('em_vote_paper_option')
    		->field("{$prefix}em_vote_paper_option.vote_paper_option_id,{$prefix}em_vote_paper_option.option_titile,{$prefix}em_vote_paper_option.option_desc,
    			{$prefix}em_vote_paper_option.option_pic,sum(case when r.vote_result_id <> '' then 1 else 0 end) as count")
    		->join("left join {$prefix}em_vote_result r on (r.option_value = {$prefix}em_vote_paper_option.vote_paper_option_id 
				or (r.option_value LIKE CONCAT({$prefix}em_vote_paper_option.vote_paper_option_id,',%') or r.option_value LIKE CONCAT('%,',{$prefix}em_vote_paper_option.vote_paper_option_id)))")
			->where("{$prefix}em_vote_paper_option.vote_paper_id=$votePaperId")
    		->group("{$prefix}em_vote_paper_option.vote_paper_id,{$prefix}em_vote_paper_option.vote_paper_option_id,{$prefix}em_vote_paper_option.option_titile,
    			{$prefix}em_vote_paper_option.option_desc,{$prefix}em_vote_paper_option.option_pic")
			->select();
    		
    		foreach($em_vote_paper_option as $k1=>$v1){
    			$optionCount = $v1['count'];
    			$v1['percent'] = round($optionCount/$allCount*100,2)."%";
    			$em_vote_paper_option[$k1] = $v1;
    		}
    		
    		$em_vote_paper[$k]['option'] = $em_vote_paper_option;
    	}
    	
    	$this->assign('emvote', $em_vote);
    	$this->assign('votePapers', $em_vote_paper);
    	$this->display('result');
    }
    
    public function resultDetail()
    {
    	$prefix = C('DB_PREFIX');
    	$questionType = isset($_GET['question_type']) ? intval($_GET['question_type']) : false;
    	$paperId = isset($_GET['paper_id']) ? intval($_GET['paper_id']) : false;
    	$optionId = isset($_GET['option_id']) ? intval($_GET['option_id']) : false;
    	
    	$field = isset($_GET['field']) ? $_GET['field'] : '';
    	$keyword = isset($_GET['keyword']) ? htmlentities($_GET['keyword']) : '';
    	$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
    	$where = "";
    	
    	if ($order == 'asc') {
    		$order = "{$prefix}em_vote_result.create_time asc";
    	} elseif (($order == 'desc')) {
    		$order = "{$prefix}em_vote_result.MODIFY_TIME desc";
    	} else {
    		$order = "{$prefix}em_vote_result.vote_result_id desc";
    	}
    	
    	if ($keyword <> '') {
    		if ($field == 'village_name') {
    			$where[$prefix.'em_vote_result.village'] = array('eq',$keyword);
    		}
    		if ($field == 'tel') {
    			$where[$prefix.'em_vote_result.tel'] = array('like',$keyword.'%');
    		}
    	}
    	
    	
    	if($questionType == 3){
    		$field = "{$prefix}em_vote_result.*,p.question_titile";
    		$where['p.vote_paper_id'] = array('eq',$paperId);
    		$join = "left join {$prefix}em_vote_paper p on p.vote_paper_id = {$prefix}em_vote_result.vote_paper_id";
    	}else{
    		$field = "{$prefix}em_vote_result.*,p.option_titile";
    		$where['p.vote_paper_option_id'] = array('eq',$optionId);
    		$join = "left join {$prefix}em_vote_paper_option p on ({$prefix}em_vote_result.option_value = p.vote_paper_option_id 
				or ({$prefix}em_vote_result.option_value LIKE CONCAT(p.vote_paper_option_id,',%') 
				or {$prefix}em_vote_result.option_value LIKE CONCAT('%,',p.vote_paper_option_id)))";
    	}
    	
    	$count = M('em_vote_result')->field($field)
    	->join($join)
    	->where($where)
    	->count();
    	$emVoteResult = M('em_vote_result')->field($field)
    	->join($join)
    	->where($where)
    	->order($order)
    	->select();
    	
    	
    	$p = isset($_GET['p']) ? intval($_GET['p']) : '1';
    	$pagesize = 10;#每页数量
    	$offset = $pagesize * ($p - 1);//计算记录偏移量
    	
    	$page = new \Think\Page($count, $pagesize);
    	$page = $page->show();
    	$this->assign('page', $page);
    	$this->assign('list',$emVoteResult);
    	$this->assign('questionType',$questionType);
    	$this->display('resultDetail');
    }
}
