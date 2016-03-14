<?php

if(!class_exists('WTBAjaxResponse')):

	/**
	*
	*/
	class WTBAjaxResponse
	{

		function __construct()
		{			
			add_action( 'wp_ajax_wtbShortCodeList', array($this, 'shortCodeList'));
		}

		function shortCodeList(){
			global $wtbInit;
			$html = null;
			$scQ = new WP_Query( array('post_type' => $wtbInit->post_type, 'order_by' => 'title', 'order' => 'DESC', 'post_status' => 'publish', 'posts_per_page' => -1) );
			if ( $scQ->have_posts() ) {

				$html .= "<div class='mce-container mce-form'>";
				$html .= "<div class='mce-container-body'>";
				$html .= '<label class="mce-widget mce-label" style="padding: 20px;font-weight: bold;" for="wtb_scid">Select Shortcode</label>';
				$html .= "<select name='id' id='wtb_scid' style='width: 150px;margin: 15px;border: 1px solid #ddd;'>";
				$html .= "<option value=''>Default</option>";
					while ( $scQ->have_posts() ) {
						$scQ->the_post();
						$html .="<option value='".get_the_ID()."'>".get_the_title()."</option>";
					}
				$html .= "</select>";
				$html .= "</div>";
				$html .= "</div>";
			}else{
				$html .= "<div>No short code fount.</div>";
			}
			echo $html;
			die();
		}
	}


endif;
