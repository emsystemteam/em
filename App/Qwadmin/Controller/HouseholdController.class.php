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

class HouseholdController extends ComController
{
    public function index()
    {
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
    	
    	//如果不是超级管理员，需要添加当前登录用户数据权限
    	if($uid != 1){
	    	$village = $this->getCurrentVillage($uid);
	    	if(!$village){
	    		$this->error('您不属于任何小区，没有住户管理权限！');
	    	}
    	}
    	
        
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $field = isset($_GET['field']) ? $_GET['field'] : '';
        $keyword = isset($_GET['keyword']) ? htmlentities($_GET['keyword']) : '';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $villageSearch = isset($_GET['villageSearch']) ? $_GET['villageSearch'] : '';
        $buildingSearch = isset($_GET['buildingSearch']) ? $_GET['buildingSearch'] : '';
        
        $where = '';
        $tabname = isset($_GET['tabname']) ? $_GET['tabname'] : 'movedIn';

        $prefix = C('DB_PREFIX');
        if ($order == 'asc') {
            $order = "{$prefix}em_household.MODIFY_TIME asc";
        } elseif (($order == 'desc')) {
        	$order = "{$prefix}em_household.MODIFY_TIME desc";
        } else {
        	$order = "{$prefix}em_household.HOUSEHOLD_ID asc";
        }

        if($keyword <> ''){
        	if ($field == 'household_name') {
        		$where = $where . " and {$prefix}em_household.household_name LIKE '%$keyword%'";
        	}
        	if ($field == 'nickname') {
        		$where = $where . " and {$prefix}em_household.nickname LIKE '%$keyword%'";
        	}
        }
        if($villageSearch <> 0 && $villageSearch <> ''){
        	$where = $where . " and h.village = $villageSearch";
        	$searchBuildingMap[$prefix.'em_building.village'] = array('eq',$villageSearch);
	        $buildings = M('em_building')->field("{$prefix}em_building.building_id,{$prefix}em_building.building_name")->where($searchBuildingMap)->select();
        }
        
        if($buildingSearch <> 0 && $buildingSearch <> ''){
        	$where = $where . " and h.building = $buildingSearch";
        }
        
        if($village){
        	$where = $where . " and h.village = $village";
        	$searchMap[$prefix.'em_village.village_id'] = array('eq',$village);
        }
        $villages = M('em_village')->field("{$prefix}em_village.village_id,{$prefix}em_village.village_name")->where($searchMap)->select();
        
        $whereMovedIn = "{$prefix}em_household.auth_result = 1 ";
        $whereTapeAudit =  "{$prefix}em_household.auth_result = 2 ";
        $whereNotPass = "{$prefix}em_household.auth_result = 3 ";
        $whereMovedOut = "{$prefix}em_household.auth_result = 4 ";
        
        
        $pagesize = 10;#每页数量
        
        if($tabname == 'movedIn' || $tabname == ''){
        	$whereMovedIn = $whereMovedIn . $where;
        }elseif ($tabname == 'tapeAudit'){
        	$whereTapeAudit =  $whereTapeAudit . $where;
        }elseif ($tabname == 'notPass'){
        	$whereNotPass = $whereNotPass . $where;
        }elseif ($tabname == 'movedOut'){
        	$whereMovedOut = $whereMovedOut . $where;
        }
        
        //已迁入
        $pageMovedIn = $this->getPage($whereMovedIn);
        $listMovedIn = $this->getList($whereMovedIn,$p);
        $this->assign('listMovedIn', $listMovedIn);
        $this->assign('pageMovedIn', $pageMovedIn);
  		
        //待审核
        $auditCount = $this->getCount($whereTapeAudit);
        $auditPage = new \Think\Page($count, $pagesize);
        $auditPage = $auditPage->show();
//         $pageTapeAudit = $this->getPage($whereTapeAudit);
        $listTapeAudit = $this->getList($whereTapeAudit,$p);
        $this->assign('listTapeAudit', $listTapeAudit);
        $this->assign('pageTapeAudit', $auditPage);
        $this->assign('tapeAuditCount',$auditCount);
        
        //审核不通过
        $pageNotPass = $this->getPage($whereNotPass);
        $listNotPass = $this->getList($whereNotPass,$p);
        $this->assign('listNotPass', $listNotPass);
        $this->assign('pageNotPass', $pageNotPass);
        
        //已迁出
        $pageMovedOut = $this->getPage($whereMovedOut);
        $listMovedOut = $this->getList($whereMovedOut,$p);
        $this->assign('listMovedOut', $listMovedOut);
        $this->assign('pageMovedOut', $pageMovedOut);
        
        $tab = $tabname;
        $this->assign('tab', $tab);
        $this->assign('listVillage', $villages);
        $this->assign('listBuilding', $buildings);
        $this->display();
    }
    
    private function getCount($where=''){
    	$prefix = C('DB_PREFIX');
    	$count = M('em_household')->field("{$prefix}em_household.*")
    	->join("left join {$prefix}em_house_household hh ON {$prefix}em_household.household_id = hh.household_id")
    	->join("left join {$prefix}em_house h ON hh.house_id = h.house_id")
    	->join("left join {$prefix}em_building b ON h.building = b.building_id")
    	->join("left join {$prefix}em_unit u ON h.unit = u.unit_id")
    	->join("left join {$prefix}em_village v ON h.village = v.village_id")
    	->join("left join {$prefix}member m ON {$prefix}em_household.tel = m.phone")
    	->join("left join {$prefix}em_dictionary d1 ON hh.household_status = d1.dict_value and d1.dict_name = 'householdStatus'")
    	->order($order)
    	->where($where)
    	->count();
    	
    	return $count;
    }
    
