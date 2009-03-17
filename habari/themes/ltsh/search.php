<?php $theme->display ( 'header' ); ?>
<!-- search -->
  <div class="content">
   <div id="primary">
    <div id="primarycontent" class="hfeed">
     <h2><?php _e('Search results for'); ?> <?php echo htmlspecialchars( $criteria ); ?></h2>
<?php foreach ( $posts as $post ) { ?>
     <div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?>">

      <div class="entry-head">
       <h3 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h3>
       <small class="entry-meta">
        <span class="chronodata"><abbr class="published"><?php echo $post->pubdate_out; ?></abbr></span><?php if ( $show_author ) { ?> by <?php echo $post->author->displayname; ?> <?php } ?>
        <span class="commentslink"><a href="<?php echo $post->permalink; ?>#comments" title="<?php _e('Comments to this post'); ?>"><?php echo $post->comments->approved->count; ?>
		<?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></span>
<?php if ( $user ) { ?>
        <span class="entry-edit"><a href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>" title="<?php _e('Edit post'); ?>"><?php _e('Edit'); ?></a></span>
<?php } ?>
<?php if ( is_array( $post->tags ) ) { ?>
        <span class="entry-tags"><?php echo $post->tags_out; ?></span>
<?php } ?>
       </small>
      </div>

      <div class="entry-content">
       <?php echo $post->content_out; ?>

      </div>

     </div>
<?php } ?>
    </div>

    <div id="page-selector">
     <?php $theme->prev_page_link(); ?> <?php $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php $theme->next_page_link(); ?>

    </div>

   </div>

   <hr>

   <div class="secondary">

<?php $theme->display ( 'sidebar' ); ?>

   </div>

   <div class="clear"></div>
  </div>
<!-- /search -->
<?php $theme->display ( 'footer' ); ?>
