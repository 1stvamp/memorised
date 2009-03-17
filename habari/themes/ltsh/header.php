<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
 <title><?php if($request->display_entry && isset($post)) { echo "{$post->title} - "; } ?><?php Options::out( 'title' ) ?></title>
 <meta http-equiv="Content-Type" content="text/html">
 <meta name="generator" content="Habari">

 <link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>">
 <link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
 <link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">

 <link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">
<!--<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=true_or_false&amp;key=ABQIAAAAuxUjeKv-IUJNfB3eK2rMahQpdlBhsOQKz-FXR1zyVt1cxPOgHxSG_FPVgZ0I3PU3894DqpM2ky1WxQ" type="text/javascript">-->
</script>
<script>
// <![CDATA[
	/*
	document.observe('dom:load', function() {
		if (GBrowserIsCompatible()) {
			var map = new GMap2($('map_canvas'));
			var mapPoint = new GLatLng(53.796849, -1.544867);
			map.setCenter(mapPoint, 15)
			map.addControl(new GSmallMapControl());
			map.addControl(new GMapTypeControl());
			var mapMarker = new GMarker(mapPoint);
			map.addOverlay(mapMarker);
			var markerHtml = "<strong>The Brewery Taps</strong>, Leeds<br/>18-24 New Station St<br/>Leeds, West Yorkshire, LS1 5DL<br/><br/>Get Directions<br/>0113 243 4414";
			mapMarker.openInfoWindowHtml(markerHtml);
		}
	});
	*/
// ]]>
</script>



<?php $theme->header(); ?>
</head>

<body class="home">
 <div id="page">
  <div id="header">
	<div id="banner">
		<h1><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?></a></h1>
	</div>
	
	<div id="sub_banner"><?php Options::out( 'tagline' ); ?></div>

   <ul class="menu">
    <li><a href="<?php Site::out_url( 'habari' ); ?>" title="<?php Options::out( 'title' ); ?>"><?php echo $home_tab; ?></a></li>
<?php
// Menu tabs
foreach ( $pages as $tab ) {
?>
    <li><a href="<?php echo $tab->permalink; ?>" title="<?php echo $tab->title; ?>"><?php echo $tab->title; ?></a></li>
<?php
}
if ( $user ) { ?>
    <li class="admintab"><a href="<?php Site::out_url( 'admin' ); ?>" title="<?php _e('Admin area'); ?>"><?php _e('Admin'); ?></a></li>
<?php } ?>
   </ul>

  </div>

  <hr>
<!-- /header -->