    private function getPage($where=''){
    	$prefix = C('DB_PREFIX');
    	$count = M('em_household')->field("{$prefix}em_household.household_id")
    	->join("left join {$prefix}em_house_household hh ON {$prefix}em_household.household_id = hh.household_id")
    	->join("left join {$prefix}em_house h ON hh.house_id = h.house_id")
    	->join("left join {$prefix}em_building b ON h.building = b.building_id")
    	->join("left join {$prefix}em_unit u ON h.unit = u.unit_id")
    	->join("left join {$prefix}em_village v ON h.village = v.village_id")
    	->join("left join {$prefix}member m ON {$prefix}em_household.tel = m.phone")
    	->join("left join {$prefix}em_dictionary d1 ON hh.household_status = d1.dict_value and d1.dict_name = 'householdStatus'")
    	->order($order)
    	->where($where)
    	->count();

    	$pagesize = 10;#每页数量
    	
    	$page = new \Think\Page($count, $pagesize);
    	$page = $page->show();
    	return $page;
    }
    
    private function getList($where='',$p = 1){
    	$pagesize = 10;#每页数量
    	$offset = $pagesize * ($p - 1);//计算记录偏移量
    	
    	$prefix = C('DB_PREFIX');
//   var_dump($where);	
    	$list = M('em_household')->field("{$prefix}em_household.*,v.village_name,h.house_id,h.house_name,m.user as user,m.openid,
    	b.building_name,u.unit_name,d1.dict_key as household_status")
    	->join("left join {$prefix}em_house_household hh ON {$prefix}em_household.household_id = hh.household_id")
    	->join("left join {$prefix}em_house h ON hh.house_id = h.house_id")
    	->join("left join {$prefix}em_building b ON h.building = b.building_id")
    	->join("left join {$prefix}em_unit u ON h.unit = u.unit_id")
    	->join("left join {$prefix}em_village v ON h.village = v.village_id")
    	->join("left join {$prefix}member m ON {$prefix}em_household.tel = m.phone")
    	->join("left join {$prefix}em_dictionary d1 ON hh.household_status = d1.dict_value and d1.dict_name = 'householdStatus'")
    	->order($order)
    	->where($where)
    	->limit($offset . ',' . $pagesize)
    	->select();
    	return $list;
    }
    
    /**
     * 删除住户，支持批量删除
     */
    public function del()
    {
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
    	
    	//如果不是超级管理员，需要添加当前登录用户数据权限
    	if($uid != 1){
    		$village = $this->getCurrentVillage($uid);
    		if(!$village){
    			$this->error('您不属于任何小区，没有住户管理权限！');
    		}
    	}
    	
    	$householdIds = isset($_REQUEST['householdIds']) ? $_REQUEST['householdIds'] : false;
//     	$houseIds = isset($_REQUEST['houseIds']) ? $_REQUEST['houseIds'] : false;
    	if(!$householdIds){
    		$this->error('需要删除的住户为空，请选择住户！');
    	}
    	/* if(!$houseIds){
    		$this->error('需要删除的房屋为空，请选择！');
    	} */
    	
    	if (is_array($householdIds)) {
    		foreach ($householdIds as $k => $v) {
    			$householdIds[$k] = intval($v);
            }
            if (!$householdIds) {
                $this->error('参数错误！');
                $householdIds = implode(',', $householdIds);
            }
        }
        
        $map['HOUSEHOLD_ID'] = array('in', $householdIds);
        if (M('em_household')->where($map)->delete()) {
        	addlog('删除住户ID：' . $householdIds);
            $this->success('恭喜，住户删除成功！');
            /* if(is_array($householdIds)){
	            $count = count($householdIds);
	            for($i=0;$i<$count;$i++){
	            	$map2['HOUSEHOLD_ID'] = array('eq',$householdIds[i]);
	            	$map2['HOUSE_ID'] = array('eq',$houseIds[i]);
	            	M('em_house_household')->where($map2)->delete();
	            }
            }else{
            	$map2['HOUSEHOLD_ID'] = array('eq',$householdIds);
            	$map2['HOUSE_ID'] = array('eq',$houseIds);
            	M('em_house_household')->where($map2)->delete();
            } */
        } else {
            $this->error('参数错误！');
        }
    }

    /**
     * 进入编辑野蛮
     */
    public function edit(){
        $vid = isset($_GET['household_id']) ? intval($_GET['household_id']) : false;
        if ($vid) {
        	$uid =  session('uid');
        	if($uid == null){
        		$this->error('您还未登录！');
        	}
        	
        	//如果不是超级管理员，需要添加当前登录用户数据权限
        	if($uid != 1){
        		$village = $this->getCurrentVillage($uid);
        		if(!$village){
        			$this->error('您不属于任何小区，没有住户管理权限！');
        		}
        	}
        	
        	
            $prefix = C('DB_PREFIX');
            $map[$prefix.'em_household.household_id'] = array('eq', $vid);
            if($village){
	            $map['v.village_id'] = array('eq', $village);
            }
            $em_household = M('em_household')
            	->field("{$prefix}em_household.*,v.village_name,v.village_id")
            	->join("left join {$prefix}em_house_household hh ON {$prefix}em_household.household_id = hh.household_id")
            	->join("left join {$prefix}em_house h ON hh.house_id = h.house_id")
            	->join("left join {$prefix}em_village v ON v.village_id = h.village")
            	->where($map)
            	->find();
            
            /* $em_village = M('em_village')->field("village_id,village_name")->select();
            
            $village = $em_household['village'];
            if($village){
	            $em_building = M('em_building')->field("building_id,building_name")->where("village = $village")->select();
            }
            
            $building = $em_household['building'];
            if($building){
	            $em_unit = M('em_unit')->field("unit_id,unit_name")->where("building = $building")->select();
            }

            $unit = $em_household['unit'];
            if($unit){
	            $em_house = M('em_house')->field("house_id,house_name")->where("unit = $unit")->select();
            } */
            
            $householdTypes = M('em_dictionary')->field("dict_key,dict_value")
            ->where("dict_name = 'householdType'")
            ->select();
            $householdStatuss = M('em_dictionary')->field("dict_key,dict_value")
            ->where("dict_name = 'householdStatus'")
            ->select();
        } else {
            $this->error('参数错误！');
        }
        $this->assign('em_household', $em_household);
       /*  $this->assign('villages', $em_village);
        $this->assign('buildings', $em_building);
        $this->assign('units', $em_unit);
        $this->assign('houses', $em_house); */
        $this->assign('householdTypes', $householdTypes);
        $this->assign('householdStatuss', $householdStatuss);
        $this->display('form');
    }

    public function update($ajax = '')
    {
    	M()->startTrans();
    	$householdId = isset($_POST['household_id']) ? intval($_POST['household_id']) : false;
    	$data['HOUSE_NAME'] = isset($_POST['HOUSE_NAME']) ? trim($_POST['HOUSE_NAME']) : '';
    	if(!$householdId){
    		$village = isset($_POST['village']) ? trim($_POST['village']) : false;
    		if(!$village){
    			$this->error('所属小区不能为空！');
    		}
    		
    		$building = isset($_POST['building']) ? trim($_POST['building']) : false;
    		if(!$building){
    			$this->error('所属楼宇不能为空！');
    		}
    		
    		$unit = isset($_POST['unit']) ? trim($_POST['unit']) : false;
    		if(!$unit){
    			$this->error('所属单元不能为空！');
    		}
    		
    		$house = isset($_POST['house']) ? trim($_POST['house']) : false;
    		if(!$house){
    			$this->error('所属房屋不能为空！');
    		}else{
    			$houseId = $house;
    		}
    	}
        
    	$data['HOUSEHOLD_NAME'] = isset($_POST['HOUSEHOLD_NAME']) ? trim($_POST['HOUSEHOLD_NAME']) : '';
    	
        $data['NICKNAME'] = isset($_POST['NICKNAME']) ? trim($_POST['NICKNAME']) : '';
        
        $tel = isset($_POST['TEL']) ? trim($_POST['TEL']) : '';
        if(!$tel){
        	$this->error('手机号不能为空！');
        }else{
        	$map['TEL'] = array('eq',$tel);
        	if($householdId){
        		$map['HOUSEHOLD_ID'] = array('neq',$householdId);
        	}
        	$em_household = M('em_household')->where($map)->find();
        	if($em_household){
        		$this->error('手机号已经存在！');
        	}
        	$data['TEL'] = $tel;
        }
        
        $data['WECHAT_ACCOUNT'] = isset($_POST['WECHAT_ACCOUNT']) ? trim($_POST['WECHAT_ACCOUNT']) : '';
        $data['WECHAT_NICKNAME'] = isset($_POST['WECHAT_NICKNAME']) ? trim($_POST['WECHAT_NICKNAME']) : '';
        $data['QQ'] = isset($_POST['QQ']) ? trim($_POST['QQ']) : '';
        $data['QQ_NICKNAME'] = isset($_POST['QQ_NICKNAME']) ? trim($_POST['QQ_NICKNAME']) : '';
        $data['ALIPAY_ACCOUNT'] = isset($_POST['ALIPAY_ACCOUNT']) ? trim($_POST['ALIPAY_ACCOUNT']) : '';
        $data['ALIPAY_NICKNAME'] = isset($_POST['ALIPAY_NICKNAME']) ? trim($_POST['ALIPAY_NICKNAME']) : '';
        $data['EMAIL'] = isset($_POST['EMAIL']) ? trim($_POST['EMAIL']) : '';
        $data['HOME_TEL'] = isset($_POST['HOME_TEL']) ? trim($_POST['HOME_TEL']) : '';
        $data['CARD_NUMBER'] = isset($_POST['CARD_NUMBER']) ? trim($_POST['CARD_NUMBER']) : '';
        $data['DOOR_CARD_NUMBER'] = isset($_POST['DOOR_CARD_NUMBER']) ? trim($_POST['DOOR_CARD_NUMBER']) : '';
        $authTime = isset($_POST['AUTH_TIME']) ? trim($_POST['AUTH_TIME']) : null;
        if(!empty($authTime)){
        	$data['AUTH_TIME'] = $authTime;
        }
        $data['MOVE_REASON'] = isset($_POST['MOVE_REASON']) ? trim($_POST['MOVE_REASON']) : '';
        
        $data['HOUSEHOLD_TYPE'] = isset($_POST['household_type']) ? trim($_POST['household_type']) : '';
        /* $data['HOUSEHOLD_STATUS'] = isset($_POST['household_status']) ? trim($_POST['household_status']) : ''; */
        
        //迁入状态
        $authResultDict = M('em_dictionary')->where("dict_name = 'authResult' and dict_key = '已迁入'")->find();
        $data['AUTH_RESULT'] = $authResultDict['dict_value'];
		
        $data['OPERATOR'] = session('uid');
        
        $timenow=date('Y-m-d H:i:s',time());
        
        $member = M('member')->where("phone = $tel")->find();
        
        if (!$householdId) {
        	$data['CREATE_TIME'] = $timenow;
        	$data['MODIFY_TIME'] = $timenow;
        	if (household_name== '') {
                $this->error('住户名称不能为空！');
            }
            
            if(!$householdId = M('em_household')->data($data)->add()){
            	M()->rollback();
            	addlog('新增住户信息异常!');
            	$this->error('新增住户信息异常!');
            }
            //住户和房屋关联表插入数据
            $householdStatus = isset($_POST['household_status']) ? trim($_POST['household_status']) : '';
            if(!$this->addHouseHousehold($houseId, $householdId, $householdStatus)){
            	M()->rollback();
            	addlog('新增住户和房屋关联信息异常!');
            	$this->error('新增住户和房屋关联信息异常!');
            }
            
            if($householdId){
            	if($member){
            		addlog('手机：' . $tel . "用户已经存在，不插入默认账户！");
            	}else{
            		if(!$this->addDefaultUser($data)){
            			M()->rollback();
            			addlog('新增默认用户异常!');
            			$this->error('新增默认用户异常!');
            		}
            	}
            }
            
            addlog('新增住户，住户ID：' . $householdId);
            
        } else {
        	$data['MODIFY_TIME'] = $timenow;
        	if(!M('em_household')->data($data)->where("household_id=$householdId")->save()){
        		M()->rollback();
        		addlog('编辑住户信息异常，住户id：' . $householdId);
        		$this->error('编辑住户信息异常!');
        	}
        	addlog('编辑住户信息，住户ID：' . $householdId);
        	
        	//如果手机账户用户已经存在
        	if($member){
        		addlog('手机：' . $tel . "用户已经存在，不插入默认账户！");
        	}else{//如果手机账户用户不存在，默认插入新用户和用户组权限
        		if(!$this->addDefaultUser($data)){
        			M()->rollback();
        			addlog('新增默认用户异常!');
        			$this->error('新增默认用户异常!');
        		}
//         		$this->addDefaultUser($data);
        	}
        	
        }
        M()->commit();
        $this->success('操作成功！','index');
    }

    private function addHouseHousehold($houseId,$householdId,$householdStatus){
    	//住户和房屋关联表插入数据
    	$houseAndHouseholdData['HOUSE_ID'] = $houseId;
    	$houseAndHouseholdData['HOUSEHOLD_ID'] = $householdId;
    	$houseAndHouseholdData['HOUSEHOLD_STATUS'] = $householdStatus;
    	return M('em_house_household')->data($houseAndHouseholdData)->add();
    }
    
    /**
     * 在插入或修改住户信息的时候，默认会插入一条用户数据，前提是该住户手机号在用户表中不存在。
     * 插入的用户默认密码是123456，用户组为普通用户（id为3），如果普通用户组ID变了，这里代码要做相应的变更
     * 如果需要修改或者删除用户，需要到用户管理中操作。
     * @param unknown $data
     */
    private function addDefaultUser($data){
    	$memberData['user'] = $data['TEL'];
    	$memberData['phone'] = $data['TEL'];
    	$memberData['qq'] = $data['QQ'];
    	$memberData['head'] = '';
    	$memberData['sex'] = 0;
    	$memberData['birthday'] = '';
    	$memberData['email'] = $data['EMAIL'];
    	$memberData['password'] = password("123456"); //默认密码为123456
    	$memberData['t'] = date('Y-m-d H:i:s',time());
    	$uid = M('member')->data($memberData)->add();
    	if($uid){
    		return M('auth_group_access')->data(array('group_id' => 3, 'uid' => $uid))->add(); //默认用户组为普通用户，id为3，数据库普通用户 id不能变
    	}else{
    		return false;
    	}
    }

    public function add()
    {
    	$prefix = C('DB_PREFIX');
    	$emVillages = M('em_village')->field("village_id,village_name")->select();
    	$householdTypes = M('em_dictionary')->field("dict_key,dict_value")
    	->where("dict_name = 'householdType'")
    	->select();
    	$householdStatuss = M('em_dictionary')->field("dict_key,dict_value")
    	->where("dict_name = 'householdStatus'")
    	->select();
    	$this->assign('villages', $emVillages);
    	$this->assign('householdTypes', $householdTypes);
    	$this->assign('householdStatuss', $householdStatuss);
        $this->display('form');
    }
    
    
    /**
     * 导出excel
     */
    public function exportHousehold()
    {
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
    	
    	//如果不是超级管理员，需要添加当前登录用户数据权限
    	if($uid != 1){
    		$currentVillage = $this->getCurrentVillage($uid);
    		if(!$currentVillage){
    			$this->error('您不属于任何小区，没有房屋管理权限！');
    		}
    	}
    	
    	$xlsName  = "住户";
    	$xlsCell  = array(
    			array('household_name','住户名称'),
    			array('village','所属小区'),
    			array('building','所属楼宇'),
    			array('unit','所属单元'),
    			array('house','所属房号'),
    			array('nickname','昵称'),
    			array('tel','手机号'),
    			array('wechat_account','微信号'),
    			array('wechat_nickname','微信昵称'),
    			array('qq','QQ号'),
    			array('qq_nickname','QQ昵称'),
    			array('alipay_account','支付宝账号'),
    			array('alipay_nickname','支付宝昵称'),
    			array('email','邮箱'),
    			array('home_tel','家庭电话'),
    			array('card_number','业主卡号'),
    			array('door_card_number','门禁卡号'),
    			array('first_login_time','注册/首次登陆时间'),
    			array('auth_time','迁入/认证时间'),
    			array('auth_result','认证状态'),
    			array('move_reason','迁入原因'),
    			array('login_times','登录次数'),
    			array('last_login_time','最后一次登录时间'),
    			array('household_type','户口类型'),
    			array('household_status','住户身份'),
    			array('create_time','创建时间'),
    			array('modify_time','修改时间'),
    			array('operator','操作人')
    	);
    	
    	if($currentVillage){
    		$where['v.village_id'] = array('eq',$currentVillage);
    	}
    	
    	$prefix = C('DB_PREFIX');
    	$emHousehold = M('em_household');
    	$list = $emHousehold->field("household_name,nickname,tel,wechat_account,wechat_nickname,{$prefix}em_household.qq,{$prefix}em_household.qq_nickname,alipay_account,alipay_nickname,
    			{$prefix}em_household.email,home_tel,card_number,door_card_number,first_login_time,auth_time,move_reason,login_times,last_login_time,
    			{$prefix}em_household.create_time,{$prefix}em_household.modify_time,d1.dict_key as household_type,d2.dict_key as household_status,d3.dict_key as auth_result,
    			b.user as operator,v.village_name as village,bd.building_name as building,u.unit_name as unit,h.house_name as house")
    	->join("left join {$prefix}em_house_household hh ON {$prefix}em_household.household_id = hh.household_id")
    	->join("left join {$prefix}em_house h ON hh.house_id = h.house_id")
    	->join("left join {$prefix}em_village v ON h.village = v.village_id")
    	->join("left join {$prefix}em_building bd ON h.building = bd.building_id")
    	->join("left join {$prefix}em_unit u ON h.unit = u.unit_id")
    	->join("left join {$prefix}em_dictionary d1 ON {$prefix}em_household.household_type = d1.dict_value and d1.dict_name = 'householdType'")
    	->join("left join {$prefix}em_dictionary d2 ON hh.household_status = d2.dict_value and d2.dict_name = 'householdStatus'")
    	->join("left join {$prefix}em_dictionary d3 ON {$prefix}em_household.auth_result = d3.dict_value and d3.dict_name = 'authResult'")
    	->join("left join {$prefix}member b ON {$prefix}em_household.operator = b.uid")
    	->where($where)
    	->select();
    	
//     	$em_village = $emVillage->field("{$prefix}em_village.*")->select();
//     	var_dump($list);
    	$this->exportExcel($xlsName,$xlsCell,$list);
    }
    
    /**
     * 导出excel导入模板
     */
    public function exportExcelTemplate(){
    	$xlsName  = "导入住户模板";
    	$xlsCell  = array(
    			array('household_name','住户名称(*)'),
    			array('village','所属小区(*)'),
    			array('building','所属楼宇(*)'),
    			array('unit','所属单元(*)'),
    			array('house','房号(*)'),
    			array('nickname','昵称'),
    			array('tel','手机号(*)'),
    			array('wechat_account','微信号'),
    			array('wechat_nickname','微信昵称'),
    			array('qq','QQ号'),
    			array('qq_nickname','QQ昵称'),
    			array('alipay_account','支付宝账号'),
    			array('alipay_nickname','支付宝昵称'),
    			array('email','邮箱'),
    			array('home_tel','家庭电话'),
    			array('card_number','业主卡号'),
    			array('door_card_number','门禁卡号'),
    			array('auth_time','迁入时间'),
    			array('household_type','户口类型'),
    			array('household_status','住户身份')
    	);
    	$this->exportExcel($xlsName,$xlsCell);
    }
    
    /**
     * 导入excel
     */
    public function importExcel()
    {
    	if(!empty($_FILES['import']['name'])){
    		$upload = new \Think\Upload();                      // 实例化上传类
    		$upload->maxSize   = 10485760 ;                 // 设置附件上传大小
    		$upload->exts      = array('xls');       // 设置附件上传类型
    		$upload->rootPath  = './Public/Excel/';             // 设置附件上传根目录
    		$upload->autoSub   = false;                   // 将自动生成以当前时间的形式的子文件夹，关闭
    		
//     		var_dump($upload);
    		
    		$info = $upload->upload();                             // 上传文件
//     		var_dump($info);
//     		$exts = $info['import']['ext'];                                 // 获取文件后缀
			
    		$filename = $upload->rootPath.$info['import']['savename'];        // 生成文件路径名
    		
    		$readExcelResult = $this->importExecl($filename);
    		if($this->batchInsert($readExcelResult['data'][0]['Content'])){
	    		$this->success('导入成功！');
    		}else{
    			$this->error("导入失败！");
    		}
    	}else{
    		$this->error("请选择上传的文件");
    	}
    }
    
    /**
     * 导入excel数据批量入库
     * @param unknown $insertData
     */
    public function batchInsert($insertData){
    	M()->startTrans();
    	$prefix = C('DB_PREFIX');
    	$emHousehold = M('em_household');
    	$emVillage = M('em_village');
    	for($i=1;$i<=count($insertData);$i++){
    		if($i == 1 || $i == 2){
    			continue;
    		}
    		$householdData = $insertData[$i];
    		
    		$householdName = $householdData[0];
    		if(empty($householdName)){
    			$this->error("住户名称不能为空！");
    		}
    		
    		$data['HOUSEHOLD_NAME'] = $householdName;
    		
    		$villageName= trim($householdData[1]);
    		$em_village = $emVillage->where("village_name = '$villageName'")->find();
    		if(empty($em_village)){
    			$this->error("小区名称：".$villageName."不存在");
    		}else{
	    		//$data['VILLAGE'] = $em_village['village_id'];
    		}
    		
    		$buildingName = trim($householdData[2]);
    		$map1['BUILDING_NAME'] = $buildingName;
    		$map1['VILLAGE'] = $em_village['village_id'];
    		$em_building = M('em_building')->where($map1)->find();
    		if(empty($em_building)){
    			$this->error("小区：" . $villageName . ",楼宇名称：" . $buildingName. "不存在");
    		}else{
    			//$data['BUILDING'] = $em_building['building_id'];
    		}
    		
    		$unitName = trim($householdData[3]);
    		$map2['BUILDING'] = $em_building['building_id'];
    		$map2['VILLAGE'] = $em_village['village_id'];
    		$map2['UNIT_NAME'] = $unitName;
    		$em_unit = M('em_unit')->where($map2)->find();
    		if(empty($em_unit)){
    			$this->error("小区：" . $villageName . ",楼宇名称：" . $buildingName . ",单元名称：" . $unitName . "不存在");
    		}else{
    			//$data['UNIT'] = $em_unit['unit_id'];
    		}
    		
    		$houseName = trim($householdData[4]);
    		$map2['BUILDING'] = $em_building['building_id'];
    		$map2['VILLAGE'] = $em_village['village_id'];
    		$map2['UNIT'] = $em_unit['unit_id'];
    		$map2['HOUSE_NAME'] = $houseName;
    		$em_house = M('em_house')->where($map2)->find();
    		if(empty($em_house)){
    			$this->error("小区：" . $villageName . ",楼宇名称：" . $buildingName . ",单元名称：" . $unitName . ",房号：" . $houseName. "不存在");
    		}else{
    			$houseId = $em_house['house_id'];
    		}
    		
    		$data['NICKNAME'] = $householdData[5];

    		$tel = $householdData[6];
    		if(!$tel){
    			$this->error('手机号不能为空！');
    		}else{
    			$map['TEL'] = array('eq',$tel);
    			$em_household = M('em_household')->where($map)->find();
    			if($em_household){
    				$this->error('手机号已经存在！');
    			}
    			$data['TEL']= $tel;
    		}
    		
    		
    		$data['WECHAT_ACCOUNT'] = $householdData[7];
    		$data['WECHAT_NICKNAME'] = $householdData[8];
    		$data['QQ'] = $householdData[9];
    		$data['QQ_NICKNAME'] = $householdData[10];
    		$data['ALIPAY_ACCOUNT'] = $householdData[11];
    		$data['ALIPAY_NICKNAME'] = $householdData[12];
    		$data['EMAIL'] = $householdData[13];
    		$data['HOME_TEL'] = $householdData[14];
    		$data['CARD_NUMBER'] = $householdData[15];
    		$data['DOOR_CARD_NUMBER'] = $householdData[16];
    		
    		$data['AUTH_TIME'] = $householdData[17];
    		//户口类型
    		$householdType = trim($householdData[18]);
    		$householdTypeDict = M('em_dictionary')->where("dict_name = 'householdType' and dict_key = '%s'",array($householdType))->find();
    		$data['HOUSEHOLD_TYPE'] = $householdTypeDict['dict_value'];
    		
    		//住户身份
    		$householdStatus = trim($householdData[19]);
    		if(empty($householdStatus)){
    			$householdStatus = '其他';
    		}
    		$householdStatusDict = M('em_dictionary')->where("dict_name = 'householdStatus' and dict_key = '%s'",array($householdStatus))->find();
    		$householdStatusId = $householdStatusDict['dict_value'];
    		
    		//迁入状态
    		$authResultDict = M('em_dictionary')->where("dict_name = 'authResult' and dict_key = '已迁入'")->find();
    		$data['AUTH_RESULT'] = $authResultDict['dict_value'];
    		
    		$data['OPERATOR'] = session('uid');
    		
    		$timenow=date('Y-m-d H:i:s',time());
    		
    		$data['CREATE_TIME'] = $timenow;
    		$data['MODIFY_TIME'] = $timenow;
    		$householdId = $emHousehold->data($data)->add();
    		
    		addlog('导入住户，住户ID：' . $householdId);
    		
    		if(!$this->addHouseHousehold($houseId, $householdId, $householdStatusId)){
    			M()->rollback();
    			return false;
    		}
    		
    		
    		$member = M('member')->where("phone = $tel")->find();
    		if($householdId){
    			if($member){
    				addlog('手机：' . $tel . "用户已经存在，不插入默认账户！");
    			}else{
    				if(!$this->addDefaultUser($data)){
    					M()->rollback();
    					return false;
    				}
    			}
    		}
    	}
    	M()->commit();
    	return true;
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
     * 迁入房屋页面
     */
    public function movedIn()
    {
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
    	
    	//如果不是超级管理员，需要添加当前登录用户数据权限
    	if($uid != 1){
	    	$currentVillage = $this->getCurrentVillage($uid);
	    	if(!$currentVillage){
	    		$this->error("您不属于任何小区，没有住户管理权限！");
	    	}
    	}
    	
    	$householdId = isset($_GET['household_id']) ? intval($_GET['household_id']) : false;;
    	if($householdId){
    		$prefix = C('DB_PREFIX');
    		$em_household = M('em_household')->field("{$prefix}em_household.*")
    		->where("{$prefix}em_household.household_id=$householdId")->find();
    		if($currentVillage){
    			$where = "{$prefix}em_village.village_id=$currentVillage";
    		}
    		$emVillages = M('em_village')->field("village_id,village_name")->where($where)->select();
    		$householdStatuss = M('em_dictionary')->field("dict_key,dict_value")
    		->where("dict_name = 'householdStatus'")
    		->select();
    		
    		$this->assign('villages', $emVillages);
    		$this->assign('em_household', $em_household);
    		$this->assign('householdStatuss',$householdStatuss);
	    	$this->display('movedIn');
    	}else{
    		$this->error("住户不存在！");
    	}
    }
    
    /**
     * 迁入房屋
     */
    public function move(){
    	M()->startTrans();
    	$householdId = isset($_POST['household_id']) ? intval($_POST['household_id']) : false;
    	if($householdId){
//     		$data['HOUSE_NAME'] = isset($_POST['HOUSE_NAME']) ? trim($_POST['HOUSE_NAME']) : '';
    		$village = isset($_POST['village']) ? trim($_POST['village']) : false;
    		if(!$village){
    			$this->error('所属小区不能为空！');
    		}
    		
    		$building = isset($_POST['building']) ? trim($_POST['building']) : false;
    		if(!$building){
    			$this->error('所属楼宇不能为空！');
    		}
    		
    		$unit = isset($_POST['unit']) ? trim($_POST['unit']) : false;
    		if(!$unit){
    			$this->error('所属单元不能为空！');
    		}
    		
    		$house = isset($_POST['house']) ? trim($_POST['house']) : false;
    		if(!$house){
    			$this->error('所属房屋不能为空！');
    		}else{
    			$houseId = $house;
    		}
    		$householdStatus = isset($_POST['household_status']) ? trim($_POST['household_status']) : '';
    		//迁入状态
    		$householdStatusDict = M('em_dictionary')->where("dict_name = 'authResult' and dict_key = '已迁入'")->find();
    		$data['AUTH_RESULT'] = $householdStatusDict['dict_value'];
    		
    		$timenow=date('Y-m-d H:i:s',time());
    		$data['MODIFY_TIME'] = $timenow;
    		$data['AUTH_TIME'] = $timenow;
    		if(!M('em_household')->data($data)->where("household_id=$householdId")->save()){
    			M()->rollback();
    			$this->error("住户迁入异常！");
    		}
    		addlog('住户迁入，住户ID：' . $householdId);
    		if($this->addHouseHousehold($houseId, $householdId,$householdStatus)){
    			M()->commit();
    		}else{
    			M()->rollback();
    		}
    	}else{
    		$this->error("住户不存在！");
    	}
    	$this->success('操作成功！','index');
    }
    
    public function batchMovedOut(){
    	M()->startTrans();
    	$householdIds = isset($_REQUEST['householdIds']) ? $_REQUEST['householdIds'] : false;
    	$houseIds = isset($_REQUEST['houseIds']) ? $_REQUEST['houseIds'] : false;
    	if(!$householdIds){
    		$this->error('需要迁出的住户为空，请选择住户！');
    	}
    	if(!$houseIds){
    		$this->error('需要迁出的房屋为空，请重新选择住户！');
    	}
    	
    	$count = count($householdIds);
    	for($i=0;$i<$count;$i++){
    		$householdId = $householdIds[$i];
    		$houseId = $houseIds[$i];
    		$map['HOUSEHOLD_ID'] = array('eq', $householdId);
    		$houseAndHousehold = M('em_house_household')->where("household_id=$householdId and house_id != $houseId")->find();
    		if(empty($houseAndHousehold)){
    			//迁入状态
    			$householdStatusDict = M('em_dictionary')->where("dict_name = 'authResult' and dict_key = '已迁出'")->find();
    			$data['AUTH_RESULT'] = $householdStatusDict['dict_value'];
    			$data['AUTH_TIME'] = null;
    		}
    		$timenow=date('Y-m-d H:i:s',time());
    		$data['MODIFY_TIME'] = $timenow;

    		if(M('em_household')->data($data)->where($map)->save()){
    			//删除住户和房屋关联关系
    			$map['HOUSEHOLD_ID'] = array('eq', $householdId);
    			$map['HOUSE_ID'] = array('eq', $houseId);
    			if(!M('em_house_household')->where($map)->delete()){
    				M()->rollback();
    				$this->error('迁出住户id:' . $householdId . ',房屋id：' . $houseId . '时删除失败！');
    			}else{
	    			addlog('住户迁出，住户ID：' . $householdId);
    			}
    		}else{
    			M()->rollback();
    			$this->error('批量迁出失败！');
    		}
    	}
    	M()->commit();
    	$this->success('操作成功！');
    }
    
    /**
     * 迁出页面
     */
    public function movedOut(){
    	M()->startTrans();
    	$householdId = isset($_REQUEST['household_id']) ? $_REQUEST['household_id'] : false;
    	$houseId = isset($_REQUEST['house_id']) ? $_REQUEST['house_id'] : false;
    	if(!$householdId){
    		$this->error('需要迁出的住户为空，请选择住户！');
    	}
    	
    	if(!$houseId){
    		$this->error('需要迁出的房屋为空，请重新选择住户！');
    	}
    	
    	$map['HOUSEHOLD_ID'] = array('eq', $householdId);
    	$houseAndHousehold = M('em_house_household')->where("household_id=$householdId and house_id != $houseId")->find();
		
    	if(empty($houseAndHousehold)){
    		//迁入状态
    		$householdStatusDict = M('em_dictionary')->where("dict_name = 'authResult' and dict_key = '已迁出'")->find();
    		$data['AUTH_RESULT'] = $householdStatusDict['dict_value'];
	    	$data['AUTH_TIME'] = null;
    	}
    	
    	$timenow=date('Y-m-d H:i:s',time());
    	$data['MODIFY_TIME'] = $timenow;
    	if(M('em_household')->data($data)->where($map)->save()){
	    	$this->success('操作成功！');
	    	//删除住户和房屋关联关系
	    	$map['HOUSEHOLD_ID'] = array('eq', $householdId);
	    	$map['HOUSE_ID'] = array('eq', $houseId);
	    	
    		if(M('em_house_household')->where($map)->delete()){
		    	M()->commit();
    		}else{
				M()->rollback();    			
    		}
	    	addlog('住户迁出，住户ID：' . $householdId);
    	}else{
    		M()->rollback();
    		$this->error("住户迁出失败！");
    	}
    }
    
    public function auditing(){
    	$this->display('auditing');
    }
    
    /**
     * 查看详情
     */
    public function details(){
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
    	
    	//如果不是超级管理员，需要添加当前登录用户数据权限
    	if($uid != 1){
    		$currentVillage = $this->getCurrentVillage($uid);
    		if(!$currentVillage){
    			$this->error("您不属于任何小区，没有住户管理权限！");
    		}
    	}
    	$vid = isset($_GET['household_id']) ? intval($_GET['household_id']) : false;
    	if ($vid) {
    		$prefix = C('DB_PREFIX');
    		//查询住户信息
    		$list = M('em_household')->field("{$prefix}em_household.household_id,household_name,nickname,tel,wechat_account,wechat_nickname,{$prefix}em_household.qq,{$prefix}em_household.qq_nickname,alipay_account,alipay_nickname,
    		{$prefix}em_household.email,home_tel,card_number,door_card_number,first_login_time,auth_time,move_reason,login_times,last_login_time,
    		{$prefix}em_household.create_time,{$prefix}em_household.modify_time,d1.dict_key as household_type,d2.dict_key as household_status,
    		b.user as operator,v.village_name,v.village_id as village,h.house_id")
    		->join("left join {$prefix}em_house_household hh ON {$prefix}em_household.household_id = hh.household_id")
    		->join("left join {$prefix}em_house h ON hh.house_id = h.house_id")
    		->join("left join {$prefix}em_village v ON h.village = v.village_id")
    		->join("left join {$prefix}em_dictionary d1 ON {$prefix}em_household.household_type = d1.dict_value and d1.dict_name = 'householdType'")
    		->join("left join {$prefix}em_dictionary d2 ON hh.household_status = d2.dict_value and d2.dict_name = 'householdStatus'")
    		->join("left join {$prefix}member b ON {$prefix}em_household.operator = b.uid")
    		->where("{$prefix}em_household.household_id=$vid")
    		->find();
    		
    		$household_id = $list['household_id'];
    		$village_id = $list['village'];
    		
    		$houseMap['hh.household_id'] = array('eq',$household_id);
    		if($currentVillage){
    			$houseMap['v.village_id'] = array('eq',$currentVillage);
    		}
    		
    		//查询当前住户所有房屋
    		$houses = M('em_house')->field("{$prefix}em_house.house_id,hh.household_id,house_name,v.village_name,d1.dict_key as house_type,d2.dict_key as household_status")
    		->join("left join {$prefix}em_house_household hh ON {$prefix}em_house.house_id = hh.house_id")
    		->join("left join {$prefix}em_village v ON {$prefix}em_house.village = v.village_id")
    		->join("left join {$prefix}em_dictionary d1 ON {$prefix}em_house.house_type = d1.dict_value and d1.dict_name = 'houseType'")
    		->join("left join {$prefix}em_dictionary d2 ON hh.household_status = d2.dict_value and d2.dict_name = 'householdStatus'")
    		->where($houseMap)
    		->select();
    		
    		if(!empty($houses)){
    			for($i=0;$i<count($houses);$i++){
    				$houseIds[$i] = $houses[$i]['house_id'];
    			}
    			$map['hh.house_id'] = array('in', $houseIds);
    			
    			//查询当前住户所属房屋下的相关住户
    			$households = M('em_household')->field("household_name,{$prefix}em_household.household_id,v.village_name,house_name,tel")
    			->join("left join {$prefix}em_house_household hh ON {$prefix}em_household.household_id = hh.household_id")
    			->join("left join {$prefix}em_house h ON hh.house_id = h.house_id")
    			->join("left join {$prefix}em_village v ON h.village = v.village_id")
    			->where($map)
    			->select();
    		}
	    	$this->assign('em_household', $list);
	    	$this->assign('households',$households);
	    	$this->assign('houses',$houses);
	    	$this->display('details');
    	} else {
    		$this->error('参数错误！');
    	}
    }
}
