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

class HouseController extends ComController
{
    public function index()
    {
        
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $field = isset($_GET['field']) ? $_GET['field'] : '';
        $keyword = isset($_GET['keyword']) ? htmlentities($_GET['keyword']) : '';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $where = '';

        $prefix = C('DB_PREFIX');
        if ($order == 'asc') {
            $order = "{$prefix}em_house.MODIFY_TIME asc";
        } elseif (($order == 'desc')) {
        	$order = "{$prefix}em_house.MODIFY_TIME desc";
        } else {
        	$order = "{$prefix}em_house.HOUSE_ID asc";
        }
        if ($keyword <> '') {
            if ($field == 'house_name') {
            	$where = "{$prefix}em_house.house_name LIKE '%$keyword%'";
            }
        }


        $emHouse = M('em_house');
        $pagesize = 10;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $count = $emHouse->field("{$prefix}em_house.*")
            ->order($order)
            ->where($where)
            ->count();

            $list = $emHouse->field("{$prefix}em_house.*,v.village_name,b.user as user,d1.dict_key as buildingType,d2.dict_key as buildingStructure,d3.dict_key as buildingOrientation")
            ->order($order)
            ->where($where)
            ->join("left join {$prefix}em_village v ON {$prefix}em_building.village = v.village_id")
            ->join("left join {$prefix}member b ON {$prefix}em_building.operator = b.uid")
            ->join("left join {$prefix}em_dictionary d1 ON {$prefix}em_building.building_type = d1.dict_value and d1.dict_name = 'buildingType'")
            ->join("left join {$prefix}em_dictionary d2 ON {$prefix}em_building.building_structure = d2.dict_value and d2.dict_name = 'buildingStructure'")
            ->join("left join {$prefix}em_dictionary d3 ON {$prefix}em_building.building_orientation = d3.dict_value and d3.dict_name = 'buildingOrientation'")
            ->limit($offset . ',' . $pagesize)
            ->select();
//         var_dump($list);
        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }

    public function del()
    {
    	$buildingIds = isset($_REQUEST['buildingIds']) ? $_REQUEST['buildingIds'] : false;
    	if(!$buildingIds){
    		$this->error('需要删除的楼宇为空，请选择楼宇！');
    	}
    	
    	if (is_array($buildingIds)) {
    		foreach ($buildingIds as $k => $v) {
    			$buildingIds[$k] = intval($v);
            }
            if (!$buildingIds) {
                $this->error('参数错误！');
                $buildingIds = implode(',', $buildingIds);
            }
        }
        $map['BUILDING_ID'] = array('in', $buildingIds);
        if (M('em_building')->where($map)->delete()) {
        	addlog('删除楼宇UID：' . $villageIds);
            $this->success('恭喜，楼宇删除成功！');
        } else {
            $this->error('参数错误！');
        }
    }

    public function edit()
    {

        $vid = isset($_GET['building_id']) ? intval($_GET['building_id']) : false;
        if ($vid) {
            $prefix = C('DB_PREFIX');
            $em_building = M('em_building')->field("{$prefix}em_building.*")->where("{$prefix}em_building.building_id=$vid")->find();
            $em_village = M('em_village')->field("village_id,village_name")->select();
            
            $buildingTypes = M('em_dictionary')->field("dict_key,dict_value")
            ->where("dict_name = 'buildingType'")
            ->select();
            $buildingOrientations = M('em_dictionary')->field("dict_key,dict_value")
            ->where("dict_name = 'buildingOrientation'")
            ->select();
            $buildingStructures = M('em_dictionary')->field("dict_key,dict_value")
            ->where("dict_name = 'buildingStructure'")
            ->select();
        } else {
            $this->error('参数错误！');
        }
        $this->assign('villages', $em_village);
        $this->assign('em_building', $em_building);
        $this->assign('buildingTypes', $buildingTypes);
        $this->assign('buildingOrientations', $buildingOrientations);
        $this->assign('buildingStructures', $buildingStructures);
        $this->display('form');
    }

