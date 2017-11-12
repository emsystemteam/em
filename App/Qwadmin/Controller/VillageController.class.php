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

class VillageController extends ComController
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
            $order = "{$prefix}em_village.MODIFY_TIME asc";
        } elseif (($order == 'desc')) {
        	$order = "{$prefix}em_village.MODIFY_TIME desc";
        } else {
        	$order = "{$prefix}em_village.VILLAGE_ID asc";
        }
        if ($keyword <> '') {
            if ($field == 'village_name') {
            	$where = "{$prefix}em_village.village_name LIKE '%$keyword%'";
            }
            if ($field == 'village_contacts') {
            	$where = "{$prefix}em_village.owners_committee_contacts LIKE '%$keyword%'";
            }
        }


        $emVillage = M('em_village');
        $pagesize = 10;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $count = $emVillage->field("{$prefix}em_village.*")
            ->order($order)
            ->where($where)
            ->count();

        $list = $emVillage->field("{$prefix}em_village.*,s1.org_name as province,s2.org_name as city,s3.org_name as county,s4.org_name as street,s5.org_name as committee")
            ->order($order)
            ->where($where)
            ->join("left join {$prefix}em_sys_org s1 ON {$prefix}em_village.province = s1.org_id")
            ->join("left join {$prefix}em_sys_org s2 ON {$prefix}em_village.city = s2.org_id")
            ->join("left join {$prefix}em_sys_org s3 ON {$prefix}em_village.county = s3.org_id")
            ->join("left join {$prefix}em_sys_org s4 ON {$prefix}em_village.street = s4.org_id")
            ->join("left join {$prefix}em_sys_org s5 ON {$prefix}em_village.neigh_committee = s5.org_id")
            ->limit($offset . ',' . $pagesize)
            ->select();
//         var_dump($list);
        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();
