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

class UnitController extends ComController
{
    public function index(){
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
    	
    	//如果不是超级管理员，需要添加当前登录用户数据权限
    	if($uid != 1){
    		$currentVillage = $this->getCurrentVillage($uid);
    		if(!$currentVillage){
    			$this->error('您不属于任何小区，没有单元管理权限！');
    		}
    	}
        
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $field = isset($_GET['field']) ? $_GET['field'] : '';
        $keyword = isset($_GET['keyword']) ? htmlentities($_GET['keyword']) : '';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $villageSearch = isset($_GET['villageSearch']) ? $_GET['villageSearch'] : '';
        $buildingSearch = isset($_GET['buildingSearch']) ? $_GET['buildingSearch'] : '';
        $where = '';

        $prefix = C('DB_PREFIX');
        if ($order == 'asc') {
            $order = "{$prefix}em_unit.MODIFY_TIME asc";
        } elseif (($order == 'desc')) {
        	$order = "{$prefix}em_unit.MODIFY_TIME desc";
        } else {
        	$order = "{$prefix}em_unit.VILLAGE asc,{$prefix}em_unit.BUILDING asc,{$prefix}em_unit.unit_id asc";
        }
        if ($keyword <> '') {
            if ($field == 'unit_name') {
            	$where[$prefix.'em_unit.unit_name'] = array('like','%'.$keyword.'%');
//             	$where = "{$prefix}em_unit.unit_name LIKE '%$keyword%'";
            }
        }
        
        if($villageSearch <> 0 && $villageSearch <> ''){
        	$where[$prefix.'em_unit.village'] = array('eq',$villageSearch);
//         	$where = "and h.village = $villageSearch";
        	$searchBuildingMap[$prefix.'em_building.village'] = array('eq',$villageSearch);
        	$buildings = M('em_building')->field("{$prefix}em_building.building_id,{$prefix}em_building.building_name")->where($searchBuildingMap)->select();
        }
        
        if($buildingSearch <> 0 && $buildingSearch <> ''){
        	$where[$prefix.'em_unit.building'] = array('eq',$buildingSearch);
        }
        

        $emUnit = M('em_unit');
        $pagesize = 10;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        
        if($currentVillage){
        	$where['v.village_id'] = array('eq',$currentVillage);
        	$searchMap[$prefix.'em_village.village_id'] = array('eq',$currentVillage);
        }
        $villages = M('em_village')->field("{$prefix}em_village.village_id,{$prefix}em_village.village_name")->where($searchMap)->select();
        
        
        $count = $emUnit->field("{$prefix}em_unit.*")
            ->order($order)
            ->where($where)
            ->join("left join {$prefix}em_village v ON {$prefix}em_unit.village = v.village_id")
            ->join("left join {$prefix}member b ON {$prefix}em_unit.operator = b.uid")
            ->join("left join {$prefix}em_building bd ON {$prefix}em_unit.building = bd.building_id")
        	->count();

        $list = $emUnit->field("{$prefix}em_unit.*,v.village_name,b.user as user,bd.building_name")
            ->order($order)
            ->where($where)
            ->join("left join {$prefix}em_village v ON {$prefix}em_unit.village = v.village_id")
            ->join("left join {$prefix}member b ON {$prefix}em_unit.operator = b.uid")
            ->join("left join {$prefix}em_building bd ON {$prefix}em_unit.building = bd.building_id")
        	->limit($offset . ',' . $pagesize)
            ->select();
        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('listVillage', $villages);
        $this->assign('listBuilding', $buildings);
        $this->display();
    }

    public function del()
    {
    	$unitIds = isset($_REQUEST['unitIds']) ? $_REQUEST['unitIds'] : false;
    	if(!$unitIds){
    		$this->error('需要删除的楼宇为空，请选择楼宇！');
    	}
    	
    	if (is_array($unitIds)) {
    		foreach ($unitIds as $k => $v) {
    			$unitIds[$k] = intval($v);
            }
            if (!$unitIds) {
                $this->error('参数错误！');
                $unitIds = implode(',', $unitIds);
            }
        }
        $map['UNIT_ID'] = array('in', $unitIds);
        $prefix = C('DB_PREFIX');
        //如果单元下包含房屋信息，不能删除
        if (is_array($unitIds)) {
	        for ($i=0;$i<count($unitIds);$i++){
	        	$unitId = $unitIds[$i];
	        	$emHouse = M('em_house')->where("unit = $unitId")->select();
	        	if(!empty($emHouse)){
	        		$this->error('单元：' . $unitId. '下包含房屋信息，不能删除!');
	        	}
	        	
	        	//楼宇单元数减一
	        	$emBuilding = M('em_building')->field("unit_number,building_id")
	        	->join("left join {$prefix}em_unit u ON {$prefix}em_building.building_id = u.building")
	        	->where("u.unit_id = $unitId")
	        	->find();
	        	$building_id = $emBuilding['building_id'];
	        	
	        	M('em_building')->where("building_id=$building_id")->setDec('UNIT_NUMBER');
	        }
        }else{
        	$emHouse = M('em_house')->where("unit = $unitIds")->select();
        	if(!empty($emHouse)){
        		$this->error('单元：' . $unitIds. '下包含房屋信息，不能删除!');
        	}
        	//楼宇单元数减一
        	$emBuilding = M('em_building')->field("unit_number,building_id")
        	->join("left join {$prefix}em_unit u ON {$prefix}em_building.building_id = u.building")
        	->where("u.unit_id = $unitIds")
        	->find();
        	$building_id = $emBuilding['building_id'];
        	
        	M('em_building')->where("building_id=$building_id")->setDec('UNIT_NUMBER');
        }
        
        if (M('em_unit')->where($map)->delete()) {
        	addlog('删除单元ID：' . $unitIds);
            $this->success('恭喜，单元删除成功！');
        } else {
            $this->error('参数错误！');
        }
    }

