<?php

$_head_profile_attr = '';
if ( bb_is_profile() ) {
	global $self;
	if ( !$self ) {
		$_head_profile_attr = ' profile="http://www.w3.org/2006/03/hcard"';
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"<?php bb_language_attributes( '1.1' ); ?>>
<head<?php echo $_head_profile_attr; ?>>
	<meta http-equiv="X-UA-Compatible" content="IE=8" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php bb_title() ?></title>

	<?php bb_feed_head(); ?>
	<?php bb_head(); ?>

	<!-- WP Template Wrap -->

	<?php 
		global $wp_head; echo $wp_head; 
		header("HTTP/1.1 200 OK");
	?>
	<!-- End WP Template Wrap -->

	<?php if ( 'rtl' == bb_get_option( 'text_direction' ) ) : ?>
		<link rel="stylesheet" href="<?php bb_stylesheet_uri( 'rtl' ); ?>" type="text/css" />
	<?php endif; ?>


</head>
<body id="<?php bb_location(); ?>">

	<?php /* Survey Scripts */ ?>
	<script type="text/javascript">var _kiq = _kiq || [];</script>
	<script type="text/javascript" src="//s3.amazonaws.com/ki.js/35006/6NG.js" async="true"></script>
	<?php /* Survey Scripts */ ?>

	<div class="container">
		<div class="ocpsoft-toparea">
			<?php include '../wp-content/themes/ocpsoft.org/navbar.php';?>
		</div>
		<div class="ocpsoft-middlearea">
			<div class="ocpsoft-middlearea-shadow-top">
				<div class="ocpsoft-middlearea-shadow-bottom">
				<div style="padding: 25px;">
				<?php if ( !in_array( bb_get_location(), array( 'login-page', 'register-page' ) ) ) login_form(); ?>