    public function update($ajax = '')
    {
    	$buildingId = isset($_POST['building_id']) ? intval($_POST['building_id']) : false;
    	$data['BUILDING_NAME'] = isset($_POST['BUILDING_NAME']) ? trim($_POST['BUILDING_NAME'], ENT_QUOTES) : '';
    	$village = isset($_POST['village']) ? trim($_POST['village']) : false;
    	if(!$village){
    		$this->error('所属小区不能为空！');
    	}else{
    		$data['VILLAGE']= $village;
    	}
        
        $data['UNIT_NUMBER'] = isset($_POST['UNIT_NUMBER']) ? trim($_POST['UNIT_NUMBER']) : '';
        $data['FLOOR_NUMBER'] = isset($_POST['FLOOR_NUMBER']) ? trim($_POST['FLOOR_NUMBER']) : '';
        $data['BUILDING_TYPE'] = isset($_POST['building_type']) ? trim($_POST['building_type']) : '';
        $data['BUILDING_STRUCTURE'] = isset($_POST['building_structure']) ? trim($_POST['building_structure']) : '';
        $data['BUILDING_ORIENTATION'] = isset($_POST['building_orientation']) ? trim($_POST['building_orientation']) : '';
		
        $data['OPERATOR'] = session('uid');
        
        $timenow=date('Y-m-d H:i:s',time());
        if (!$buildingId) {
        	$data['CREATE_TIME'] = $timenow;
        	$data['MODIFY_TIME'] = $timenow;
        	if (village_name== '') {
                $this->error('小区名称不能为空！');
            }
            $uid = M('em_building')->data($data)->add();
            addlog('新增楼宇，楼宇ID：' . $buildingId);
        } else {
        	$data['MODIFY_TIME'] = $timenow;
        	M('em_building')->data($data)->where("building_id=$buildingId")->save();
        	addlog('编辑楼宇信息，楼宇ID：' . $buildingId);

        }
        $this->success('操作成功！');
    }


