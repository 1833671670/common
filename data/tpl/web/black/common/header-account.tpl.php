<?php defined('IN_IA') or exit('Access Denied');?><div class="text-center"><img src="<?php  echo tomedia('headimg_'.$_W['account']['acid'].'.jpg')?>?time=<?php  echo time()?>" class="head-logo"></div>
<div class="text-center account-name"><?php  echo $_W['account']['name'];?></div>

<?php  if($_W['account']['type'] == ACCOUNT_TYPE_OFFCIAL_NORMAL || $_W['account']['type'] == ACCOUNT_TYPE_OFFCIAL_AUTH) { ?>
	<div class="text-center">
		<?php  if($_W['account']['level'] == 1 || $_W['account']['level'] == 3) { ?>
			<span class="label label-primary">订阅号</span>
			<?php  if($_W['account']['level'] == 3) { ?>
				<span class="label label-primary">已认证</span>
			<?php  } ?>
		<?php  } ?>

		<?php  if($_W['account']['level'] == 2 || $_W['account']['level'] == 4) { ?>
			<span class="label label-primary">服务号</span>
			<?php  if($_W['account']['level'] == 4) { ?>
				<span class="label label-primary">已认证</span>
			<?php  } ?>
		<?php  } ?>

		<?php  if($_W['uniaccount']['isconnect'] == 0) { ?>
			<span class="label label-danger" data-toggle="popover">未接入</span>
			<script>
				$(function(){
					var url = "<?php  echo url('account/post', array('uniacid' => $_W['account']['uniacid'], 'acid' => $_W['acid']));?>";
					$('[data-toggle="popover"]').popover({
						trigger: 'manual',
						html: true,
						placement: 'bottom',
						content: '<i class="wi wi-warning-sign"></i>未接入微信公众号' +
								'<a href="' +
								url +
								'">立即接入</a>'
					}).on("mouseenter", function() {
						var _this = this;
						$(this).popover("show");
						$(this).siblings(".popover").on("mouseleave", function() {
							$(_this).popover('hide');
						});
					}).on("mouseleave", function() {
						var _this = this;
						setTimeout(function() {
							if(!$(".popover:hover").length) {
								$(_this).popover("hide")
							}
						}, 100);
					});
				});
			</script>
		<?php  } ?>
	</div>
	<div class="text-center operate">
		<a href="<?php  echo url('utility/emulator');?>" target="_blank">
			<i class="wi wi-iphone" data-toggle="tooltip" data-placement="bottom" title="模拟测试"></i>
		</a>
		<?php  if(uni_permission($_W['uid'], $_W['uniacid']) != ACCOUNT_MANAGE_NAME_OPERATOR) { ?>
			<a href="<?php  echo url('account/post', array('uniacid' => $_W['account']['uniacid'], 'acid' => $_W['acid'], 'account_type' => $_W['account']['type']))?>" data-toggle="tooltip" data-placement="bottom" title="公众号设置">
				<i class="wi wi-appsetting"></i>
			</a>
		<?php  } ?>
		<a href="<?php  echo url('account/display', array('type' => 'all'))?>"  data-toggle="tooltip" data-placement="bottom" title="切换平台">
			<i class="wi wi-changing-over"></i>
		</a>
	</div>

<?php  } else if($_W['account']['type'] == ACCOUNT_TYPE_XZAPP_NORMAL || $_W['account']['type'] == ACCOUNT_TYPE_XZAPP_AUTH) { ?>
	<div class="text-center">
		<?php  if($_W['account']['level'] == 1) { ?>
			<span class="label label-primary">个人</span>
		<?php  } else if($_W['account']['level'] == 2) { ?>
			<span class="label label-primary">媒体</span>
		<?php  } else if($_W['account']['level'] == 3) { ?>
			<span class="label label-primary">企业</span>
		<?php  } else if($_W['account']['level'] == 4) { ?>
			<span class="label label-primary">政府</span>
		<?php  } else if($_W['account']['level'] == 5) { ?>
			<span class="label label-primary">其他组织</span>
		<?php  } ?>

		<?php  if($_W['uniaccount']['isconnect'] == 0) { ?>
			<span class="label label-danger" data-toggle="popover">未接入</span>
			<script>
				$(function(){
					var url = "<?php  echo url('account/post', array('uniacid' => $_W['account']['uniacid'], 'acid' => $_W['acid'], 'account_type' => ACCOUNT_TYPE_XZAPP_NORMAL));?>";
					$('[data-toggle="popover"]').popover({
						trigger: 'manual',
						html: true,
						placement: 'bottom',
						content: '<i class="wi wi-warning-sign"></i>未接入熊掌号' +
						'<a href="' +
						url +
						'">立即接入</a>'
					}).on("mouseenter", function() {
						var _this = this;
						$(this).popover("show");
						$(this).siblings(".popover").on("mouseleave", function() {
							$(_this).popover('hide');
						});
					}).on("mouseleave", function() {
						var _this = this;
						setTimeout(function() {
							if(!$(".popover:hover").length) {
								$(_this).popover("hide")
							}
						}, 100);
					});
				});
			</script>
		<?php  } ?>
	</div>

	<div class="text-center operate">
		<?php  if(uni_permission($_W['uid'], $_W['uniacid']) != ACCOUNT_MANAGE_NAME_OPERATOR) { ?>
		<a href="<?php  echo url('account/post', array('uniacid' => $_W['account']['uniacid'], 'acid' => $_W['acid'], 'account_type' => ACCOUNT_TYPE_XZAPP_NORMAL))?>" title="熊掌号设置">
			<i class="wi wi-appsetting"></i>
		</a>
		<?php  } ?>

		<a href="<?php  echo url('account/display', array('type' => 'all'))?>"  data-toggle="tooltip" data-placement="bottom" title="切换平台">
			<i class="wi wi-changing-over"></i>
		</a>
	</div>

<?php  } ?>