<?php $theme->display ('header'); ?>
<!-- page.single -->
  <div class="page content">
   <div id="primary">
    <div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?>">

     <div class="entry-head">
      <h3 class="entry-title"><?php _e('Page:'); ?> <a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h3>
      <small class="entry-meta">
       <span class="chronodata"><abbr class="published"><?php echo $post->pubdate_out; ?></abbr></span> by <?php echo $post->author->displayname; ?> 
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
<?php $theme->display ( 'comments' ); ?>
   </div>

   <hr>

   <div class="secondary">

<?php $theme->display ('sidebar'); ?>

   </div>

   <div class="clear"></div>
  </div>
<!-- /page.single -->
<?php $theme->display ( 'footer' ); ?>
