<?php /* Template Name: Complete profile Page */
get_header();
?>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('#form_completeprofile .frm_button_submit').on('click',function(){
	    jQuery('#form_completeprofile .frm_submit').prepend('<h5>برجاء الانتظار دقيقه لإنشاء حسابك</h5>');
	});
});
</script>
<div id="primary" class="content-area">

<?php echo FrmFormsController::get_form_shortcode( array( 'id' => 8, 'title' => false, 'description' => false ) ); ?>

</div>

<?php get_footer(); ?>