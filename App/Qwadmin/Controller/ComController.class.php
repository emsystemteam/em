<?php
/**
 *
 * 版权所有：恰维网络<qwadmin.qiawei.com>
 * 作    者：寒川<hanchuan@qiawei.com>
 * 日    期：2015-09-17
 * 版    本：1.0.0
 * 功能说明：后台公用控制器。
 *
 **/

namespace Qwadmin\Controller;

use Common\Controller\BaseController;
use Think\Auth;

class ComController extends BaseController
{
    public $USER;

    public function _initialize()
    {

        C(setting());
        if (!C("COOKIE_SALT")) {
            $this->error('请配置COOKIE_SALT信息');
        }
        /**
         * 不需要登录控制器
         */
        if (in_array(CONTROLLER_NAME, array("Login"))) {
            return true;
        }
        //检测是否登录
        $flag =  $this->check_login();
        $url = U("login/index");
        if (!$flag) {
            header("Location: {$url}");
            exit(0);
        }
        $m = M();
        $prefix = C('DB_PREFIX');
        $UID = $this->USER['uid'];
        $userinfo = $m->query("SELECT * FROM {$prefix}auth_group g left join {$prefix}auth_group_access a on g.id=a.group_id where a.uid=$UID");
        $Auth = new Auth();
        $allow_controller_name = array('Upload');//放行控制器名称
        $allow_action_name = array();//放行函数名称
        if ($userinfo[0]['group_id'] != 1 && !$Auth->check(CONTROLLER_NAME . '/' . ACTION_NAME,
                $UID) && !in_array(CONTROLLER_NAME, $allow_controller_name) && !in_array(ACTION_NAME,
                $allow_action_name)
        ) {
            $this->error('没有权限访问本页面!');
        }

        $user = member(intval($UID));
        $this->assign('user', $user);


        $current_action_name = ACTION_NAME == 'edit' ? "index" : ACTION_NAME;
        $current = $m->query("SELECT s.id,s.title,s.name,s.tips,s.pid,p.pid as ppid,p.title as ptitle FROM {$prefix}auth_rule s left join {$prefix}auth_rule p on p.id=s.pid where s.name='" . CONTROLLER_NAME . '/' . $current_action_name . "'");
        $this->assign('current', $current[0]);


        $menu_access_id = $userinfo[0]['rules'];

        if ($userinfo[0]['group_id'] != 1) {

            $menu_where = "AND id in ($menu_access_id)";

        } else {

            $menu_where = '';
        }
        $menu = M('auth_rule')->field('id,title,pid,name,icon')->where("islink=1 $menu_where ")->order('o ASC')->select();
        $menu = $this->getMenu($menu);
        $this->assign('menu', $menu);

    }


    protected function getMenu($items, $id = 'id', $pid = 'pid', $son = 'children')
    {
        $tree = array();
        $tmpMap = array();
        //修复父类设置islink=0，但是子类仍然显示的bug @感谢linshaoneng提供代码
        foreach( $items as $item ){
            if( $item['pid']==0 ){
                $father_ids[] = $item['id'];
            }
        }
        //----
        foreach ($items as $item) {
            $tmpMap[$item[$id]] = $item;
        }

        foreach ($items as $item) {
            //修复父类设置islink=0，但是子类仍然显示的bug by shaoneng @感谢linshaoneng提供代码
            if( $item['pid']<>0 && !in_array( $item['pid'], $father_ids )){
                continue;
            }
            //----
            if (isset($tmpMap[$item[$pid]])) {
                $tmpMap[$item[$pid]][$son][] = &$tmpMap[$item[$id]];
            } else {
                $tree[] = &$tmpMap[$item[$id]];
            }
        }
        return $tree;
    }

    public function check_login(){
        session_start();
        $flag = false;
        $salt = C("COOKIE_SALT");
        $ip = get_client_ip();
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $auth = cookie('auth');
        $uid = session('uid');
        if ($uid) {
            $user = M('member')->where(array('uid' => $uid))->find();

            if ($user) {
                if ($auth ==  password($uid.$user['user'].$ip.$ua.$salt)) {
                    $flag = true;
                    $this->USER = $user;
                }
            }
        }
        return $flag;
    }
    
