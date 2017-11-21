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
            $sql = "select * from qw_em_village v,qw_em_household d,qw_em_house h,qw_em_building b,qw_em_unit u where d.VILLAGE=VILLAGE_ID and HOUSE_ID=d.HOUSE and h.unit=unit_id and h.building=building_id and TEL='{$user['phone']}'";
            $voList = $house->query($sql);
            
            $this->assign('user', $user);
            $this->assign('list', $voList);
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