    public function edit()
    {
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
    	
    	//如果不是超级管理员，需要添加当前登录用户数据权限
    	if($uid != 1){
    		$currentVillage = $this->getCurrentVillage($uid);
    		if(!$currentVillage){
    			$this->error('您不属于任何小区，没有单元管理权限！');
    		}
    	}

        $vid = isset($_GET['unit_id']) ? intval($_GET['unit_id']) : false;
        if ($vid) {
            $prefix = C('DB_PREFIX');
            $em_unit= M('em_unit')->field("{$prefix}em_unit.*")->where("{$prefix}em_unit.unit_id=$vid")->find();
            
            if($currentVillage){
            	$map[$prefix.'em_village.village_id'] = array('eq',$currentVillage);
            }
            $em_village = M('em_village')->field("village_id,village_name")->where($map)->select();
            $village = $em_unit['village'];
            
            $em_building = M('em_building')->field("building_id,building_name")->where("village = $village")->select();
        } else {
            $this->error('参数错误！');
        }
        $this->assign('villages', $em_village);
        $this->assign('buildings', $em_building);
        $this->assign('em_unit', $em_unit);
        $this->display('form');
    }

    public function update($ajax = '')
    {
    	$unitId = isset($_POST['unit_id']) ? intval($_POST['unit_id']) : false;
    	$data['UNIT_NAME'] = isset($_POST['UNIT_NAME']) ? trim($_POST['UNIT_NAME']) : '';
    	$village = isset($_POST['village']) ? trim($_POST['village']) : false;
    	if(!$village){
    		$this->error('所属小区不能为空！');
    	}else{
    		$data['VILLAGE']= $village;
    	}
    	
    	$building = isset($_POST['building']) ? trim($_POST['building']) : false;
    	if(!$building){
    		$this->error('所属楼宇不能为空！');
    	}else{
    		$data['BUILDING']= $building;
    	}
        
        $data['LOWEST'] = isset($_POST['LOWEST']) ? trim($_POST['LOWEST']) : '';
        $data['HIGHEST'] = isset($_POST['HIGHEST']) ? trim($_POST['HIGHEST']) : '';
        $data['ENTRY_AND_EXIT_NUMBER'] = isset($_POST['ENTRY_AND_EXIT_NUMBER']) ? trim($_POST['ENTRY_AND_EXIT_NUMBER']) : '';
		
        $data['OPERATOR'] = session('uid');
        
        $timenow=date('Y-m-d H:i:s',time());
        
        $prefix = C('DB_PREFIX');
        if (!$unitId) {
        	$data['CREATE_TIME'] = $timenow;
        	$data['MODIFY_TIME'] = $timenow;
            $unitId = M('em_unit')->data($data)->add();
            
            //楼宇单元数加一
            M('em_building')->where("building_id=$building")->setInc('UNIT_NUMBER');
            
            addlog('新增单元，单元ID：' . $unitId. ',小区ID：' . $village);
        } else {
        	$data['MODIFY_TIME'] = $timenow;
        	M('em_unit')->data($data)->where("unit_id=$unitId")->save();
        	addlog('编辑单元信息，单元ID：' . $unitId);
        }
        $this->success('操作成功！','index');
    }


    public function add()
    {
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
    	
    	//如果不是超级管理员，需要添加当前登录用户数据权限
    	if($uid != 1){
    		$currentVillage = $this->getCurrentVillage($uid);
    		if(!$currentVillage){
    			$this->error('您不属于任何小区，没有单元管理权限！');
    		}
    	}
    	
    	$prefix = C('DB_PREFIX');
    	
    	if($currentVillage){
    		$map[$prefix.'em_village.village_id'] = array('eq',$currentVillage);
    	}
    	
    	$emVillage = M('em_village');
    	$emVillages = $emVillage->field("village_id,village_name")->where($map)->select();
    	
    	$this->assign('villages', $emVillages);
        $this->display('form');
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
    	//     	trace($thisOrg);
    	$this->ajaxReturn($thisBuilding);
    }
}
