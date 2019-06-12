<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Agency_EweiShopV2Page extends WebPage
{
    public function main()
    {

        include $this->template();
    }

    public function add()
    {
        $member = pdo_getall('ewei_shop_member', array('is_agency' => 0), array('id', 'nickname'));
        $agency = pdo_getall('agency_area');
        include $this->template();
    }

    public function post()
    {
        
    }
}