    /**
     +----------------------------------------------------------
     * Export Excel | 2013.08.23
     * Author:HongPing <hongping626@qq.com>
     +----------------------------------------------------------
     * @param $expTitle     string File name
     +----------------------------------------------------------
     * @param $expCellName  array  Column name
     +----------------------------------------------------------
     * @param $expTableData array  Table data
     +----------------------------------------------------------
     */
    public function exportExcel($expTitle,$expCellName,$expTableData){
    	$xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
    	$fileName = $xlsTitle.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
    	$cellNum = count($expCellName);
    	$dataNum = count($expTableData);
    	//引入PHPExcel库文件
    	import("Org.Util.PHPExcel");
    	$objPHPExcel = new \PHPExcel();
    	$cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
    	
    	$objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
    	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
    	for($i=0;$i<$cellNum;$i++){
    		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
    	}
    	// Miscellaneous glyphs, UTF-8
    	for($i=0;$i<$dataNum;$i++){
    		for($j=0;$j<$cellNum;$j++){
    			$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
    		}
    	}

    	ob_end_clean();//清除缓冲区,避免乱码
    	
//     	header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xlsx"');
    	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    	header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
    	
    	header('Cache-Control: max-age=0');
    	// If you're serving to IE 9, then the following may be needed
    	header('Cache-Control: max-age=1');
    	
    	// If you're serving to IE over SSL, then the following may be needed
    	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    	header ('Pragma: public'); // HTTP/1.0
    	//创建Excel输入对象
    	import("Org.Util.PHPExcel.IOFactory");
    	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    	$objWriter->save('php://output');
//     	var_dump($objPHPExcel->getActiveSheet()->toArray());
    	exit;
    }
    
    /**
     +----------------------------------------------------------
     * Import Excel | 2013.08.23
     * Author:HongPing <hongping626@qq.com>
     +----------------------------------------------------------
     * @param  $file   upload file $_FILES
     +----------------------------------------------------------
     * @return array   array("error","message")
     +----------------------------------------------------------
     */
    public function importExecl($file){
    	if(!file_exists($file)){
    		return array("error"=>0,'message'=>'file not found!');
    	}
    	import("Org.Util.PHPExcel");
    	import("Org.Util.PHPExcel.Reader.Excel5");
    	import("Org.Util.PHPExcel.Cell");
    	//创建Excel输入对象
    	import("Org.Util.PHPExcel.IOFactory");
    	$objReader = \PHPExcel_IOFactory::createReader('Excel5');
    	try{
    		$PHPReader = $objReader->load($file);
    	}catch(Exception $e){}
    	if(!isset($PHPReader)) return array("error"=>0,'message'=>'read error!');
    	$allWorksheets = $PHPReader->getAllSheets();
    	$i = 0;
    	foreach($allWorksheets as $objWorksheet){
    		$sheetname=$objWorksheet->getTitle();
    		$allRow = $objWorksheet->getHighestRow();//how many rows
    		$highestColumn = $objWorksheet->getHighestColumn();//how many columns
    		$allColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
    		$array[$i]["Title"] = $sheetname;
    		$array[$i]["Cols"] = $allColumn;
    		$array[$i]["Rows"] = $allRow;
    		$arr = array();
    		$isMergeCell = array();
    		foreach ($objWorksheet->getMergeCells() as $cells) {//merge cells
    			foreach (\PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
    				$isMergeCell[$cellReference] = true;
    			}
    		}
    		for($currentRow = 1 ;$currentRow<=$allRow;$currentRow++){
    			$row = array();
    			for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++){;
    			$cell =$objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
    			$afCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn+1);
    			$bfCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn-1);
    			$col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
    			$address = $col.$currentRow;
    			$value = $objWorksheet->getCell($address)->getValue();
    			if(is_object($value)){
    				$value = $value->__toString();
    			}
    			if(substr($value,0,1)=='='){
    				return array("error"=>0,'message'=>'can not use the formula!');
    				exit;
    			}
    			if($cell->getDataType()==\PHPExcel_Cell_DataType::TYPE_NUMERIC){
    				$cellstyleformat=$cell->getStyle($cell->getCoordinate() )->getNumberFormat();
    				$formatcode=$cellstyleformat->getFormatCode();
    				if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
    					$value=gmdate("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($value));
    				}else{
    					$value=\PHPExcel_Style_NumberFormat::toFormattedString($value,$formatcode);
    				}
    			}
    			if($isMergeCell[$col.$currentRow]&&$isMergeCell[$afCol.$currentRow]&&!empty($value)){
    				$temp = $value;
    			}elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$col.($currentRow-1)]&&empty($value)){
    				$value=$arr[$currentRow-1][$currentColumn];
    			}elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$bfCol.$currentRow]&&empty($value)){
    				$value=$temp;
    			}
    			$row[$currentColumn] = $value;
    			}
    			$arr[$currentRow] = $row;
    		}
    		$array[$i]["Content"] = $arr;
    		$i++;
    	}
//     	spl_autoload_register(array('Think','autoload'));//must, resolve ThinkPHP and PHPExcel conflicts
    	unset($objWorksheet);
    	unset($PHPReader);
//     	unset($PHPExcel);
    	unlink($file);
    	return array("error"=>1,"data"=>$array);
    }
}