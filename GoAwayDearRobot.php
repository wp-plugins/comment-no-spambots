<?php
/*
Plugin name: Spambot Remover
Description: Stops spam by adding some hidden fields with javascript. This fields cannot be reproduced by bots, so you need only to get rid of humans that makes spam. 
Version: 0.0.1
Author: Ionut Stoica
*/

// This key is a security key for encoding data within javascript generated fields.
$key = 'akkkdhhsgguiie27ha67jjd0';


add_filter("plugin_action_links", "ant_links", 10, 2 );

function ant_links($links, $file){ 
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(dirname(__FILE__).'/plugin.php');
	
	if ($file == $this_plugin){
		$settings_link = '<a href="http://ionutstoica.info/" target="_blank">Visit my website</a>';
		array_unshift( $links, $settings_link); 
		
	}
	return $links;
}

add_filter( 'comment_form_defaults', 'change_comment_form_defaults');
function change_comment_form_defaults( $default ) {
    $commenter = wp_get_current_commenter();
	
	wp_enqueue_script('jquery');
    $default[ 'fields' ][ 'email' ] .= '
	<input type="hidden" name="botcheck" id="botcheck" value="'.md5($key . $_SERVER['REMOTE_ADDR']).'">
	<input type="hidden" name="botcheck2" value="'.time().'">
	<input type="hidden" name="botcheck3" id="botcheck3">
	<script>
	jQuery(\'#botcheck3\').val(jQuery(\'#botcheck\').val());
	</script>
	';
return $default;
}

add_filter( 'preprocess_comment', 'verify_comment_meta_data' );
function verify_comment_meta_data( $commentdata ) {
	if(!is_user_logged_in()){
		$i = "------------\n".serialize($commentdata)."\n";
		$F=fopen(dirname(__FILE__).'/log.txt', 'a+');
		fwrite($F, $i);
		fclose($F);
		
		if (empty($_POST['botcheck3']) || $_POST['botcheck3'] != $_POST['botcheck'])
			wp_die( __( 'Error: <br> EN: please enable JavaScript and don`t spam me anymore.<br>Thank you !<br><br> RO: Salutare botule, te rog fugi d`aici, nu am nevoie de comment-urile tale pline de spam.<br>Daca nu esti robot activeaza JavaScript in browser si revino.<br> Multumesc!' ) );
	}
return $commentdata;
}
?>