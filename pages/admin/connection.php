<div class="wrap c-myposeo__holder">
	<h1><img height="35" src="<?php echo MYPOSEO_URL;?>assets/images/logo_full.png" /><?php _e('plugin', 'myposeo' );?></h1>
<?php
	$ok=false;
	$new=true;
	if(isset($_POST['action']))
		check_admin_referer( 'register' );

	if(isset($_POST['action']) && (sanitize_text_field($_POST['action'])=="Delete" || sanitize_text_field($_POST['action'])=="Effacer"))
	{
		echo '<div class="error">'.__('API key deleted', 'myposeo' ).'</div>';
		update_option("myposeo_apikey","");
		$apikey="";
	}
	else if(isset($_POST['apikey']))
	{
		$apikey=$_POST['apikey'];
		update_option("myposeo_apikey",$apikey);
		echo '<div class="updated">'.__('API key registered, thank you !', 'myposeo' ).'</div>';
		$new=false;
	}
	else
	{
		$apikey=get_option("myposeo_apikey");
		if($apikey!="")
			$new=false;
	}
	if(!$new)
	{
		if(isset($_POST['idCampaign']))
		{
			$idCampaign=sanitize_text_field($_POST['idCampaign']);
			update_option("myposeo_idCampaign",$idCampaign);
			echo '<script>window.location.href = \'admin.php?page=myposeo_dashboard\';</script>';

		}
		else
		{
			$idCampaign=get_option("myposeo_idCampaign");
		}

		$url="http://api.myposeo.com/1.0/d/tracking/api/campaign?key=";

		$result=wp_remote_get($url.$apikey);
		if($result!="")
		{
			$result=json_decode($result['body']);
			if($result->state==-1)
			{
				echo '<div class="error">'.__('Your API key seems to be wrong, please check it', 'myposeo' ).'</div>';
				$ok=false;
			}else
				$ok=true;
		}
		else
		{
			echo '<div class="error">'.__('Your API key seems to be wrong, please check it', 'myposeo' ).'</div>';
			$ok=false;
		}
	}

	?>
	<div class="myposeo-intro">
		<div class="bloc-left">
			<div class="intro">
				<p><?php _e('This plugin made by Myposeo let you follow your positioning evolution of your posts through search engines. This plugin needs to subscribe to a Premium licence, and an active project on your SEO dashboard with keywords mentioned', 'myposeo' );?></p>
			</div>

			<div class="card card-config">
				<h2><?php _e("How to setup your plugin ?", 'myposeo' );?></h2>
				<ol>
					<li><?php _e('Copy your API key available on the API page from your', 'myposeo' );?>&nbsp;<a href="https://account.myposeo.com" target="_blank"><?php _e('Myposeo account', 'myposeo' );?></a></li>
					<li><?php _e('Paste your key in the field below', 'myposeo' );?></li>
					<li><?php _e('Select a campaign which will be used as default campaign on your SEO monitoring page', 'myposeo' );?></li>
				</ol>
			</div>
		</div>

		<div class="bloc-right">
			<div class="card card-help">
			<h3><?php _e("Needs help ?", 'myposeo' );?></h3>
			<p><?php _e("We provide resources to help you step by step through this plugin configuration", 'myposeo' );?></p>
			<ul>
				<li><a href="https://help.myposeo.com/hc/fr/categories/200851851-Suivi-SEO" target="_blank"><?php _e('Knowledge database', 'myposeo' );?></a></li>
			</ul>
			<p><?php _e("Any issue accessing datas ? An advanced issue and no answer in our knowledge database ? Please open a", 'myposeo' );?>&nbsp;<a href="https://account.myposeo.com/account/support" target="_blank"><?php _e('ticket on Myposeo support page', 'myposeo' );?></a></p>
		</div>
		</div>
	</div>

	<form id="your-profile" action="#" method="POST" novalidate="novalidate">
		<input type="hidden" name="_wp_http_referer" value="/wp-admin/profile.php?wp_http_referer=%2Fwp-admin%2Fusers.php" />
		<?php wp_nonce_field( 'register' );?>
		<input type="hidden" name="from" value="profile" />
		<input type="hidden" name="checkuser_id" value="1" />
		<h2><?php _e('Myposeo Plugin settings', 'myposeo' );?></h2>
		<table class="form-table api-key">
			<tr class="user-rich-editing-wrap">
				<th scope="row"><?php _e("API key","myposeo"); ?></th>
<?php if($ok) {?>
				<td><input name="apikey" type="text" readonly id="apikey" value="<?=$apikey?>" size="60" /><input type="submit" name="action" value="<?php _e('Delete', 'myposeo' );?>" /></td>
<?php } else { ?>
				<td><input name="apikey" type="text" id="apikey" value="" size="60" /><input type="submit" name="action" value="OK" />
					<br><?php _e('The API key which is used to connect to your account', 'myposeo' );?>&nbsp;<a href="https://account.myposeo.com/account/configuration/api" target="_blank"><?php _e('Recover your API key here', 'myposeo' );?></a>
				</td>
<?php } ?>
			</tr>
		</table>
<?php if($ok) {?>
		<h2><?php _e("Select a campaign", 'myposeo' );?></h2>
		<table class="form-table campaign">
			<tr class="user-rich-editing-wrap">
				<th scope="row"><?php _e("Campaign","myposeo");?></th>
				<td>
					<select id="idCampaign" name="idCampaign">
						<option>--<?php _e('Select a campaign', 'myposeo' );?>--</option>
						<?php	foreach($result->data as $elem)
							{
								if($idCampaign!="" && $elem->id==$idCampaign)
									echo '<option selected value="'.$elem->id.'">'.$elem->id.' - '.$elem->name.'</option>\n';
								else
									echo '<option value="'.$elem->id.'">'.$elem->id.' - '.$elem->name.'</option>\n';
							}
						?>
					</select>
					<input type="submit" value="<?php _e('Validate', 'myposeo' );?>" />
				</td>
			</tr>
		</table>
<?php } ?>
	</form>

</div>
