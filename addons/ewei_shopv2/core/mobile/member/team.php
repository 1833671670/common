<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Team_EweiShopV2Page extends MobileLoginPage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $member = pdo_getall('ewei_shop_member',array('fid'=>$_GPC['id']),array('id','mobile','realname','inviter','nickname'));
        foreach ($member as $k=>$v){
            $member[$k]['num'] = count(m('share')->digui($v['id']));
        }
        include $this->template();
    }
}