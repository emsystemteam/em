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

class MobileController extends Controller
{

    public function index()
    {
        $model = M("em_contentmanager");
        $list = $model->where("status=1")
            ->order("createtime desc")
            ->select();
        $this->assign("list", $list);
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
            $this->assign('user', $user);
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
                $this->assign("noticetitle", $notice['noticetitle']);
                $this->assign("noticepicture", $notice['noticepicture']);
                $this->assign("noticecontent", $notice['noticecontent']);
            }
            $this->display("details");
        }
    }
}