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
        $this->display("home");
    }
}