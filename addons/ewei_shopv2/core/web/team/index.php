<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

class Index_EweiShopV2Page extends WebPage
{
    public function main()
    {
        $member = pdo_getall('ewei_shop_member', array(), array('id', 'fid', 'openid'));

        $member = pdo_getall('ewei_shop_member', array('inviter' => 0), array('id', 'fid', 'openid'));
        $today = date('Y-m-d', time());
        $tomorrow = date('Y-m-d', strtotime("+1 day", time()));
        $today0 = strtotime($today);
        $tomorrow0 = strtotime($tomorrow);

        foreach ($member as $k => $v) {
            $formance[$v['id']] = $this->getTeamPerformance($v['id'], $v['openid']);
            $my_Performance = pdo_getall("ewei_shop_order", array('openid' => $v['openid'], 'status' => 3, "paytime <" => $tomorrow0, 'paytime >' => $today0), array('price'));
            $sum_performance = pdo_getall("ewei_shop_order", array('openid' => $v['openid'], 'status' => 3), array('price'));
            if (count($my_Performance) > 0) {
                foreach ($my_Performance as $k1 => $v2) {
                    $member[$k]['order_total'] += $v2['price'];
                }
                if ($member[$k]['order_total'] >= 30000 && $member[$k]['order_total'] < 60000) {
                    $member[$k]['ratio'] = 2;
                }
                if ($member[$k]['order_total'] >= 60000 && $member[$k]['order_total'] < 120000) {
                    $member[$k]['ratio'] = 3;
                }
                if ($member[$k]['order_total'] >= 120000 && $member[$k]['order_total'] < 300000) {
                    $member[$k]['ratio'] = 4;
                }
                if ($member[$k]['order_total'] >= 300000 && $member[$k]['order_total'] < 600000) {
                    $member[$k]['ratio'] = 5;
                }
                if ($member[$k]['order_total'] >= 600000 && $member[$k]['order_total'] < 1200000) {
                    $member[$k]['ratio'] = 6;
                }
                if ($member[$k]['order_total'] >= 1200000 && $member[$k]['order_total'] < 3000000) {
                    $member[$k]['ratio'] = 7;
                }
                if ($member[$k]['order_total'] >= 3000000 && $member[$k]['order_total'] < 6000000) {
                    $member[$k]['ratio'] = 9;
                }
                if ($member[$k]['order_total'] >= 6000000 && $member[$k]['order_total'] < 12000000) {
                    $member[$k]['ratio'] = 11;
                }
                if ($member[$k]['order_total'] >= 1200000) {
                    $member[$k]['ratio'] = 13;
                }
            } else {
                $member[$k]['order_total'] = 0;
                $member[$k]['ratio'] = 0;
            }
            if (count($sum_performance) > 0) {
                foreach ($sum_performance as $k1 => $v2) {
                    $member[$k]['sum_performance'] += $v2['price'];
                }
            } else {
                $member[$k]['sum_performance'] = 0;
            }
        }
        $member = $this->getTeamAward($member);

        foreach ($member as $k => $val) {
            if ($val['fid'] != 0) {
                $arr[] = $val;
            } else {
                if ($val['lower_ratio']){
                    
                }
                if ($val['ratio']){
                    $a = ($val['ratio'] * $val['group_money'])/100;
                }
                $arr2[] = $val;
            }
        }


        include $this->template();
    }


