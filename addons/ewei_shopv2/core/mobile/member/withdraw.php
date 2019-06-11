<?php if (!defined("IN_IA")) {
	exit("Access Denied");
}

class Withdraw_EweiShopV2Page extends MobileLoginPage
{
	/**
	 * 模版
	 */
	public function main()
	{
		include($this->template());
	}

	/**
	 * 提现
	 */
	public function withdraw()
	{
		global $_W;
		global $_GPC;
		$price = $_GPC['price'];
		if (!is_numeric($price)) exit(show_json(0, ['msg' => '参数不合法']));
		$member = pdo_get('ewei_shop_member', ['openid' => $_W['openid']]);
		if ($price > $member['credit2']) exit(show_json(0, ['msg' => '提现金额不可大于余额']));
		$res = pdo_insert('ewei_shop_withdraw', [
			'uid' => $member['id'],
			'price' => $price,
			'status' => 1,
			'create_time' => time()
		]);
		if (!$res) exit(show_json(0, ['msg' => '提现失败']));
		exit(show_json(1, ['msg' => '提现成功，请等待管理员审核']));
	}
}

?>