//         trace($list);
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }

    public function del()
    {
    	$villageIds= isset($_REQUEST['villageIds']) ? $_REQUEST['villageIds'] : false;
    	if (is_array($villageIds)) {
    		foreach ($villageIds as $k => $v) {
                $villageIds[$k] = intval($v);
            }
            if (!$villageIds) {
                $this->error('参数错误！');
                $villageIds= implode(',', $villageIds);
            }
        }
        $map['VILLAGE_ID'] = array('in', $villageIds);
        if (is_array($villageIds)) {
        	for ($i=0;$i<count($villageIds);$i++){
        		$villageId = $villageIds[$i];
        		$emBuilding = M('em_building')->where("village = $villageId")->select();
        		if(!empty($emBuilding)){
        			$this->error('小区：' . $villageId. '下包含楼宇信息，不能删除!');
        		}
        	}
        }else{
        	$emBuilding = M('em_building')->where("village = $villageIds")->select();
        	if(!empty($emBuilding)){
        		$this->error('小区：' . $villageIds. '下包含楼宇信息，不能删除!');
        	}
        }
        
        
        if (M('em_village')->where($map)->delete()) {
        	addlog('删除小区UID：' . $villageIds);
            $this->success('恭喜，小区删除成功！');
        } else {
            $this->error('参数错误！');
        }
    }

    public function edit()
    {

        $vid = isset($_GET['village_id']) ? intval($_GET['village_id']) : false;
        if ($vid) {
            //$member = M('member')->where("uid='$uid'")->find();
            $prefix = C('DB_PREFIX');
            $emVillage = M('em_village');
            $em_village = $emVillage->field("{$prefix}em_village.*")->where("{$prefix}em_village.village_id=$vid")->find();
			$emSysOrg = M('em_sys_org');
			$provinces = $emSysOrg->field("org_id,org_name")->where("org_type=1")->select();
			$citys = $emSysOrg->field("org_id,org_name")->where("org_type='%d' and parent_id = '%s'",array(2,$em_village['province']))->select();
			$countys = $emSysOrg->field("org_id,org_name")->where("org_type='%d' and parent_id = '%s'",array(3,$em_village['city']))->select();
			trace($citys);
			$streets = $emSysOrg->field("org_id,org_name")->where("org_type='%d' and parent_id = '%s'",array(4,$em_village['county']))->select();
			$committees = $emSysOrg->field("org_id,org_name")->where("org_type='%d' and parent_id = '%s'",array(5,$em_village['street']))->select();
        } else {
            $this->error('参数错误！');
        }
        $this->assign('em_village', $em_village);
        $this->assign('provinces', $provinces);
        $this->assign('citys', $citys);
        $this->assign('countys', $countys);
        $this->assign('streets', $streets);
        $this->assign('committees', $committees);
        $this->display('form');
    }

    public function update($ajax = '')
    {
        /* if ($ajax == 'yes') {
            $uid = I('get.uid', 0, 'intval');
            $gid = I('get.gid', 0, 'intval');
            M('auth_group_access')->data(array('group_id' => $gid))->where("uid='$uid'")->save();
            die('1');
        } */

        $villageId = isset($_POST['village_id']) ? intval($_POST['village_id']) : false;
    	$data['VILLAGE_NAME'] = isset($_POST['VILLAGE_NAME']) ? trim($_POST['VILLAGE_NAME']) : '';
    	if ($data['VILLAGE_NAME']== '') {
    		$this->error('小区名称不能为空！');
    	}
    	
    	$data['PROPERTY_COMPANY']= isset($_POST['PROPERTY_COMPANY']) ? trim($_POST['PROPERTY_COMPANY']) : false;
        $logo = I('post.VILLAGE_LOGO', '', 'strip_tags');
        $data['VILLAGE_LOGO'] = $logo? $logo: '';
        $data['PROPERTY_CUSTOMER_TEL'] = isset($_POST['PROPERTY_CUSTOMER_TEL']) ? trim($_POST['PROPERTY_CUSTOMER_TEL']) : '';
        $data['PROPERTY_CHARGE_PERSON'] = isset($_POST['PROPERTY_CHARGE_PERSON']) ? trim($_POST['PROPERTY_CHARGE_PERSON']) : '';
        $data['PROPERTY_CHARGE_PERSON_TEL'] = isset($_POST['PROPERTY_CHARGE_PERSON_TEL']) ? trim($_POST['PROPERTY_CHARGE_PERSON_TEL']) : '';
        $data['OWNERS_COMMITTEE_CONTACTS'] = isset($_POST['OWNERS_COMMITTEE_CONTACTS']) ? trim($_POST['OWNERS_COMMITTEE_CONTACTS']) : '';
        $data['OWNERS_COMMITTEE_TEL'] = isset($_POST['OWNERS_COMMITTEE_TEL']) ? trim($_POST['OWNERS_COMMITTEE_TEL']) : '';
        $data['VILLAGE_CONTACTS'] = isset($_POST['VILLAGE_CONTACTS']) ? trim($_POST['VILLAGE_CONTACTS']) : '';
        $data['VILLAGE_CONTACTS_TEL'] = isset($_POST['VILLAGE_CONTACTS_TEL']) ? trim($_POST['VILLAGE_CONTACTS_TEL']) : '';
		
        $data['OPERATOR'] = session('uid');
        
        $province = isset($_POST['province']) ? trim($_POST['province']) : false;
        if(!$province){
        	//$this->error('省不能为空！');
        }else{
        	$data['PROVINCE'] = $province;
        }
        $city = isset($_POST['city']) ? trim($_POST['city']) : false;
        if(!$city){
        	//$this->error('市不能为空！');
        	$data['CITY'] = null;
        }else{
        	$data['CITY'] = $city;
        }
        $county = isset($_POST['county']) ? trim($_POST['county']) : false;
        if(!$county){
        	//$this->error('县区不能为空！');
        	$data['COUNTY'] = null;
        }else{
        	$data['COUNTY'] = $county;
        }
        if(!isset($_POST['street']) || $_POST['street'] == ''){
        	$data['STREET'] = null;
        }else{
        	$data['STREET'] = trim($_POST['street']);
        }
        
        if(!isset($_POST['committee'])|| $_POST['committee'] == ''){
        	$data['NEIGH_COMMITTEE']= null;
        }else{
        	$data['NEIGH_COMMITTEE'] = trim($_POST['committee']);
        }
        
        $timenow=date('Y-m-d H:i:s',time());
        if (!$villageId) {
        	$data['CREATE_TIME'] = $timenow;
        	$data['MODIFY_TIME'] = $timenow;
            $uid = M('em_village')->data($data)->add();
            addlog('新增小区，小区ID：' . $villageId);
        } else {
        	$data['MODIFY_TIME'] = $timenow;
        	M('em_village')->data($data)->where("village_id=$villageId")->save();
            addlog('编辑小区信息，小区ID：' . $villageId);

        }
        $this->success('操作成功！');
    }


    public function add()
    {
    	$prefix = C('DB_PREFIX');
    	$emSysOrg = M('em_sys_org');
    	$provinces = $emSysOrg->field("org_id,org_name")->where("org_type=1")->select();
    	$citys = $emSysOrg->field("org_id,org_name")->where("org_type=2")->select();
    	//$countys = $emSysOrg->field("org_id,org_name")->where("org_type=3")->select();
    	//$streets = $emSysOrg->field("org_id,org_name")->where("org_type=4")->select();
    	//$committees = $emSysOrg->field("org_id,org_name")->where("org_type=5")->select();
    	$this->assign('provinces', $provinces);
    	$this->assign('citys', $citys);
    	//$this->assign('countys', $countys);
    	//$this->assign('streets', $streets);
    	//$this->assign('committees', $committees);
        $this->display('form');
    }
    
    /**
     * 组织机构级联
     * @param string $orgId 组织机构id
     * @param string $orgType 组织机构类型，1.省，2.市，3.县（区），4.街道（社区），5.社居委
     */
    public function changeOrg($orgId = FALSE,$orgType = FALSE)
    {
    	$emSysOrg = M('em_sys_org');
    	$thisOrg = $emSysOrg->field("org_id,org_name")->where("org_type = $orgType and parent_id = $orgId")->select();
//     	trace($thisOrg);
    	$this->ajaxReturn($thisOrg);
    }
    
    
    
    /**
     * 导出excel
     */
    public function exportVillage()
    {
    	$xlsName  = "小区";
    	$xlsCell  = array(
    			array('village_id','小区编号'),
    			array('village_name','小区名称'),
    			array('province','省'),
    			array('city','市'),
    			array('county','县(区)'),
    			array('street','街道（社区）'),
    			array('neigh_committee','社居委'),
    			array('property_company','物业服务公司'),
    			array('property_customer_tel','物业客服电话'),
    			array('property_charge_person','物业负责人'),
    			array('property_charge_person_tel','物业负责人电话'),
    			array('owners_committee_contacts','业主委员会联系人'),
    			array('owners_committee_tel','业主委员会电话'),
    			array('village_contacts','联系人'),
    			array('village_contacts_tel','联系人电话'),
    			/* array('village_logo','小区logo'), */
    			array('create_time','创建时间'),
    			array('modify_time','修改时间'),
    			array('operator','操作人')
    	);
    	
    	$prefix = C('DB_PREFIX');
    	$emVillage = M('em_village');
//     	$where = "{$prefix}em_village.village_id = 2";
    	$list = $emVillage->field("village_id,village_name,property_company,property_customer_tel,property_charge_person,property_charge_person_tel,
			owners_committee_contacts,owners_committee_tel,village_contacts,village_contacts_tel,{$prefix}em_village.create_time,{$prefix}em_village.modify_time,
			b.user as operator,s1.org_name as province,
			s2.org_name as city,s3.org_name as county,s4.org_name as street,s5.org_name as neigh_committee")
    	->join("left join {$prefix}em_sys_org s1 ON {$prefix}em_village.province = s1.org_id")
    	->join("left join {$prefix}em_sys_org s2 ON {$prefix}em_village.city = s2.org_id")
    	->join("left join {$prefix}em_sys_org s3 ON {$prefix}em_village.county = s3.org_id")
    	->join("left join {$prefix}em_sys_org s4 ON {$prefix}em_village.street = s4.org_id")
    	->join("left join {$prefix}em_sys_org s5 ON {$prefix}em_village.neigh_committee = s5.org_id")
    	->join("left join {$prefix}member b ON {$prefix}em_village.operator = b.uid")
    	->select();
    	
//     	$em_village = $emVillage->field("{$prefix}em_village.*")->select();
//     	var_dump($list);
    	$this->exportExcel($xlsName,$xlsCell,$list);
    }
    
    /**
     * 导出excel导入模板
     */
    public function exportExcelTemplate(){
    	$xlsName  = "导入小区模板";
    	$xlsCell  = array(
    			array('village_name','小区名称'),
    			array('province','省'),
    			array('city','市'),
    			array('county','县(区)'),
    			array('street','街道（社区）'),
    			array('neigh_committee','社居委'),
    			array('property_company','物业服务公司'),
    			array('property_customer_tel','物业客服电话'),
    			array('property_charge_person','物业负责人'),
    			array('property_charge_person_tel','物业负责人电话'),
    			array('owners_committee_contacts','业主委员会联系人'),
    			array('owners_committee_tel','业主委员会电话'),
    			array('village_contacts','联系人'),
    			array('village_contacts_tel','联系人电话')
    			/* array('village_logo','小区logo'), 
    			array('create_time','创建时间'),
    			array('modify_time','修改时间'),
    			array('operator','操作人')*/
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
    		$this->success('上传成功！');
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
    	$emVillage = M('em_village');
    	$emSysOrg = M('em_sys_org');
    	$em_sys_org = $emSysOrg->select();
    	$emSysOrgMap = $this->arrayToMap($em_sys_org);
//     	var_dump($emSysOrgMap);
//     	var_dump($insertData);
    	for($i=1;$i<=count($insertData);$i++){
    		if($i == 1 || $i == 2){
    			continue;
    		}
    		$villageData = $insertData[$i];
    		
    		$villageName = $villageData[0];
    		if(is_object($villageName)){
    			$villageName = $villageName->_toString();
    		}
    		var_dump($villageName);
    		if(empty($villageName)){
    			$this->error("小区名称不能为空！");
    		}
    		$em_village = $emVillage->where("village_name = '$villageName'")->select();
    		if(!empty($em_village)){
    			$this->error("小区名称：".$villageName."已经存在");
    		}
    		$data['VILLAGE_NAME'] = $villageName;
    		//省
    		$province = trim($villageData[1]);
    		if(!empty($province)){
    			$data['PROVINCE'] = $emSysOrgMap[$province]['org_id'];
    		}
    			
    		//市
    		$city = trim($villageData[2]);
    		if(!empty($city)){
    			$data['CITY'] = $emSysOrgMap[$city]['org_id'];
    		}
    			
    		//县区
    		$county = trim($villageData[3]);
    		if(!empty($county)){
    			$data['COUNTY'] = $emSysOrgMap[$county]['org_id'];
    		}
    			
    		//街道（社区）
    		$street = trim($villageData[4]);
    		if(!empty($street)){
    			$data['STREET'] = $emSysOrgMap[$street]['org_id'];
    		}
    			
    		//社居委
    		$committee = trim($villageData[5]);
    		if(!empty($committee)){
    			$data['NEIGH_COMMITTEE'] = $emSysOrgMap[$committee]['org_id'];
    		}
    			
    		$data['PROPERTY_COMPANY'] = trim($villageData[6]);
    		$data['PROPERTY_CUSTOMER_TEL'] = trim($villageData[7]);
    		$data['PROPERTY_CHARGE_PERSON'] = trim($villageData[8]);
    		$data['PROPERTY_CHARGE_PERSON_TEL'] = trim($villageData[9]);
    		$data['OWNERS_COMMITTEE_CONTACTS'] = trim($villageData[10]);
    		$data['OWNERS_COMMITTEE_TEL'] = trim($villageData[11]);
    		$data['VILLAGE_CONTACTS'] = trim($villageData[12]);
    		$data['VILLAGE_CONTACTS_TEL'] = trim($villageData[13]);
    		
    		$data['OPERATOR'] = session('uid');
    		
    		$timenow=date('Y-m-d H:i:s',time());
    		
    		$data['CREATE_TIME'] = $timenow;
    		$data['MODIFY_TIME'] = $timenow;
    		
    		$emVillage->data($data)->add();
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
