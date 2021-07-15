<?php
/* Template Name: Affiliate page */
get_header();
?>

<div id="primary" class="content-area">


<div class="referal_page">
	<?php esc_html_e('رابط التسجيل الخاص بك', 'wordpress') ?>
	<div class="referal_code">
		<?php 
			$id = get_current_user_id()*7;
		?>
    	<?php echo get_site_url()."?ref=".$id; ?>
    </div>
    <br>
    <hr>

</div>






</div>

<?php get_footer(); ?>