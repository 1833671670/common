<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>
<div class='fui-page  fui-page-current'>
    <div class="fui-header">
        <div class="fui-header-left">
            <a class="back"></a>
        </div>
        <div class="title">我的团队
        </div>
        <div class="fui-header-right">&nbsp;</div>
    </div>
    <div class="fui-content">
        <div class="fui-list-group address-item" style=''
             data-addressid="">
            <?php  if(is_array($member)) { foreach($member as $mem) { ?>
            <div  class="fui-list" >
                <div class="fui-list-inner">
                    <p style="display: inline-block; font-weight: bold; font-size: 0.7rem;">用户姓名：<span style="font-weight: normal"><?php  if(($mem['realname'] == '')) { ?> <?php  echo $mem['nickname'];?> <span style="color: #696a6e; font-size: 12px;"> (未填写姓名)</span><?php  } else { ?> <?php  echo $mem['realname'];?><?php  } ?></span></p>
                    <p style="font-weight: bold; font-size: 0.7rem;">用户电话：<span style="font-weight: normal"> <?php  echo $mem['mobile'];?></span></p>
                    <p style="display: inline-block; font-weight: bold; font-size: 0.7rem;">团队人数：<span style="font-weight: normal"> <?php  echo $mem['num'];?></span></p>
                    <a style="float: right; background: #0A8CD2; padding: 5px; color: white; border-radius: 3px;" href="<?php  echo mobileUrl('member/team',array('id'=>$mem['id']))?>">查看团队</a>
                </div>
            </div>
            <?php  } } ?>
        </div>
    </div>

</div>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>