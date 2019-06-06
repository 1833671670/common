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

    //获取上级fid；laval表示分销级别.0表示顶级分销人员，1是一级分销
    function getMemberLaval($fid,$laval=""){
        global $_W;
        $fid = pdo_get("ewei_shop_member",array('uid'=>$fid),array('fid'));
        $fid['laval'] = $laval;
        if($fid['fid'] != 0){
            $fid['laval'] += 1;
            $fid = $this->getFId($fid['fid']);
        }
        return $fid;
    }

    //获取用户父级的会员等级
    public  function getMemberFLevel(){
        global $_W;
        $member = m("member")->getMember($_W["openid"], true);
        $f_id = $this->getFId($member['fid']);
        return pdo_get("ewei_shop_member_level",array('id'=>$f_id['id']),array('level'));
    }

    //获取父级的id
    public  function getFId($fid){
        return pdo_get("ewei_shop_member",array('id'=>$fid),array('id'));
    }

    //获取父级的总消费金额
    public  function getFPriceAll($openid){
        $member = m("member")->getMember($openid, true);
        $f_id = $this->getFId($member['fid']);
        $memberPrice = pdo_getall("ewei_shop_order",array('openid'=>$f_id['id']),array('price'));
        $priceAll = 0;
        //循环计算推荐人的总金额
        foreach ($memberPrice as $v){
            $priceAll += $v['price'];
        }
        return $priceAll;
    }

    //分享奖佣金
    public function shareCommission($level = "",$orderid = ""){
        global $_W;
        $member = m("member")->getMember($_W["openid"], true);
        if($level == 0){
            return false;
        }else{
            $price = pdo_get("ewei_shop_order",array('id'=>$orderid),array('price'));
            $memberMLevel = pdo_get("ewei_shop_member_level",array('id'=>$member['level']),array('level'));
            //判断自身的消费金额和父级总消费金额的大小进行比较
            $priceAll = $this->getFPriceAll($_W['openid']); //获取父级消费总额
            if($memberMLevel <= $priceAll){
                $commission = $price['price'] * 0.4;
            }else{
                //如果自身消费金额比父级总消费金额大，佣金比例为父级总消费金额的40%
                $commission = $priceAll * 0.4;
            }
        }
    }

    //管理奖佣金
    public  function manageCommission($level = "",$orderid = ""){
        global $_W;
        $member = m("member")->getMember($_W["openid"], true);
        $price = pdo_get("ewei_shop_order",array('id'=>$orderid),array('price'));
        if($level <= 1){
            return false;
        }else if($level == 2){ //当存在二级分销的时候
            $priceAll = $this->getFPriceAll($_W['openid']); //获取父级消费总额
            if($price <= $priceAll){
                $commission = $price * 0.08;
            }else{
                $commission = $priceAll * 0.08;
            }
        }else if($level >= 3){
            $Fopenid = pdo_get("ewei_shop_member",array('id'=>$member['fid']),array('openid'));
            $FpriceAll = $this->getFPriceAll($Fopenid);
            if($price <= $FpriceAll){
                //获取会员等级
                $commission = $price * 0.08;
            }else{
                $commission = $FpriceAll * 0.08;
            }
        }
    }
}
?>