<?php if (!defined("IN_IA")) {
	exit("Access Denied");
}

class Money_EweiShopV2Page extends MobileLoginPage
{
	/**
	 * 模版
	 */
	public function main()
	{
		include($this->template());
	}

	/**
	 * 资金明细
	 */
	public function details()
	{
		global $_W;
		global $_GPC;
		$status = $_GPC['status'];
		$member = pdo_get('ewei_shop_member', ['openid' => $_W['openid']]);
		$member_list = pdo_getall('ewei_shop_member');
		$member_list_arr = [];
		foreach ($member_list as $v) {
			$member_list_arr[$v['id']] = $v['mobile'];
		}
		// 奖金记录
		if ($status == 1) {
			$list = pdo_getall('ewei_shop_commission_list', ['getcomid' => $member['id']]);
			foreach ($list as &$v) {
				$v['uid'] = $member_list_arr[$v['uid']];
				$v['getcomid'] = $member_list_arr[$v['getcomid']];
				$v['create_time'] = date('Y-m-d', $v['create_time']);
			}
			dump($list);
		}
		// 转账记录
		if ($status == 2) {
			$list = pdo_getall('ewei_shop_member_transfer', ['transferor' => $member['id']]);
			foreach ($list as &$v) {
				$v['transferor'] = $member_list_arr[$v['transferor']];
				$v['payee'] = $member_list_arr[$v['payee']];
				$v['create_time'] = date('Y-m-d', $v['create_time']);
			}
		}
		exit(show_json(1, ['data' => $list]));
	}
}

?>