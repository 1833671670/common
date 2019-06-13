<?php  if( !defined("IN_IA") )
{
    exit( "Access Denied" );
}
class Op_EweiShopV2Page extends MobileLoginPage
{
    public function cancel()
    {
        global $_W;
        global $_GPC;
        $orderid = intval($_GPC["id"]);
        $order = pdo_fetch("select id,ordersn,openid,status,deductcredit,deductcredit2,deductprice,couponid,isparent,`virtual`,`virtual_info`,merchid from " . tablename("ewei_shop_order") . " where id=:id and uniacid=:uniacid and openid=:openid limit 1", array( ":id" => $orderid, ":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"] ));
        if( empty($order) )
        {
            show_json(0, "订单未找到");
        }
        if( 0 < $order["status"] )
        {
            show_json(0, "订单已支付，不能取消!");
        }
        if( $order["status"] < 0 )
        {
            show_json(0, "订单已经取消!");
        }
        if( !empty($order["virtual"]) && $order["virtual"] != 0 )
        {
            $goodsid = pdo_fetch("SELECT goodsid FROM " . tablename("ewei_shop_order_goods") . " WHERE uniacid = " . $_W["uniacid"] . " AND orderid = " . $order["id"]);
            $typeid = $order["virtual"];
            $vkdata = ltrim($order["virtual_info"], "[");
            $vkdata = rtrim($vkdata, "]");
            $arr = explode("}", $vkdata);
            foreach( $arr as $k => $v )
            {
                if( !$v )
                {
                    unset($arr[$k]);
                }
            }
            $vkeynum = count($arr);
            pdo_query("update " . tablename("ewei_shop_virtual_data") . " set openid=\"\",usetime=0,orderid=0,ordersn=\"\",price=0,merchid=" . $order["merchid"] . " where typeid=" . intval($typeid) . " and orderid = " . $order["id"]);
            pdo_query("update " . tablename("ewei_shop_virtual_type") . " set usedata=usedata-" . $vkeynum . " where id=" . intval($typeid));
        }
        m("order")->setStocksAndCredits($orderid, 2);
        if( 0 < $order["deductprice"] )
        {
            m("member")->setCredit($order["openid"], "credit1", $order["deductcredit"], array( "0", $_W["shopset"]["shop"]["name"] . "购物返还抵扣积分 积分: " . $order["deductcredit"] . " 抵扣金额: " . $order["deductprice"] . " 订单号: " . $order["ordersn"] ));
        }
        m("order")->setDeductCredit2($order);
        if( com("coupon") && !empty($order["couponid"]) )
        {
            $plugincoupon = com("coupon");
            if( $plugincoupon )
            {
                $coupondata = $plugincoupon->getCouponByDataID($order["couponid"]);
                if( $coupondata["used"] != 1 )
                {
                    com("coupon")->returnConsumeCoupon($orderid);
                }
            }
        }
        pdo_update("ewei_shop_order", array( "status" => -1, "canceltime" => time(), "closereason" => trim($_GPC["remark"]) ), array( "id" => $order["id"], "uniacid" => $_W["uniacid"] ));
        if( !empty($order["isparent"]) )
        {
            pdo_update("ewei_shop_order", array( "status" => -1, "canceltime" => time(), "closereason" => trim($_GPC["remark"]) ), array( "parentid" => $order["id"], "uniacid" => $_W["uniacid"] ));
        }
        m("notice")->sendOrderMessage($orderid);
        show_json(1);
    }

    public function finish()
    {
        global $_W;
        global $_GPC;
        $orderid = intval($_GPC["id"]);
        $order = pdo_fetch("select id,status,address,openid,couponid,price,refundstate,refundid,ordersn,price from " . tablename("ewei_shop_order") . " where id=:id and uniacid=:uniacid and openid=:openid limit 1", array( ":id" => $orderid, ":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"] ));

        $item = pdo_get('ewei_shop_order_goods',array('orderid'=>$orderid,'uniacid'=>$_W['uniacid']),array('goodsid','total'));
        $item1 = pdo_get('ewei_shop_goods',array('id'=>$item['goodsid']),array('good_inte','wholesale','promotion'));
        $finalinte = $item1['good_inte'] * $item['total']; //积分
        $member = m("member")->getMember($_W["openid"], true);

        $good_inte = $finalinte + $member['credit1'];
        $grade = m('share')->getMemberGrade($member['fid']);
        //分享奖佣金
        $shareCommission = m("share")->shareCommission($grade['grade'],$orderid);
        //管理奖佣金
        $manageCommission= m("share")->manageCommission($grade['grade'],$orderid);

        if( empty($order) )
        {
            show_json(0, "订单未找到");
        }
        if( $order["status"] != 2 )
        {
            show_json(0, "订单不能确认收货");
        }
        if( 0 < $order["refundstate"] && !empty($order["refundid"]) )
        {
            $change_refund = array( );
            $change_refund["status"] = -2;
            $change_refund["refundtime"] = time();
            pdo_update("ewei_shop_order_refund", $change_refund, array( "id" => $order["refundid"], "uniacid" => $_W["uniacid"] ));
        }
        //修改订单的信息，完成订单
        pdo_update("ewei_shop_order", array( "status" => 3, "finishtime" => time(), "refundstate" => 0 ), array( "id" => $order["id"], "uniacid" => $_W["uniacid"] ));
        //修改积分
        pdo_update("ewei_shop_member",array("credit1"=>$good_inte),array('id'=>$member['id']));
        //进货区额外奖励推荐人10%

        //address保存的数据为反序列化，使用unserialize()函数进行序列化操作
        $pca = unserialize($order['address']);
        $province = $pca['province']; //订单省
        $city = $pca['city'];//订单市
        $area = $pca['area'];//订单区
        $cr = pdo_get("ewei_shop_member",array('id'=>$member['fid']),array('credit2'));
        if(!$item1['wholesale']) {
            $pagency = pdo_get("agency_area", array('address' => $province), array('id', 'proportion'));
            if ($pagency) {
                $ppid = pdo_get("ewei_shop_member", array('is_agency' => $pagency['id']), array('id'));
                pdo_insert("ewei_shop_commission_list", array('uid' => $member['id'], 'getcomid' => $ppid['id'], 'commission' => $order['price'] * 0.02, 'status' => 7, 'create_time' => time()));
                $credit = $cr['credit2'] + $order['price'] * 0.02;
                pdo_update("ewei_shop_member", array('credit2' => $credit), array('id' => $ppid['id']));
            }
            $cagency = pdo_get("agency_area", array('address' => $city), array('id', 'proportion'));
            if ($cagency) {
                $pcid = pdo_get("ewei_shop_member", array('is_agency' => $cagency['id']), array('id'));
                pdo_insert("ewei_shop_commission_list", array('uid' => $member['id'], 'getcomid' => $pcid['id'], 'commission' => $order['price'] * 0.03, 'status' => 7, 'create_time' => time()));
                $credit = $cr['credit2'] + $order['price'] * 0.03;
                pdo_update("ewei_shop_member", array('credit2' => $credit), array('id' => $pcid['id']));
            }
            $aagency = pdo_get("agency_area", array('address' => $area), array('id', 'proportion'));
            if ($aagency) {
                $paid = pdo_get("ewei_shop_member", array('is_agency' => $aagency['id']), array('id'));
                pdo_insert("ewei_shop_commission_list", array('uid' => $member['id'], 'getcomid' => $paid['id'], 'commission' => $order['price'] * 0.05, 'status' => 7, 'create_time' => time()));
                $credit = $cr['credit2'] + $order['price'] * 0.05;
                pdo_update("ewei_shop_member", array('credit2' => $credit), array('id' => $paid['id']));
            }
            if($member['fid'] != 0){
                pdo_insert("ewei_shop_commission_list",array('uid'=>$member['id'],'getcomid'=>$member['fid'],'commission'=>$order['price'] * 0.1,'status'=>6,'create_time'=>time()));
                $credit = $cr['credit2'] + $order['price'] * 0.1;
                pdo_update("ewei_shop_member",array('credit2'=>$credit),array('id'=>$member['fid']));
            }
        } else {
            $pagency = pdo_get("agency_area",array('address'=>$province),array('id','proportion'));
            if($pagency){
                $ppid = pdo_get("ewei_shop_member",array('is_agency'=>$pagency['id']),array('id'));
                pdo_insert("ewei_shop_commission_list",array('uid'=>$member['id'],'getcomid'=>$ppid['id'],'commission'=>$order['price'] * $pagency['proportion'] * 0.01,'status'=>7,'create_time'=>time()));
                $credit = $cr['credit2'] + $order['price'] * $pagency['proportion']* 0.01;
                pdo_update("ewei_shop_member",array('credit2'=>$credit),array('id'=>$ppid['id']));
            }
            $cagency = pdo_get("agency_area",array('address'=>$city),array('id','proportion'));
            if($cagency){
                $pcid = pdo_get("ewei_shop_member",array('is_agency'=>$cagency['id']),array('id'));
                pdo_insert("ewei_shop_commission_list",array('uid'=>$member['id'],'getcomid'=>$pcid['id'],'commission'=>$order['price'] * $cagency['proportion']* 0.01,'status'=>7,'create_time'=>time()));
                $credit = $cr['credit2'] + $order['price'] * $cagency['proportion']* 0.01;
                pdo_update("ewei_shop_member",array('credit2'=>$credit),array('id'=>$pcid['id']));
            }
            $aagency = pdo_get("agency_area",array('address'=>$area),array('id','proportion'));
            if($aagency){
                $paid = pdo_get("ewei_shop_member",array('is_agency'=>$aagency['id']),array('id'));
                pdo_insert("ewei_shop_commission_list",array('uid'=>$member['id'],'getcomid'=>$paid['id'],'commission'=>$order['price'] * $aagency['proportion']* 0.01,'status'=>7,'create_time'=>time()));
                $credit = $cr['credit2'] + $order['price'] * $aagency['proportion']* 0.01;
                pdo_update("ewei_shop_member",array('credit2'=>$credit),array('id'=>$paid['id']));
            }
            if($member['fid'] != 0){
                pdo_insert("ewei_shop_commission_list",array('uid'=>$member['id'],'getcomid'=>$member['fid'],'commission'=>$order['price'] * 0.1,'status'=>6,'create_time'=>time()));
                $credit = $cr['credit2'] + $order['price'] * 0.1;
                pdo_update("ewei_shop_member",array('credit2'=>$credit),array('id'=>$member['fid']));
            }
        }
        //促销去额外奖励推荐人10%
        if($item1['promotion']){
            if($member['fid'] != 0){
                $cr = pdo_get("ewei_shop_member",array('id'=>$member['fid']),array('credit2','is_league'));
                pdo_insert("ewei_shop_commission_list",array('uid'=>$member['id'],'getcomid'=>$member['fid'],'commission'=>$order['price'] * 0.1,'status'=>6,'create_time'=>time()));
                if($cr['is_league']){
                    $credit = $cr['credit2'] + $order['price'] * 0.2;
                }else{
                    $credit = $cr['credit2'] + $order['price'] * 0.1;
                }
                pdo_update("ewei_shop_member",array('credit2'=>$credit),array('id'=>$member['fid']));
            }
        }
        //添加佣金记录
        //分享
        if($shareCommission){
            if($member['fid'] != 0){
                //添加到佣金记录表中
                pdo_insert("ewei_shop_commission_list",array('uid'=>$member['id'],'getcomid'=>$member['fid'],'commission'=>$shareCommission,'status'=>1,'create_time'=>time()));
                //修改member表中佣金数据
                $cr = pdo_get("ewei_shop_member",array('id'=>$member['fid']),array('credit2'));
                $credit = $cr['credit2'] + $shareCommission;
                pdo_update("ewei_shop_member",array('credit2'=>$credit),array('id'=>$member['fid']));
            }
        }
        //管理2代
        if($manageCommission['grade2Commission']){
            //添加佣金记录
            $uid = pdo_get("ewei_shop_member",array('id'=>$member['fid']),array('fid'));
            pdo_insert("ewei_shop_commission_list",array('uid'=>$member['id'],'getcomid'=>$uid['fid'],'commission'=>$manageCommission['grade2Commission'],'status'=>2,'create_time'=>time()));
            //修改member表中佣金数据
            $cr = pdo_get("ewei_shop_member",array('id'=>$uid['fid']),array('credit2'));
            $credit = $cr['credit2'] + $manageCommission['grade2Commission'];
            pdo_update("ewei_shop_member",array('credit2'=>$credit),array('id'=>$uid['fid']));
        }
        //管理3代
        if($manageCommission['grade3Commission']){
            //添加佣金记录
            $fuid = pdo_get("ewei_shop_member",array('id'=>$member['fid']),array('fid'));
            $u3id = pdo_get("ewei_shop_member",array('id'=>$fuid['fid']),array('fid'));
            pdo_insert("ewei_shop_commission_list",array('uid'=>$member['id'],'getcomid'=>$u3id['fid'],'commission'=>$manageCommission['grade3Commission'],'status'=>2,'create_time'=>time()));
            //修改member表中佣金数据
            $cr = pdo_get("ewei_shop_member",array('id'=>$u3id['fid']),array('credit2'));
            $credit = $cr['credit2'] + $manageCommission['grade3Commission'];
            pdo_update("ewei_shop_member",array('credit2'=>$credit),array('id'=>$u3id['fid']));
        }

        m("order")->setStocksAndCredits($orderid, 3);
        m("order")->fullback($orderid);
        m("member")->upgradeLevel($order["openid"], $orderid);
        m("order")->setGiveBalance($orderid, 1);
        if( com("coupon") )
        {
            $refurnid = com("coupon")->sendcouponsbytask($orderid);
        }
        if( com("coupon") && !empty($order["couponid"]) )
        {
            com("coupon")->backConsumeCoupon($orderid);
        }
        m("notice")->sendOrderMessage($orderid);
        com_run("printer::sendOrderMessage", $orderid);
        if( p("lineup") )
        {
            p("lineup")->checkOrder($order);
        }
        if( p("commission") )
        {
            p("commission")->checkOrderFinish($orderid);
        }
        if( p("lottery") )
        {
            $res = p("lottery")->getLottery($_W["openid"], 1, array( "money" => $order["price"], "paytype" => 2 ));
            if( $res )
            {
                p("lottery")->getLotteryList($_W["openid"], array( "lottery_id" => $res ));
            }
        }
        if( p("task") )
        {
            p("task")->checkTaskProgress($order["price"], "order_full", "", $order["openid"]);
        }
        show_json(1, array( "url" => mobileUrl("order", array( "status" => 3 )) ));
    }
    public function delete()
    {
        global $_W;
        global $_GPC;
        $orderid = intval($_GPC["id"]);
        $userdeleted = intval($_GPC["userdeleted"]);
        $order = pdo_fetch("select id,status,refundstate,refundid from " . tablename("ewei_shop_order") . " where id=:id and uniacid=:uniacid and openid=:openid limit 1", array( ":id" => $orderid, ":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"] ));
        if( empty($order) )
        {
            show_json(0, "订单未找到!");
        }
        if( $userdeleted == 0 )
        {
            if( $order["status"] != 3 )
            {
                show_json(0, "无法恢复");
            }
        }
        else
        {
            if( $order["status"] != 3 && $order["status"] != -1 )
            {
                show_json(0, "无法删除");
            }
            if( 0 < $order["refundstate"] && !empty($order["refundid"]) )
            {
                $change_refund = array( );
                $change_refund["status"] = -2;
                $change_refund["refundtime"] = time();
                pdo_update("ewei_shop_order_refund", $change_refund, array( "id" => $order["refundid"], "uniacid" => $_W["uniacid"] ));
            }
        }
        pdo_update("ewei_shop_order", array( "userdeleted" => $userdeleted, "refundstate" => 0 ), array( "id" => $order["id"], "uniacid" => $_W["uniacid"] ));
        show_json(1);
    }
}
?>