    protected function getTeamAward($arr = array())
    {
        foreach ($arr as $k => $val) {
            if (!isset($tmp[$val['fid']])) {
                $tmp[$val['fid']][$val['id']] = array('id' => $val['id'], 'fid' => $val['fid'], 'order_total' => $val['order_total'],);
            } else {
                $tmp[$val['fid']][$val['id']] = array('id' => $val['id'], 'fid' => $val['fid'], 'order_total' => $val['order_total'],);
            }
        }
        // 计算无下级 团队中的用户总业绩 以及极差
        foreach ($tmp as $k => $v) {
            foreach ($v as $key => $value) {
                $tmp[$k]['group_money'] += $value['order_total'];
            }
            if ($tmp[$k]['group_money'] >= 30000 && $tmp[$k]['group_money'] < 60000) {
                $tmp[$k]['ratio'] = 2;
            }
            if ($tmp[$k]['group_money'] >= 60000 && $tmp[$k]['group_money'] < 120000) {
                $tmp[$k]['ratio'] = 3;
            }
            if ($tmp[$k]['group_money'] >= 120000 && $tmp[$k]['group_money'] < 300000) {
                $tmp[$k]['ratio'] = 4;
            }
            if ($tmp[$k]['group_money'] >= 300000 && $tmp[$k]['group_money'] < 600000) {
                $tmp[$k]['ratio'] = 5;
            }
            if ($tmp[$k]['group_money'] >= 600000 && $tmp[$k]['group_money'] < 1200000) {
                $tmp[$k]['ratio'] = 6;
            }
            if ($tmp[$k]['group_money'] >= 1200000 && $tmp[$k]['group_money'] < 3000000) {
                $tmp[$k]['ratio'] = 7;
            }
            if ($tmp[$k]['group_money'] >= 3000000 && $tmp[$k]['group_money'] < 6000000) {
                $tmp[$k]['ratio'] = 9;
            }
            if ($tmp[$k]['group_money'] >= 6000000 && $tmp[$k]['group_money'] < 12000000) {
                $tmp[$k]['ratio'] = 11;
            }
            if ($tmp[$k]['group_money'] >= 1200000) {
                $tmp[$k]['ratio'] = 13;
            }
        }
        foreach ($tmp as $k => $v) {
            if ($v['ratio']) {
                $tmp[$k]['brokerage'] = ($v['ratio'] * $v['group_money']) / 100;
            } else {
                $tmp[$k]['brokerage'] = 0;
            }
        }
        $today = date('Y-m-d', time());
        $tomorrow = date('Y-m-d', strtotime("+1 day", time()));
        $today0 = strtotime($today);
        $tomorrow0 = strtotime($tomorrow);

        $member = pdo_getall('ewei_shop_member', array('inviter >' => 0), array('id', 'fid', 'openid'));
        $order_total = 0;
        foreach ($member as $k => $v) {
            $my_Performance = pdo_getall("ewei_shop_order", array('openid' => $v['openid'], 'status' => 3, "paytime <" => $tomorrow0, 'paytime >' => $today0), array('price'));
            foreach ($my_Performance as $key => $value) {
                $order_total += $value['price'];
            }
            if (count($tmp[$v['id']]) > 0) {
                $member[$k]['performance'] = $order_total;
                $member[$k]['group_money'] = $order_total + $tmp[$v['id']]['group_money'];
                $member[$k]['lower_ratio'] = $tmp[$v['id']]['ratio'];
                if ($member[$k]['group_money'] >= 30000 && $member[$k]['group_money'] < 60000) {
                    $member[$k]['ratio'] = 2;
                }
                if ($member[$k]['group_money'] >= 60000 && $member[$k]['group_money'] < 120000) {
                    $member[$k]['ratio'] = 3;
                }
                if ($member[$k]['group_money'] >= 120000 && $member[$k]['group_money'] < 300000) {
                    $member[$k]['ratio'] = 4;
                }
                if ($member[$k]['group_money'] >= 300000 && $member[$k]['group_money'] < 600000) {
                    $member[$k]['ratio'] = 5;
                }
                if ($member[$k]['group_money'] >= 600000 && $member[$k]['group_money'] < 1200000) {
                    $member[$k]['ratio'] = 6;
                }
                if ($member[$k]['group_money'] >= 1200000 && $member[$k]['group_money'] < 3000000) {
                    $member[$k]['ratio'] = 7;
                }
                if ($member[$k]['group_money'] >= 3000000 && $member[$k]['group_money'] < 6000000) {
                    $member[$k]['ratio'] = 9;
                }
                if ($member[$k]['group_money'] >= 6000000 && $member[$k]['group_money'] < 12000000) {
                    $member[$k]['ratio'] = 11;
                }
                if ($member[$k]['group_money'] >= 1200000) {
                    $member[$k]['ratio'] = 13;
                }
            }
        }

        return $member;
    }


    //获取用户所属团队订单的总额
    public function getTeamPerformance($uid, $openid, $status = 1)
    {
        if ($status == 1) {
            static $TeamPerformance;
            $TeamPerformance = 0;
        }
        static $TeamPerformance = 0;
        $my_Performance = pdo_getall('ewei_shop_order', array('openid' => $openid, 'status' => 3), array('price'));
        foreach ($my_Performance as $k1 => $v2) {
            $TeamPerformance += $v2['price'];
        }
        $subordinate = pdo_getall('ewei_shop_member', array('fid' => $uid), array('id', 'openid'));
        foreach ($subordinate as $k => $v) {
            $price = pdo_getall('ewei_shop_order', array('openid' => $v['openid'], 'status' => 3), array('price'));
            foreach ($price as $k2 => $v3) {
                $TeamPerformance += $v3['price'];
            }
            $aa = pdo_getall('ewei_shop_member', array('fid' => $v['id']), array('id', 'openid'));
            if (count($aa) > 0) {
                foreach ($aa as $key => $value) {
                    $this->getTeamPerformance($value['id'], $value['openid'], $status = 0);
                }
            }
        }
        return $TeamPerformance;
    }
}