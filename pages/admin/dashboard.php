<div class="wrap c-myposeo__holder">
	<h1><img height="35" src="<?php echo MYPOSEO_URL;?>assets/images/logo_full.png" /><?php _e("SEO Dashboard", 'myposeo' );?></h1>
<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	if(isset($_POST['action']))
		check_admin_referer( 'search' );

	function myposeo_date_convert($date)
	{
		if(get_bloginfo("language")!="fr-FR")
			return $date;
		$datetime=DateTime::createFromFormat( "Y-m-d H:i:s", $date);
		if($datetime=="")
			return $date;
		return $datetime->format("d/m/Y h:i:s");
	}
	function myposeo_search_engine($engine_id)
	{
		$engines=$GLOBALS['engines'];
		foreach($engines as $engine)
		{
			if($engine->engine_id==$engine_id)
				return $engine;
		}
		return null;
	}
	function myposeo_search_location($engine,$location_id)
	{
		foreach($engine->locations as $location)
		{
			if($location->location_id==$location_id)
			{
				return $location;
			}
		}
		return null;
	}
	function myposeo_search_device($device_id)
	{
		if($device_id==1)
			return __("Desktop","myposeo");
		if($device_id==2)
			return __("Mobile","myposeo");
		return "-";
	}

	$ok=true;
	$mainurl="http://api.myposeo.com/1.0/d";
	$apikey=get_option("myposeo_apikey");
	$idCampaign=get_option("myposeo_idCampaign");
	if(isset($_POST['website_id']))
		$website_id=sanitize_text_field($_POST['website_id']);
	if(isset($_POST['engine_id']))
		$engine_id=sanitize_text_field($_POST['engine_id']);
	if(isset($_POST['device_id']))
		$device_id=sanitize_text_field($_POST['device_id']);
	if(isset($_POST['location_id']))
		$location_id=sanitize_text_field($_POST['location_id']);
	if(isset($_POST['start_date']) && $_POST['start_date']!="")
		$start_date=sanitize_text_field($_POST['start_date']);
	else
	{
		$date=new DateTime();
		$date->sub(new DateInterval('P30D'));
		$start_date=$date->format("Y-m-d");

	}
	if(isset($_POST['end_date']) && sanitize_text_field($_POST['end_date'])!="")
		$end_date=sanitize_text_field($_POST['end_date']);
	else
	{
		$date=new DateTime();
		$end_date=$date->format("Y-m-d");

	}
	if($apikey=="")
	{
		echo '<div class="update-nag bsf-update-nag error">'.__("Please fill a valid API key to access to your datas", 'myposeo' ).' - <a href="admin.php?page=myposeo_connexion">'.__('MyPoseo Settings', 'myposeo' ).'</a></div>';
		$ok=false;
	}
	else if($idCampaign=="")
	{
		echo '<div class="update-nag bsf-update-nag error">'.__("Please select a campaign to access to your datas", 'myposeo' ).' - <a href="admin.php?page=myposeo_connexion">'.__('MyPoseo Settings', 'myposeo' ).'</a></div>';
		$ok=false;
	}
	else
	{
		// get campaign datas
		$url=$mainurl."/tracking/api/campaign/";
		$fullURL=$url.$idCampaign."?key=".$apikey;
		$result=wp_remote_get($fullURL);
		if($result!="")
		{
			$result=json_decode($result['body']);
			$campaign=$result->data;
			$ok=true;
		}
		else
		{
			echo '<div class="update-nag bsf-update-nag error">'.__("Connection failed, please check your API code", 'myposeo' ).' - <a href="admin.php?page=myposeo_connexion">'.__('Myposeo Settings', 'myposeo' ).'</a></div>';
			$ok=false;
		}

		if($ok)
		{
			// get engine list
			$url=$mainurl."/service/api/engine/list";
			$fullURL=$url."?key=".$apikey;
			$result=wp_remote_get($fullURL);
			if($result!="")
			{
				$result=json_decode($result['body']);
				$engines=array();
				foreach($result->data as $engine)
				{
					// get engine list
					$url=$mainurl."/service/api/engine/location-list?engineId=".$engine->engine_id;
					$fullURL=$url."&key=".$apikey;
					$result=wp_remote_get($fullURL);
					if($result!="")
					{
						$result=$result['body'];
						$result=json_decode($result);
						$engine->locations=$result->data;
						$engines[]=$engine;
					}

				}
				$GLOBALS['engines']=$engines;
			}
		}
	}

	// everything is OK, let's generate the table
	if($ok)
	{
	?>
	<h2><?php _e("Campaign","myposeo");?> <?=$campaign->name?> (id : <?=$campaign->id?>)</h2>
	<form action="#" method="POST">
		<?php wp_nonce_field( 'search' );?>
		<input type="hidden" name="action" value="search" />
		<ul class="c-dashboard__thumbs">
			<li><strong><?php _e('Created on', 'myposeo' );?>&nbsp;</strong><?=myposeo_date_convert($campaign->created_at)?></li>
			<li><strong><?php _e('Updated on', 'myposeo' );?>&nbsp;</strong><?=myposeo_date_convert($campaign->updated_at)?></li>
			<li><strong><?php _e('Websites', 'myposeo' );?> : </strong>
				<select name="website_id" onchange="this.form.submit()">
					<?php foreach($campaign->sites as $site) {
						if($website_id=="" && $site->is_main==1) $website_id=$site->id;
						?>
						<option value="<?=$site->id?>" <?php if($site->id==$website_id) {$website=$site;echo "selected";} ?>><?=$site->name?></option>
					<?php } ?>
				</select>
			</li>
		</ul>
	</form>
	<?php if($website!="")
	{
		// get ranking datas
		$url=$mainurl."/tracking/api/ranking/all";
		$fullURL=$url."?campaignId=$idCampaign&siteId=".$website->id."&start=$start_date&end=$end_date&mode=day&key=$apikey";
		$result=wp_remote_get($fullURL);
		if($result!="")
		{
			$result=json_decode($result['body']);
			$rankings=$result->data;
		}
	?>
	<h2><?php _e("Keywords for website","myposeo");?>&nbsp;<a href="<?=$website->url?>" target="_blank"><?=$website->name?></a></h2>

	<div>
		<form action="#" method="POST">
			<?php wp_nonce_field( 'search' );?>
			<input type="hidden" name="action" value="search" />
			<div class="c-form__dates">
				<div class="c-form__date">
					<span><?php _e("Start date","myposeo");?> : </span><input type="text" name="start_date" value="<?=$start_date?>" id="datepicker_start" class="datepickers" onchange="this.form.submit()">
				</div>
				<div class="c-form__date">
					<span><?php _e("End date","myposeo");?> : </span><input type="text" name="end_date" value="<?=$end_date?>" id="datepicker_end" class="datepickers" onchange="this.form.submit()">
				</div>
			</div>
			<select name="engine_id" onchange="this.form.submit()">
				<option value="">--<?php _e("Choose an engine","myposeo");?>--</option>
				<?php foreach($engines as $current_engine) {
					if($current_engine->engine_id==$engine_id)
						$default_engine=$current_engine;
					?>
					<option value="<?=$current_engine->engine_id?>" <?php if($current_engine->engine_id==$engine_id) echo "selected"; ?>><?=$current_engine->name?></option>
				<?php } ?>
			</select>
			<select name="device_id" onchange="this.form.submit()">
				<option value="">--<?php _e("Choose a device","myposeo");?>--</option>
				<option value="1" <?php if($device_id==1) echo "selected"; ?>>Desktop</option>
				<option value="2" <?php if($device_id==2) echo "selected"; ?>>Mobile</option>
			</select>
			<select name="location_id" onchange="this.form.submit()">
				<option value="">--<?php _e("Choose a location","myposeo");?>--</option>
				<?php
				if($default_engine!="")
				{
					$locations=$default_engine->locations;
				}
				else
				{
					$engine=myposeo_search_engine(2); // Google
					$locations=$engine->locations;
				}
				foreach($locations as $location) {
					?>
					<option value="<?=$location->location_id?>" <?php if($location->location_id==$location_id) echo "selected"; ?>><?=$location->country_name?> (<?=$location->language_name?>)</option>
				<?php } ?>
			</select>
		</form>
	</div>
	<div class="c-table__wrapper">
		<table id="motscles" class="display" style="width:100%">
	        <thead>
	            <tr>
	                <th><?php _e('Keyword', 'myposeo' );?></th>
	                <th><?php _e('URL', 'myposeo' );?></th>
	                <th><?php _e('Position start date', 'myposeo' );?></th>
	                <th><?php _e('Country', 'myposeo' );?></th>
	                <th><?php _e('Position end date', 'myposeo' );?></th>
	                <th><?php _e('Device', 'myposeo' );?></th>
	                <th><?php _e('Volume', 'myposeo' );?></th>
	                <th><?php _e('Traffic', 'myposeo' );?></th>
	                <th><?php _e('Search engine', 'myposeo' );?></th>
	            </tr>
	        </thead>
	        <tbody>
			<?php
				foreach($rankings as $ranking)
				{
					$ok=true;
					if($engine_id!="" && $ranking->engine_id!=$engine_id)
						$ok=false;
					if($device_id!="" && $ranking->device_id!=$device_id)
						$ok=false;
					if($location_id!="" && $ranking->location_id!=$location_id)
						$ok=false;
					if($ok)
					{
						$website_id=$website->id;
						$ranking_site_start=$ranking->sites->$website_id->dates->$start_date;
						$ranking_site_end=$ranking->sites->$website_id->dates->$end_date;
						if($default_engine!="")
						{
							$engine=$default_engine;
							$location=myposeo_search_location($default_engine,$ranking->location_id);
						}
						else
						{
							$engine=myposeo_search_engine($ranking->engine_id);
							$location=myposeo_search_location($engine,$ranking->location_id);
						}
						if($ranking_site_end!="")
						{
							if($ranking_site_end->position<$ranking_site_start->position)
								$arrow='<span class="dashicons dashicons-arrow-up" style="color:green;"></span>';
							else if($ranking_site_end->position>$ranking_site_start->position)
								$arrow='<span class="dashicons dashicons-arrow-down" style="color:red;"></span>';
							else
								$arrow='<span class="dashicons"></span>';
							echo "<tr>";
							echo "<td>".$ranking->name."</td>";
							echo '<td><a target="_blank" href="'.$ranking_site_end->url.'">'.substr($ranking_site_end->url,0,100).'</a></td>';
							echo "<td>".$ranking_site_start->position."</td>";
							echo "<td>".$location->country_name."</td>";
							echo "<td>".$arrow."&nbsp;".$ranking_site_end->position."</td>";
							echo "<td>".myposeo_search_device($ranking->device_id)."</td>";
							echo "<td>".$ranking_site_end->volume."</td>";
							echo "<td>".$ranking_site_end->traffic."</td>";
							echo "<td>".$engine->name."&nbsp;".$location->tld."</td>";
							echo "</tr>\n";
						}
					}
				}

			?>
	        </tbody>
	    </table>
    </div>
	<?php } ?>
</div>
<script>
	jQuery(document).ready(function() {
		jQuery('#motscles').dataTable( {
			 "pageLength": 50,
<?php if(get_bloginfo("language")=="fr-FR") {?>
	        "language":
			{
				"processing":     "Traitement en cours...",
				"from": 		  "Depuis",
				"to": 		  "Jusqu'à",
				"search":         "Rechercher&nbsp;:",
			    "lengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
				"info":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
				"infoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
				"infoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
				"infoPostFix":    "",
				"loadingRecords": "Chargement en cours...",
			    "zeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
				"emptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
				"paginate": {
					"first":      "Premier",
					"previous":   "Pr&eacute;c&eacute;dent",
					"next":       "Suivant",
					"last":       "Dernier"
				},
				"aria": {
					"sortAscending":  ": activer pour trier la colonne par ordre croissant",
					"sortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
				},
				"select": {
			        	"rows": {
			         		_: "%d lignes sélectionnées",
			         		0: "Aucune ligne sélectionnée",
			        		1: "1 ligne sélectionnée"
			        	}
				}
			}
<?php } ?>
	    } )
	.yadcf([
	{
        column_number: 2,
<?php if(get_bloginfo("language")=="fr-FR") {?>
        filter_default_label: ["de", "à"],
<?php } ?>
        filter_type: "range_number"
    },
	{
        column_number: 4,
<?php if(get_bloginfo("language")=="fr-FR") {?>
        filter_default_label: ["de", "à"],
<?php } ?>
        filter_type: "range_number"
    },
	{
        column_number: 6,
<?php if(get_bloginfo("language")=="fr-FR") {?>
        filter_default_label: ["de", "à"],
<?php } ?>
        filter_type: "range_number"
    },
	{
        column_number: 7,
<?php if(get_bloginfo("language")=="fr-FR") {?>
        filter_default_label: ["de", "à"],
<?php } ?>
        filter_type: "range_number"
    },
/*    {
        column_number: 6,
        select_type: 'chosen',
<?php if(get_bloginfo("language")=="fr-FR") {?>
        filter_default_label: "Sélectionnez",
<?php } ?>
        select_type_options: {
            search_contains: false
        }
    },*/
    ]);

} );

    jQuery(document).ready(function($) {
        $("#datepicker_start").datepicker();
        $("#datepicker_end").datepicker();

        $(".datepickers").datepicker( "option", "showAnim", "slideDown");
        $(".datepickers").datepicker( "option", "dateFormat", "yy-mm-dd");

        $("#datepicker_start").datepicker('setDate', '<?=$start_date?>');
        $("#datepicker_end").datepicker('setDate', '<?=$end_date?>');
    });
</script>
<?php } ?>
