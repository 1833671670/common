<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Share_EweiShopV2Model 
{
    function getPid($pid){
        global $_W;
        return pdo_getcolumn('users', array('uid' => $pid), 'pid',1);
    }

    function getLaval($uid){
        global $_W;
        return pdo_getcolumn('users', array('uid' => $uid), 'laval',1);
    }

}
?>