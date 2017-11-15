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
    	$uid =  session('uid');
    	if($uid == null){
    		$this->error('您还未登录！');
    	}
    	
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


        //如果不是超级管理员，需要添加当前登录用户数据权限
        if($uid != 1){
        	$member = M('member')->where("uid = $uid")->find();
        	$phone = $member['phone'];
        	if($where != ""){
        		$where = $where . "and hh.tel = $phone";
        	}else{
        		$where ="hh.tel = $phone";
        	}
        }
        
        $emHouse = M('em_house');
        $pagesize = 10;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $model = $emHouse->field("{$prefix}em_house.*")
            ->order($order)
            ->where($where)
            ->join("left join {$prefix}em_village v ON {$prefix}em_house.village = v.village_id")
            ->join("left join {$prefix}em_building b ON {$prefix}em_house.building = b.building_id")
            ->join("left join {$prefix}em_unit u ON {$prefix}em_house.unit = u.unit_id")
            ->join("left join {$prefix}member m ON {$prefix}em_house.operator = m.uid")
            ->join("left join {$prefix}em_dictionary d1 ON {$prefix}em_house.house_type = d1.dict_value and d1.dict_name = 'houseType'")
            ->join("left join {$prefix}em_dictionary d2 ON {$prefix}em_house.house_structure = d2.dict_value and d2.dict_name = 'buildingStructure'")
            ->join("left join {$prefix}em_dictionary d3 ON {$prefix}em_house.house_orientation = d3.dict_value and d3.dict_name = 'buildingOrientation'");
        if($uid != 1){
        	$model = $model->join("left join {$prefix}em_household hh ON {$prefix}em_house.village = hh.village");
        }
        $count = $model->count();
            
        $listModel = $emHouse->field("{$prefix}em_house.*,v.village_name,b.building_name,u.unit_name,m.user as user,
				d1.dict_key as house_type,d2.dict_key as building_structure,d3.dict_key as building_orientation")
            ->order($order)
            ->where($where)
            ->join("left join {$prefix}em_village v ON {$prefix}em_house.village = v.village_id")
            ->join("left join {$prefix}em_building b ON {$prefix}em_house.building = b.building_id")
            ->join("left join {$prefix}em_unit u ON {$prefix}em_house.unit = u.unit_id")
            ->join("left join {$prefix}member m ON {$prefix}em_house.operator = m.uid")
            ->join("left join {$prefix}em_dictionary d1 ON {$prefix}em_house.house_type = d1.dict_value and d1.dict_name = 'houseType'")
            ->join("left join {$prefix}em_dictionary d2 ON {$prefix}em_house.house_structure = d2.dict_value and d2.dict_name = 'buildingStructure'")
            ->join("left join {$prefix}em_dictionary d3 ON {$prefix}em_house.house_orientation = d3.dict_value and d3.dict_name = 'buildingOrientation'");
        if($uid != 1){
        	$listModel= $listModel->join("left join {$prefix}em_household hh ON {$prefix}em_house.village = hh.village");
        }
        $list = $listModel->limit($offset . ',' . $pagesize)
            ->select();
        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }

    public function del()
    {
    	$houseIds= isset($_REQUEST['houseIds']) ? $_REQUEST['houseIds'] : false;
    	if(!$houseIds){
    		$this->error('需要删除的房屋为空，请选择房屋！');
    	}
    	
    	if (is_array($houseIds)) {
    		foreach ($houseIds as $k => $v) {
    			$houseIds[$k] = intval($v);
            }
            if (!$houseIds) {
                $this->error('参数错误！');
                $houseIds= implode(',', $houseIds);
            }
        }
        
        if (is_array($houseIds)) {
        	for ($i=0;$i<count($houseIds);$i++){
        		$houseId = $houseIds[$i];
        		$emHousehold = M('em_household')->where("house = $houseId")->select();
        		if(!empty($emHousehold)){
        			$this->error('房屋：' . $houseId. '下包含住户信息，不能删除!');
        		}
        	}
        }else{
        	$emHousehold = M('em_household')->where("house = $houseIds")->select();
        	if(!empty($emHousehold)){
        		$this->error('房屋：' . $houseIds. '下包含住户信息，不能删除!');
        	}
        }
        
        $map['HOUSE_ID'] = array('in', $houseIds);
        if (M('em_house')->where($map)->delete()) {
        	addlog('删除房屋ID：' . $houseIds);
            $this->success('恭喜，房屋删除成功！');
        } else {
            $this->error('参数错误！');
        }
    }

    public function edit()
    {

        $vid = isset($_GET['house_id']) ? intval($_GET['house_id']) : false;
        if ($vid) {
            $prefix = C('DB_PREFIX');
            $em_house= M('em_house')->field("{$prefix}em_house.*")->where("{$prefix}em_house.house_id=$vid")->find();
            $em_village = M('em_village')->field("village_id,village_name")->select();
            $village = $em_house['village'];
            $em_building = M('em_building')->field("building_id,building_name")->where("village = $village")->select();
            $building = $em_house['building'];
            $em_unit = M('em_unit')->field("unit_id,unit_name")->where("building = $building")->select();
            
            $houseTypes = M('em_dictionary')->field("dict_key,dict_value")
            ->where("dict_name = 'houseType'")
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
        $this->assign('em_house', $em_house);
        $this->assign('villages', $em_village);
        $this->assign('buildings', $em_building);
        $this->assign('units', $em_unit);
        $this->assign('houseTypes', $houseTypes);
        $this->assign('houseOrientations', $buildingOrientations);
        $this->assign('houseStructures', $buildingStructures);
        $this->display('form');
    }

    public function update($ajax = '')
    {
    	$houseId = isset($_POST['house_id']) ? intval($_POST['house_id']) : false;
    	$data['HOUSE_NAME'] = isset($_POST['HOUSE_NAME']) ? trim($_POST['HOUSE_NAME']) : '';
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
    	
    	$unit = isset($_POST['unit']) ? trim($_POST['unit']) : false;
    	if(!$unit){
    		$this->error('所属单元不能为空！');
    	}else{
    		$data['UNIT']= $unit;
    	}
        
        $data['FLOOR'] = isset($_POST['FLOOR']) ? trim($_POST['FLOOR']) : '';
        $data['BUILD_UP_AREA'] = isset($_POST['BUILD_UP_AREA']) ? trim($_POST['BUILD_UP_AREA']) : '';
        $data['SET_IN_AREA'] = isset($_POST['SET_IN_AREA']) ? trim($_POST['SET_IN_AREA']) : '';
        $data['POLL_AREA'] = isset($_POST['POLL_AREA']) ? trim($_POST['POLL_AREA']) : '';
        
        $data['HOUSE_TYPE'] = isset($_POST['house_type']) ? trim($_POST['house_type']) : '';
        $data['HOUSE_STRUCTURE'] = isset($_POST['house_structure']) ? trim($_POST['house_structure']) : '';
        $data['HOUSE_ORIENTATION'] = isset($_POST['house_orientation']) ? trim($_POST['house_orientation']) : '';
        
        $transfer_time = isset($_POST['HOUSE_TRANSFER_TIME']) ? trim($_POST['HOUSE_TRANSFER_TIME']) : false;
        if($transfer_time){
        	$data['HOUSE_TRANSFER_TIME'] = $transfer_time;
        }
        $data['PROPERTY_RIGHT_AGE'] = isset($_POST['PROPERTY_RIGHT_AGE']) ? trim($_POST['PROPERTY_RIGHT_AGE']) : '';
		
        $data['OPERATOR'] = session('uid');
        
        $timenow=date('Y-m-d H:i:s',time());
        if (!$houseId) {
        	$data['CREATE_TIME'] = $timenow;
        	$data['MODIFY_TIME'] = $timenow;
        	if (house_name== '') {
                $this->error('房号不能为空！');
            }
            $houseId = M('em_house')->data($data)->add();
            addlog('新增房屋，房屋ID：' . $houseId);
        } else {
        	$data['MODIFY_TIME'] = $timenow;
        	M('em_house')->data($data)->where("house_id=$houseId")->save();
        	addlog('编辑房屋信息，房屋ID：' . $houseId);

        }
        $this->success('操作成功！','index');
    }


    public function add()
    {
    	$prefix = C('DB_PREFIX');
    	$emVillages = M('em_village')->field("village_id,village_name")->select();
    	$houseTypes = M('em_dictionary')->field("dict_key,dict_value")
    	->where("dict_name = 'houseType'")
    	->select();
    	$buildingOrientations = M('em_dictionary')->field("dict_key,dict_value")
    	->where("dict_name = 'buildingOrientation'")
    	->select();
    	$buildingStructures = M('em_dictionary')->field("dict_key,dict_value")
    	->where("dict_name = 'buildingStructure'")
    	->select();
    	$this->assign('villages', $emVillages);
    	$this->assign('houseTypes', $houseTypes);
    	$this->assign('houseOrientations', $buildingOrientations);
    	$this->assign('houseStructures', $buildingStructures);
        $this->display('form');
    }
    
    
    /**
     * 导出excel
     */
    public function exportHouse()
    {
    	$xlsName  = "房屋";
    	$xlsCell  = array(
    			array('house_id','房屋编号'),
    			array('house_name','房号'),
    			array('village','所属小区'),
    			array('building','所属楼宇'),
    			array('unit','所属单元'),
    			array('floor','所在楼层'),
    			array('build_up_area','建筑面积'),
    			array('set_in_area','套内面积'),
    			array('poll_area','公摊面积'),
    			array('house_type','房屋类型'),
    			array('house_structure','房屋结构'),
    			array('house_orientation','房屋朝向'),
    			array('house_transfer_time','交房时间'),
    			array('property_right_age','产权年限'),
    			array('create_time','创建时间'),
    			array('modify_time','修改时间'),
    			array('operator','操作人')
    	);
    	
    	$prefix = C('DB_PREFIX');
    	$emHouse = M('em_house');
//     	$where = "{$prefix}em_village.village_id = 2";
    	$list = $emHouse->field("house_id,house_name,floor,house_transfer_time,property_right_age,d1.dict_key as house_type,d2.dict_key as house_structure,
    			d3.dict_key as house_orientation,{$prefix}em_house.create_time,{$prefix}em_house.modify_time,build_up_area,set_in_area,
    			poll_area,b.user as operator,v.village_name as village,bd.building_name as building,u.unit_name as unit")
    	->join("left join {$prefix}em_village v ON {$prefix}em_house.village = v.village_id")
    	->join("left join {$prefix}em_building bd ON {$prefix}em_house.building = bd.building_id")
    	->join("left join {$prefix}em_unit u ON {$prefix}em_house.unit = u.unit_id")
    	->join("left join {$prefix}em_dictionary d1 ON {$prefix}em_house.house_type = d1.dict_value and d1.dict_name = 'houseType'")
    	->join("left join {$prefix}em_dictionary d2 ON {$prefix}em_house.house_structure = d2.dict_value and d2.dict_name = 'buildingStructure'")
    	->join("left join {$prefix}em_dictionary d3 ON {$prefix}em_house.house_orientation = d3.dict_value and d3.dict_name = 'buildingOrientation'")
    	->join("left join {$prefix}member b ON {$prefix}em_house.operator = b.uid")
    	->select();
    	
//     	$em_village = $emVillage->field("{$prefix}em_village.*")->select();
//     	var_dump($list);
    	$this->exportExcel($xlsName,$xlsCell,$list);
    }
    
    /**
     * 导出excel导入模板
     */
    public function exportExcelTemplate(){
    	$xlsName  = "导入房屋模板";
    	$xlsCell  = array(
    			array('house_name','房号'),
    			array('village','所属小区'),
    			array('building','所属楼宇'),
    			array('unit','所属单元'),
    			array('floor','所在楼层'),
    			array('build_up_area','建筑面积'),
    			array('set_in_area','套内面积'),
    			array('poll_area','公摊面积'),
    			array('house_type','房屋类型'),
    			array('house_structure','房屋结构'),
    			array('house_orientation','房屋朝向'),
    			array('house_transfer_time','交房时间'),
    			array('property_right_age','产权年限')
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
    	$emHouse = M('em_house');
    	$emVillage = M('em_village');
    	for($i=1;$i<=count($insertData);$i++){
    		if($i == 1 || $i == 2){
    			continue;
    		}
    		$houseData = $insertData[$i];
    		
    		$houseName = $houseData[0];
    		if(empty($houseName)){
    			$this->error("房号不能为空！");
    		}
    		
    		$data['HOUSE_NAME'] = $houseName;
    		
    		$villageName = trim($houseData[1]);
    		$em_village = $emVillage->where("village_name = '$villageName'")->find();
    		if(empty($em_village)){
    			$this->error("小区名称：".$villageName."不存在");
    		}else{
	    		$data['VILLAGE'] = $em_village['village_id'];
    		}
    		
    		$buildingName = trim($houseData[2]);
    		$map1['BUILDING_NAME'] = $buildingName;
    		$map1['VILLAGE'] = $em_village['village_id'];
    		$em_building = M('em_building')->where($map1)->find();
    		if(empty($em_building)){
    			$this->error("小区：" . $villageName . ",楼宇名称：" . $buildingName. "不存在");
    		}else{
    			$data['BUILDING'] = $em_building['building_id'];
    		}
    		
    		$unitName = trim($houseData[3]);
    		$map2['BUILDING'] = $em_building['building_id'];
    		$map2['VILLAGE'] = $em_village['village_id'];
    		$map2['UNIT_NAME'] = $unitName;
    		$em_unit = M('em_unit')->where($map2)->find();
    		if(empty($em_unit)){
    			$this->error("小区：" . $villageName . ",楼宇名称：" . $buildingName . ",单元名称：" . $unitName . "不存在");
    		}else{
    			$data['UNIT'] = $em_unit['unit_id'];
    		}
    		
    		$data['FLOOR'] = $houseData[4];
    		$data['BUILD_UP_AREA'] = $houseData[5];
    		$data['SET_IN_AREA'] = $houseData[6];
    		$data['POLL_AREA'] = $houseData[7];
    		
    		//房屋类型
    		$houseType = trim($houseData[8]);
    		$houseTypeDict = M('em_dictionary')->where("dict_name = 'houseType' and dict_key = '%s'",array($houseType))->find();
    		$data['HOUSE_TYPE'] = $houseTypeDict['dict_value'];
    		
    		//房屋结构
    		$houseStructure = trim($houseData[9]);
    		$houseStructureDict= M('em_dictionary')->where("dict_name = 'buildingStructure' and dict_key = '%s'",array($houseStructure))->find();
    		$data['HOUSE_STRUCTURE'] = $houseStructureDict['dict_value'];
    		
    		//房屋朝向
    		$houseOrientation = trim($houseData[10]);
    		$houseOrientationDict = M('em_dictionary')->where("dict_name = 'buildingOrientation' and dict_key = '%s'",array($houseOrientation))->find();
    		$data['HOUSE_ORIENTATION'] = $houseOrientationDict['dict_value'];
    		
    		//交房时间
    		$data['HOUSE_TRANSFER_TIME'] = $houseData[11];
    		//产权年限
    		$data['PROPERTY_RIGHT_AGE'] = $houseData[12];
    		
    		$data['OPERATOR'] = session('uid');
    		
    		$timenow=date('Y-m-d H:i:s',time());
    		
    		$data['CREATE_TIME'] = $timenow;
    		$data['MODIFY_TIME'] = $timenow;
    		$emHouse->data($data)->add();
    	}
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
    
    public function uploadAttachedfile()
    {
    	$upload = new \Think\Upload();                      // 实例化上传类
    	$upload->maxSize = 104857600 ;                 // 设置附件上传大小,10M
    	$upload->exts = array(
    			'xls',
    			'xlsx',
    			'doc',
    			'pdf',
    			'csv',
    			'jpeg',
    			'jpg',
    			'jpeg',
    			'png',
    			'pjpeg',
    			'gif',
    			'bmp',
    			'x-png');       // 设置附件上传类型
    	$upload->rootPath  = './Public/';             // 设置附件上传根目录
    	$savePath->savePath = 'attached/'.date('Y')."/".date('m')."/";
    	$upload->autoSub   = false;                   // 将自动生成以当前时间的形式的子文件夹，关闭
    	
    	//     		var_dump($upload);
    	
    	$info = $upload->upload();                             // 上传文件
    	
    	if(!$info) {// 上传错误提示错误信息
    		$error = $upload->getError();
    		$this->error($error);
    	}else{// 上传成功
    		foreach ($info as $item) {
    			$filePath[] = __ROOT__."/Public/".$item['savepath'].$item['savename'];
    		}
    		$fileStr = implode("|", $filePath);
    		$this->success('上传成功！');
    	}
    }
}
