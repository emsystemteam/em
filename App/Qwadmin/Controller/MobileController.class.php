<?php
/**
 *
 * 版权：CopyRight (C) talver 2017
 * 作者：talver (talver@126.com)
 * 日期：Nov 13, 2017
 * 版本：1.0.0
 * 说明：
 *
 **/
namespace Qwadmin\Controller;

use Think\Controller;
use Think\Model;

class MobileController extends Controller
{

    public function index()
    {
        $data = array();
        $model = M("em_contentmanager");
        $list = $model->where("status=1")
            ->order("createtime desc")
            ->select();
        $count = count($list);
        for ($i = 0; $i < $count; $i ++) {
            
            $model = M("em_notice");
            $note = $model->where(array(
                "contentid" => $list[$i]['id'],
                "stauts" => 1
            ))
                ->order("createtime desc")
                ->limit(3)
                ->select();
            if (! $note)
                $note = array();
            array_push($data, $note);
        }
        $this->assign("list", $list);
        $this->assign("data", $data);
        $this->display("home");
    }

    public function notice()
    {
        $id = I("get.id");
        if (strlen($id) > 0) {
            $model = M("em_notice");
            $list = $model->where(array(
                "contentid" => $id,
                "stauts" => 1
            ))
                ->order("createtime desc")
                ->select();
            $this->assign("list", $list);
            $this->display("notices");
        }
    }

    public function profile()
    {
        $id = I("get.id");
        if (strlen($id) > 0) {
            $model = M("Member");
            $user = $model->where(array(
                'openid' => $id
            ))->find();
            
            // 住户信息
            $house = new Model();
            $sql = "SELECT `c`.`FLOOR`,`c`.`UNIT`,`f`.`UNIT_NAME`,a.tel,`d`.`VILLAGE_NAME`,a.HOUSEHOLD_NAME,`a`.`WECHAT_ACCOUNT`,`e`.`BUILDING_NAME`,`c`.`HOUSE_NAME` FROM qw_em_household a LEFT JOIN qw_em_house_household b ON a.HOUSEHOLD_ID = b.HOUSEHOLD_ID LEFT JOIN qw_em_house c ON c.HOUSE_ID = b.HOUSE_ID LEFT JOIN qw_em_village d ON d.VILLAGE_ID = c.village LEFT JOIN qw_em_building e ON e.building_id = c.building LEFT JOIN qw_em_unit f ON f.unit_id = c.unit where a.tel='{$user['phone']}'";
            $voList = $house->query($sql);
            
            // 按小区分组
            $name = "";
            $phone = "";
            $wechat = "";
            $villages = array();
            $count = count($voList);
            for ($i = 0; $i < $count; $i ++) {
                $villageName = $voList[$i]['village_name'];
                $name = $voList[$i]['household_name'];
                $phone = $voList[$i]['tel'];
                $wechat = $voList[$i]['wechat_account'];
                if (in_array($villageName, $villages))
                    continue;
                array_push($villages, $villageName);
            }
            $this->assign('name', $name);
            $this->assign('phone', $phone);
            $this->assign('wechat', $wechat);
            $this->assign('user', $user);
            $this->assign('list', $voList);
            $this->assign('villages', $villages);
            $this->display("profile");
        }
    }

    public function detail()
    {
        $id = I("get.id");
        if (strlen($id) > 0) {
            $model = M("em_notice");
            $notice = $model->where(array(
                'id' => $id
            ))->find();
            if ($notice) {
                $createtime = date('Y-m-d', strtotime($notice['createtime']));
                $this->assign("createtime", $createtime);
                $this->assign("noticetitle", $notice['noticetitle']);
                $this->assign("noticepicture", $notice['noticepicture']);
                $this->assign("noticecontent", htmlspecialchars_decode($notice['noticecontent']));
            }
            $this->display("details");
        }
    }

    // 手机端显示图文消息
    public function news_detail()
    {
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $m = M('em_news');
            $condition['id'] = $id;
            $model = $m->where('id=' . $id)->find();
            $this->assign('model', $model);
            $this->display();
        } else {
            $this->error('没有找到任何图文素材信息');
        }
    }
}