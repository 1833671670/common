<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Transfer_EweiShopV2Page extends WebPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		$start_page = $_GPC['page'];
		$pindex = max(1, intval($_GPC["page"]));
		$psize = 20;
		$total = count(pdo_getall('ewei_shop_member_transfer'));
		$list = pdo_getall('ewei_shop_member_transfer', '', '', '', '', [$start_page, 20]);
		$member_list = pdo_getall('ewei_shop_member');
		$mobile = [];
		foreach ($member_list as $v) {
			$mobile[$v['id']] = $v['mobile'];
		}
		$pager = pagination2($total, $pindex, $psize);
		include $this->template();
	}
}