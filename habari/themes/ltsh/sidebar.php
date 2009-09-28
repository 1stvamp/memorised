<?php
/**
 * Return a unix timestamp for next "first Friday of the month", e.g.
 * either the first Friday of the current month or if that date has already passed
 * the first friday of the next month
 * @return int unix timestamp
 */
function getNextFirstFriday() {
        $firstFridayThisMonth = strtotime('first friday', mktime(0, 0, 0, date('n'), 0, date('Y')));
        if (time() < strtotime('7pm', $firstFridayThisMonth)) {
                return $firstFridayThisMonth;
        }
        return strtotime('first friday', strtotime(date('Y-m-0', strtotime('next month'))));
}
?>
<!-- sidebar -->
<?php Plugins::act( 'theme_sidebar_top' ); ?>

    <div id="search">
     <h2><?php _e('Search'); ?></h2>
<?php $theme->display ('searchform' ); ?>
    </div>	
 
    <div class="sb-about">
    	<div>
		Next meeting will be:<br/>
		<?php echo date('l jS F Y \@ 7\p\m', getNextFirstFriday()); ?><br/>
		<a href="http://www.brewerytapleeds.co.uk/">The Brewery Tap, Leeds</a>
	</div>
	<!--<div id="map_canvas" style="width: 200px; height: 200px"></div>-->
    	<ul>
		<lh>Nav</lh>
                <li><a href="<?php Site::out_url( 'habari' ); ?>"><?php _e('Home'); ?></a></li>
                <?php
                // List Pages
                foreach ( $pages as $page ) {
                        echo '<li><a href="' . $page->permalink . '" title="' . $page->title . '">' . $page->title . '</a></li>' . "\n";
                }
                ?>
	</ul>
	<ul>
		<lh>Shouts</lh>
		<li><a href="http://www.2600.com/">2600</a></li>
		<li><a href="http://geekup.org/">GeekUp</a></li>
		<li><a href="http://hackspace.org.uk//">Hackspace Foundation</a></li>
	</ul>
    </div>
    <div class="sb-user">
     <h2><?php _e('User'); ?></h2>
<?php $theme->display ( 'loginform' ); ?>
    </div>	
    
<?php Plugins::act( 'theme_sidebar_bottom' ); ?>
<!-- /sidebar -->