    public function add()
    {
    	$prefix = C('DB_PREFIX');
    	$emVillage = M('em_village');
    	$emVillages = $emVillage->field("village_id,village_name")->select();
    	$buildingTypes = M('em_dictionary')->field("dict_key,dict_value")
    	->where("dict_name = 'buildingType'")
    	->select();
    	$buildingOrientations = M('em_dictionary')->field("dict_key,dict_value")
    	->where("dict_name = 'buildingOrientation'")
    	->select();
    	$buildingStructures = M('em_dictionary')->field("dict_key,dict_value")
    	->where("dict_name = 'buildingStructure'")
    	->select();
    	$this->assign('villages', $emVillages);
    	$this->assign('buildingTypes', $buildingTypes);
    	$this->assign('buildingOrientations', $buildingOrientations);
    	$this->assign('buildingStructures', $buildingStructures);
        $this->display('form');
    }
    
    
    /**
     * 导出excel
     */
    public function exportBuilding()
    {
    	$xlsName  = "楼宇";
    	$xlsCell  = array(
    			array('building_id','楼宇编号'),
    			array('building_name','楼宇名称'),
    			array('village','所属小区'),
    			array('unit_number','单元数量'),
    			array('floor_number','楼宇层数'),
    			array('building_type','楼宇类型'),
    			array('building_structure','楼宇结构'),
    			array('building_orientation','楼宇朝向'),
    			array('create_time','创建时间'),
    			array('modify_time','修改时间'),
    			array('operator','操作人')
    	);
    	
    	$prefix = C('DB_PREFIX');
    	$emBuilding = M('em_building');
//     	$where = "{$prefix}em_village.village_id = 2";
    	$list = $emBuilding->field("building_id,building_name,unit_number,floor_number,d1.dict_key as building_type,d2.dict_key as building_structure,
    			d3.dict_key as building_orientation,{$prefix}em_building.create_time,{$prefix}em_building.modify_time,
    			b.user as operator,v.village_name as village")
    	->join("left join {$prefix}em_village v ON {$prefix}em_building.village = v.village_id")
    	->join("left join {$prefix}em_dictionary d1 ON {$prefix}em_building.building_type = d1.dict_value and d1.dict_name = 'buildingType'")
    	->join("left join {$prefix}em_dictionary d2 ON {$prefix}em_building.building_structure = d2.dict_value and d2.dict_name = 'buildingStructure'")
    	->join("left join {$prefix}em_dictionary d3 ON {$prefix}em_building.building_orientation = d3.dict_value and d3.dict_name = 'buildingOrientation'")
    	->join("left join {$prefix}member b ON {$prefix}em_building.operator = b.uid")
    	->select();
    	
//     	$em_village = $emVillage->field("{$prefix}em_village.*")->select();
//     	var_dump($list);
    	$this->exportExcel($xlsName,$xlsCell,$list);
    }
    
    /**
     * 导出excel导入模板
     */
    public function exportExcelTemplate(){
    	$xlsName  = "导入楼宇模板";
    	$xlsCell  = array(
    			array('building_name','楼宇名称'),
    			array('village','所属小区'),
    			array('unit_number','单元数量'),
    			array('floor_number','楼宇层数'),
    			array('building_type','楼宇类型'),
    			array('building_structure','楼宇结构'),
    			array('building_orientation','楼宇朝向')
    	);
    	$this->exportExcel($xlsName,$xlsCell);
    }
    
    /**
     * 导入excel
     */
    public function importExcel()
    {
//     	dump($_FILES);
    	if(!empty($_FILES)){
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
    		$this->batchInsert($readExcelResult['data'][0]['Content']);
    		$this->success('导入成功！');
    	}else{
    		$this->error("请选择上传的文件");
    	}
    }
    
    /**
     * 导入excel数据批量入库
     * @param unknown $insertData
     */
    public function batchInsert($insertData){
    	$prefix = C('DB_PREFIX');
    	$emBuilding = M('em_building');
    	$emVillage = M('em_village');
    	for($i=1;$i<=count($insertData);$i++){
    		if($i == 1 || $i == 2){
    			continue;
    		}
    		$buildingData = $insertData[$i];
    		
    		$buildingName = $buildingData[0];
    		if(empty($buildingName)){
    			$this->error("楼宇名称不能为空！");
    		}
    		
    		$data['BUILDING_NAME'] = $buildingName;
    		
    		$villageName= $buildingData[1];
    		$em_village = $emVillage->where("village_name = '$villageName'")->find();
    		if(empty($em_village)){
    			$this->error("小区名称：".$villageName."不存在");
    		}
    		$data['VILLAGE'] = $em_village['village_id'];
    			
    		$data['UNIT_NUMBER'] = trim($buildingData[2]);
    		$data['FLOOR_NUMBER'] = trim($buildingData[3]);
    		
    		//楼宇类型
    		$buildingType = trim($buildingData[4]);
    		$buildingTypeDict = M('em_dictionary')->where("dict_name = 'buildingType' and dict_key = '%s'",array($buildingType))->find();
    		$data['BUILDING_TYPE'] = $buildingTypeDict['dict_value'];
    		
    		//楼宇结构
    		$buildingStructure = trim($buildingData[5]);
    		$buildingStructureDict = M('em_dictionary')->where("dict_name = 'buildingStructure' and dict_key = '%s'",array($buildingStructure))->find();
    		$data['BUILDING_STRUCTURE'] = $buildingStructureDict['dict_value'];
    		
    		//楼宇朝向
    		$buildingOrientation = trim($buildingData[6]);
    		$buildingOrientationDict = M('em_dictionary')->where("dict_name = 'buildingOrientation' and dict_key = '%s'",array($buildingOrientation))->find();
    		$data['BUILDING_ORIENTATION'] = $buildingOrientationDict['dict_value'];
    		
    		$data['OPERATOR'] = session('uid');
    		
    		$timenow=date('Y-m-d H:i:s',time());
    		
    		$data['CREATE_TIME'] = $timenow;
    		$data['MODIFY_TIME'] = $timenow;
    		$emBuilding->data($data)->add();
    	}
    }
    
    /**
     * array类型组织机构数据转换成二维数组形式的map，key为组织机构名称
     * @param unknown $em_sys_org 组织机构数组
     * @return unknown
     */
    private function arrayToMap($em_sys_org){
    	for($i=0;$i<count($em_sys_org);$i++){
    		$emSysOrgMap[$em_sys_org[$i]['org_name']] = $em_sys_org[$i];
		}
		return $emSysOrgMap;
    }
}