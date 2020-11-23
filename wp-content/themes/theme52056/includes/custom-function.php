<?php
	// Loading child theme textdomain
	load_child_theme_textdomain( CURRENT_THEME, CHILD_DIR . '/languages' );

	add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_scripts' );
	function my_theme_enqueue_scripts() {
		wp_enqueue_script( 'device', get_stylesheet_directory_uri() . '/js/device.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'custom_script', get_stylesheet_directory_uri() . '/js/custom_script.js', array('jquery'), '1.0' );
	}	

	add_filter( 'cherry_slider_params', 'child_slider_params' );
    function child_slider_params( $params ) {
        $params['minHeight'] = '"114px"';
        $params['height'] = '"52.35714285714286%"';
    return $params;
    }

    add_action( 'after_setup_theme', 'after_cherry_child_setup' );
	function after_cherry_child_setup() {
		$nfu_options = get_option( 'nsu_form' );
		if ( !$nfu_options ) {
			$nfu_options_array = array();
			$nfu_options_array['email_label']         = 'Newsletter';
			$nfu_options_array['email_default_value'] = 'Enter Your E-mail...';
			$nfu_options_array['submit_button']       = 'sign up';
			update_option( 'nsu_form', $nfu_options_array );
		}
	}

	// Spacer
	if (!function_exists('spacer_shortcode')) {
		function spacer_shortcode($atts, $content = null) {
			extract(shortcode_atts(array(
				'custom_class'    => ''
			), $atts));

			return '<div class="spacer '.$custom_class.'"></div><!-- .spacer (end) -->';
		}
		add_shortcode('spacer', 'spacer_shortcode');
	}

    /**
	 * Post Grid
	 *
	 */
	if (!function_exists('posts_grid_shortcode')) {

		function posts_grid_shortcode($atts, $content = null) {
			extract(shortcode_atts(array(
				'type'            => 'post',
				'category'        => '',
				'custom_category' => '',
				'columns'         => '3',
				'rows'            => '3',
				'order_by'        => 'date',
				'order'           => 'DESC',
				'thumb_width'     => '370',
				'thumb_height'    => '250',
				'meta'            => '',
				'excerpt_count'   => '15',
				'link'            => 'yes',
				'link_text'       => __('Read more', CHERRY_PLUGIN_DOMAIN),
				'custom_class'    => ''
			), $atts));

			$spans = $columns;
			$rand  = rand();

			// columns
			switch ($spans) {
				case '1':
					$spans = 'span12';
					break;
				case '2':
					$spans = 'span6';
					break;
				case '3':
					$spans = 'span4';
					break;
				case '4':
					$spans = 'span3';
					break;
				case '6':
					$spans = 'span2';
					break;
			}

			// check what order by method user selected
			switch ($order_by) {
				case 'date':
					$order_by = 'post_date';
					break;
				case 'title':
					$order_by = 'title';
					break;
				case 'popular':
					$order_by = 'comment_count';
					break;
				case 'random':
					$order_by = 'rand';
					break;
			}

			// check what order method user selected (DESC or ASC)
			switch ($order) {
				case 'DESC':
					$order = 'DESC';
					break;
				case 'ASC':
					$order = 'ASC';
					break;
			}

			// show link after posts?
			switch ($link) {
				case 'yes':
					$link = true;
					break;
				case 'no':
					$link = false;
					break;
			}

				global $post;
				global $my_string_limit_words;

				$numb = $columns * $rows;

				// WPML filter
				$suppress_filters = get_option('suppress_filters');

				$args = array(
					'post_type'         => $type,
					'category_name'     => $category,
					$type . '_category' => $custom_category,
					'numberposts'       => $numb,
					'orderby'           => $order_by,
					'order'             => $order,
					'suppress_filters'  => $suppress_filters
				);

				$posts      = get_posts($args);
				$i          = 0;
				$count      = 1;
				$output_end = '';
				if ($numb > count($posts)) {
					$output_end = '</ul>';
				}

				$output = '<ul class="posts-grid row-fluid unstyled '. $custom_class .'">';

				foreach ( $posts as $j => $post ) {
					$post_id = $posts[$j]->ID;
					//Check if WPML is activated
					if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
						global $sitepress;

						$post_lang = $sitepress->get_language_for_element( $post_id, 'post_' . $type );
						$curr_lang = $sitepress->get_current_language();
						// Unset not translated posts
						if ( $post_lang != $curr_lang ) {
							unset( $posts[$j] );
						}
						// Post ID is different in a second language Solution
						if ( function_exists( 'icl_object_id' ) ) {
							$posts[$j] = get_post( icl_object_id( $posts[$j]->ID, $type, true ) );
						}
					}

					setup_postdata($posts[$j]);
					$excerpt        = get_the_excerpt();
					$attachment_url = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'full' );
					$url            = $attachment_url['0'];
					$image          = aq_resize($url, $thumb_width, $thumb_height, true);
					$mediaType      = get_post_meta($post_id, 'tz_portfolio_type', true);
					$prettyType     = 0;

					if ($count > $columns) {
						$count = 1;
						$output .= '<ul class="posts-grid row-fluid unstyled '. $custom_class .'">';
					}

					$output .= '<li class="'. $spans .'">';
						if(has_post_thumbnail($post_id) && $mediaType == 'Image') {

							$prettyType = 'prettyPhoto-'.$rand;

							$output .= '<figure class="featured-thumbnail thumbnail">';
							$output .= '<a href="'.get_permalink($post_id).'" title="'.get_the_title($post_id).'">';
							$output .= '<img  src="'.$image.'" alt="'.get_the_title($post_id).'" />';
							$output .= '</a></figure>';
							
						} elseif ($mediaType != 'Video' && $mediaType != 'Audio') {

							$thumbid = 0;
							$thumbid = get_post_thumbnail_id($post_id);

							$images = get_children( array(
								'orderby'        => 'menu_order',
								'order'          => 'ASC',
								'post_type'      => 'attachment',
								'post_parent'    => $post_id,
								'post_mime_type' => 'image',
								'post_status'    => null,
								'numberposts'    => -1
							) );

							if ( $images ) {

								$k = 0;
								//looping through the images
								foreach ( $images as $attachment_id => $attachment ) {
									$prettyType = "prettyPhoto-".$rand ."[gallery".$i."]";
									//if( $attachment->ID == $thumbid ) continue;

									$image_attributes = wp_get_attachment_image_src( $attachment_id, 'full' ); // returns an array
									$img = aq_resize( $image_attributes[0], $thumb_width, $thumb_height, true ); //resize & crop img
									$alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
									$image_title = $attachment->post_title;

									if ( $k == 0 ) {
										if (has_post_thumbnail($post_id)) {
											$output .= '<figure class="featured-thumbnail thumbnail">';
											$output .= '<a href="'.get_permalink($post_id).'" title="'.get_the_title($post_id).'">';
											$output .= '<img src="'.$image.'" alt="'.get_the_title($post_id).'" />';
										} else {
											$output .= '<figure class="featured-thumbnail thumbnail">';
											$output .= '<a href="'.get_permalink($post_id).'" title="'.get_the_title($post_id).'">';
											$output .= '<img  src="'.$img.'" alt="'.get_the_title($post_id).'" />';
										}
									} else {
										$output .= '<figure class="featured-thumbnail thumbnail" style="display:none;">';
										$output .= '<a href="'.get_permalink($post_id).'" title="'.get_the_title($post_id).'">';
									}
									$output .= '</a></figure>';
									$k++;
								}
							} elseif (has_post_thumbnail($post_id)) {
								$prettyType = 'prettyPhoto-'.$rand;
								$output .= '<figure class="featured-thumbnail thumbnail">';
								$output .= '<a href="'.get_permalink($post_id).'" title="'.get_the_title($post_id).'">';
								$output .= '<img  src="'.$image.'" alt="'.get_the_title($post_id).'" />';
								$output .= '</a></figure>';
							} elseif ($type=="team") {
								$url     = get_stylesheet_directory_uri().'/images/empty-avatar.gif';
								$image   = aq_resize($url, $thumb_width, $thumb_height, true);
								$output .= '<figure class="featured-thumbnail thumbnail">';
								$output .= '<img  src="'.$image.'" alt="'.get_the_title($post_id).'" />';
								$output .= '</figure>';
							}
						} else {

							// for Video and Audio post format - no lightbox
							$output .= '<figure class="featured-thumbnail thumbnail"><a href="'.get_permalink($post_id).'" title="'.get_the_title($post_id).'">';
							$output .= '<img  src="'.$image.'" alt="'.get_the_title($post_id).'" />';
							$output .= '</a></figure>';
						}

						$output .= '<div class="clear"></div>';

						if ($meta == 'yes') {
							// begin post meta
							if (has_post_thumbnail($post_id)) {
								$output .= '<div class="post_meta post_meta__alt">';
							} else {
								$output .= '<div class="post_meta">';
							}

								// post date
								$output .= '<span class="post_date">';
								$output .= '<time datetime="'.get_the_time('Y-m-d\TH:i:s', $post_id).'">' .get_the_time('j M'). '</time>';
								$output .= '</span>';
								
							$output .= '</div>';
							// end post meta
						}

						$output .= cherry_get_post_networks(array('post_id' => $post_id, 'display_title' => false, 'output_type' => 'return'));

						$output .= '<h5><a href="'.get_permalink($post_id).'" title="'.get_the_title($post_id).'">';
							$output .= get_the_title($post_id);
						$output .= '</a></h5>';						

						if($excerpt_count >= 1){
							$output .= '<p class="excerpt">';
								$output .= my_string_limit_words($excerpt,$excerpt_count);
							$output .= '</p>';
						}
						if($link){
							$output .= '<a href="'.get_permalink($post_id).'" class="btn btn-primary btn-normal" title="'.get_the_title($post_id).'">';
							$output .= $link_text;
							$output .= '</a>';
						}
						$output .= '</li>';
						if ($j == count($posts)-1) {
							$output .= $output_end;
						}
					if ($count % $columns == 0) {
						$output .= '</ul><!-- .posts-grid (end) -->';
					}
				$count++;
				$i++;

			} // end for
			wp_reset_postdata(); // restore the global $post variable

			return $output;
		}
		add_shortcode('posts_grid', 'posts_grid_shortcode');
	}

	/**
	 * Post Cycle
	 *
	 */
	if (!function_exists('shortcode_post_cycle')) {

		function shortcode_post_cycle($atts, $content = null) {
			extract(shortcode_atts(array(
					'num'              => '5',
					'type'             => 'post',
					'meta'             => '',
					'effect'           => 'slide',
					'thumb'            => 'true',
					'thumb_width'      => '200',
					'thumb_height'     => '180',
					'more_text_single' => '',
					'category'         => '',
					'custom_category'  => '',
					'excerpt_count'    => '15',
					'pagination'       => 'true',
					'navigation'       => 'true',
					'custom_class'     => ''
			), $atts));

			$type_post         = $type;
			$slider_pagination = $pagination;
			$slider_navigation = $navigation;
			$random            = gener_random(10);
			$i                 = 0;
			$rand              = rand();

			$output = '<script type="text/javascript">
							jQuery(window).load(function() {
								jQuery("#flexslider_'.$random.'").flexslider({
									animation: "'.$effect.'",
									smoothHeight : true,
									directionNav: '.$slider_navigation.',
									controlNav: '.$slider_pagination.'
								});
							});';
			$output .= '</script>';
			$output .= '<div id="flexslider_'.$random.'" class="flexslider no-bg '.$custom_class.'">';
				$output .= '<ul class="slides">';

				global $post;
				global $my_string_limit_words;

				// WPML filter
				$suppress_filters = get_option('suppress_filters');

				$args = array(
					'post_type'              => $type_post,
					'category_name'          => $category,
					$type_post . '_category' => $custom_category,
					'numberposts'            => $num,
					'orderby'                => 'post_date',
					'order'                  => 'DESC',
					'suppress_filters'       => $suppress_filters
				);

				$latest = get_posts($args);

				foreach($latest as $key => $post) {
					//Check if WPML is activated
					if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
						global $sitepress;

						$post_lang = $sitepress->get_language_for_element($post->ID, 'post_' . $type_post);
						$curr_lang = $sitepress->get_current_language();
						// Unset not translated posts
						if ( $post_lang != $curr_lang ) {
							unset( $latest[$key] );
						}
						// Post ID is different in a second language Solution
						if ( function_exists( 'icl_object_id' ) ) {
							$post = get_post( icl_object_id( $post->ID, $type_post, true ) );
						}
					}
					setup_postdata($post);
					$content        = get_the_content();
					$attachment_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
					$url            = $attachment_url['0'];
					$image          = aq_resize($url, $thumb_width, $thumb_height, true);

					$output .= '<li>';

						if ($thumb == 'true') {

							if ( has_post_thumbnail($post->ID) ){
								$output .= '<figure class="thumbnail featured-thumbnail"><a href="'.get_permalink($post->ID).'" title="'.get_the_title($post->ID).'">';
								$output .= '<img  src="'.$image.'" alt="'.get_the_title($post->ID).'" />';
								$output .= '</a></figure>';
							} else {

								$thumbid = 0;
								$thumbid = get_post_thumbnail_id($post->ID);

								$images = get_children( array(
									'orderby'        => 'menu_order',
									'order'          => 'ASC',
									'post_type'      => 'attachment',
									'post_parent'    => $post->ID,
									'post_mime_type' => 'image',
									'post_status'    => null,
									'numberposts'    => -1
								) );

								if ( $images ) {

									$k = 0;
									//looping through the images
									foreach ( $images as $attachment_id => $attachment ) {
										// $prettyType = "prettyPhoto-".$rand ."[gallery".$i."]";
										//if( $attachment->ID == $thumbid ) continue;

										$image_attributes = wp_get_attachment_image_src( $attachment_id, 'full' ); // returns an array
										$img = aq_resize( $image_attributes[0], $thumb_width, $thumb_height, true ); //resize & crop img
										$alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
										$image_title = $attachment->post_title;

										if ( $k == 0 ) {
											$output .= '<figure class="featured-thumbnail">';
											$output .= '<a href="'.get_permalink($post->ID).'" title="'.get_the_title($post->ID).'">';
											$output .= '<img  src="'.$img.'" alt="'.get_the_title($post->ID).'" />';
											$output .= '</a></figure>';
										} break;
										$k++;
									}
								}
							}
						}

						$output .= '<h5><a href="'.get_permalink($post->ID).'" title="'.get_the_title($post->ID).'">';
						$output .= get_the_title($post->ID);
						$output .= '</a></h5>';

						if($meta == 'true'){
							$output .= '<span class="meta">';
							$output .= '<span class="post-date">';
							$output .= get_the_date();
							$output .= '</span>';
							$output .= '<span class="post-comments">'.__('Comments', CHERRY_PLUGIN_DOMAIN).": ";
							$output .= '<a href="'.get_comments_link($post->ID).'">';
							$output .= get_comments_number($post->ID);
							$output .= '</a>';
							$output .= '</span>';
							$output .= '</span>';
						}
						//display post options
						$output .= '<div class="post_options">';
						switch($type_post) {
							case "team":
								$teampos  = (get_post_meta($post->ID, 'my_team_pos', true)) ? get_post_meta($post->ID, 'my_team_pos', true) : "";
								$teaminfo = (get_post_meta($post->ID, 'my_team_info', true)) ? get_post_meta($post->ID, 'my_team_info', true) : "";
								$output .= "<span class='page-desc'>".$teampos."</span><br><span class='team-content post-content'>".$teaminfo."</span>";
								$output .= cherry_get_post_networks(array('post_id' => $post->ID, 'display_title' => false, 'output_type' => 'return'));
								break;
							case "testi":
								$testiname = (get_post_meta($post->ID, 'my_testi_caption', true)) ? get_post_meta($post->ID, 'my_testi_caption', true) : "";
								$testiurl  = (get_post_meta($post->ID, 'my_testi_url', true)) ? get_post_meta($post->ID, 'my_testi_url', true) : "";
								$testiinfo = (get_post_meta($post->ID, 'my_testi_info', true)) ? get_post_meta($post->ID, 'my_testi_info', true) : "";
								$output .="<span class='user'>".$testiname."</span>, <span class='info'>".$testiinfo."</span><br><a href='".$testiurl."'>".$testiurl."</a>";
								break;
							case "portfolio":
								$portfolioClient = (get_post_meta($post->ID, 'tz_portfolio_client', true)) ? get_post_meta($post->ID, 'tz_portfolio_client', true) : "";
								$portfolioDate = (get_post_meta($post->ID, 'tz_portfolio_date', true)) ? get_post_meta($post->ID, 'tz_portfolio_date', true) : "";
								$portfolioInfo = (get_post_meta($post->ID, 'tz_portfolio_info', true)) ? get_post_meta($post->ID, 'tz_portfolio_info', true) : "";
								$portfolioURL = (get_post_meta($post->ID, 'tz_portfolio_url', true)) ? get_post_meta($post->ID, 'tz_portfolio_url', true) : "";
								$output .="<strong class='portfolio-meta-key'>".__('Client', CHERRY_PLUGIN_DOMAIN).": </strong><span> ".$portfolioClient."</span><br>";
								$output .="<strong class='portfolio-meta-key'>".__('Date', CHERRY_PLUGIN_DOMAIN).": </strong><span> ".$portfolioDate."</span><br>";
								$output .="<strong class='portfolio-meta-key'>".__('Info', CHERRY_PLUGIN_DOMAIN).": </strong><span> ".$portfolioInfo."</span><br>";
								$output .="<a href='".$portfolioURL."'>".__('Launch Project', CHERRY_PLUGIN_DOMAIN)."</a><br>";
								break;
							default:
								$output .="";
						};
						$output .= '</div>';

						if($excerpt_count >= 1){
							$output .= '<p class="excerpt">';
							$output .= my_string_limit_words($content,$excerpt_count);
							$output .= '</p>';
						}

						if($more_text_single!=""){
							$output .= '<a href="'.get_permalink($post->ID).'" class="btn btn-primary" title="'.get_the_title($post->ID).'">';
							$output .= $more_text_single;
							$output .= '</a>';
						}

					$output .= '</li>';
				}
				wp_reset_postdata(); // restore the global $post variable
				$output .= '</ul>';
			$output .= '</div>';
			return $output;
		}
		add_shortcode('post_cycle', 'shortcode_post_cycle');

	}

	/**
	 * Carousel Elastislide
	 */
	if ( !function_exists('shortcode_carousel') ) {
		function shortcode_carousel( $atts ) {
			extract( shortcode_atts( array(
				'title'            => '',
				'num'              => 8,
				'type'             => 'post',
				'thumb'            => 'true',
				'thumb_width'      => 220,
				'thumb_height'     => 180,
				'more_text_single' => '',
				'category'         => '',
				'custom_category'  => '',
				'excerpt_count'    => 12,
				'date'             => '',
				'author'           => '',
				'comments'         => '',
				'min_items'        => 3,
				'spacer'           => 18,
				'custom_class'     => ''
			), $atts) );

			// check what type of post user selected
			switch ( $type ) {
				case 'blog':
					$type = 'post';
					break;
				case 'testimonial':
					$type = 'testi';
					break;
			}

			$carousel_uniqid = uniqid();
			$thumb_width     = absint( $thumb_width );
			$thumb_height    = absint( $thumb_height );
			$excerpt_count   = absint( $excerpt_count );
			$i          	 = 0;
			$counter 		 = 1;
			$rand            = rand();

			$output = '<div class="carousel-wrap ' . $custom_class . '">';
				if ( !empty( $title{0} ) ) {
					$output .= '<h2>' . esc_html( $title ) . '</h2>';
				}
				$output .= '<div id="carousel-' . $carousel_uniqid . '" class="es-carousel-wrapper">';
				$output .= '<div class="es-carousel">';
					$output .= '<ul class="es-carousel_list unstyled clearfix">';

						// WPML filter
						$suppress_filters = get_option( 'suppress_filters' );

						$args = array(
							'post_type'         => $type,
							'category_name'     => $category,
							$type . '_category' => $custom_category,
							'numberposts'       => $num,
							'orderby'           => 'post_date',
							'order'             => 'DESC',
							'suppress_filters'  => $suppress_filters
						);

						global $post; // very important
						$carousel_posts = get_posts( $args );

						$output .= '<li class="es-carousel_li ' . $format . ' clearfix">';

						foreach ( $carousel_posts as $key => $post ) {
							$post_id = $post->ID;

							//Check if WPML is activated
							if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
								global $sitepress;

								$post_lang = $sitepress->get_language_for_element( $post_id, 'post_' . $type );
								$curr_lang = $sitepress->get_current_language();
								// Unset not translated posts
								if ( $post_lang != $curr_lang ) {
									unset( $carousel_posts[$j] );
								}
								// Post ID is different in a second language Solution
								if ( function_exists( 'icl_object_id' ) ) {
									$post = get_post( icl_object_id( $post_id, $type, true ) );
								}
							}
							setup_postdata( $post ); // very important
							$post_title      = esc_html( get_the_title( $post_id ) );
							$post_title_attr = esc_attr( strip_tags( get_the_title( $post_id ) ) );
							$format          = get_post_format( $post_id );
							$format          = (empty( $format )) ? 'format-standart' : 'format-' . $format;

							$attachment_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
							$url            = $attachment_url['0'];
							$image          = aq_resize($url, $thumb_width, $thumb_height, true);
							$mediaType      = get_post_meta($post_id, 'tz_portfolio_type', true);
							$prettyType     = 0;

							if ( get_post_meta( $post_id, 'tz_link_url', true ) ) {
								$post_permalink = ( $format == 'format-link' ) ? esc_url( get_post_meta( $post_id, 'tz_link_url', true ) ) : get_permalink( $post_id );
							} else {
								$post_permalink = get_permalink( $post_id );
							}
							if ( has_excerpt( $post_id ) ) {
								$excerpt = wp_strip_all_tags( get_the_excerpt() );
							} else {
								$excerpt = wp_strip_all_tags( strip_shortcodes (get_the_content() ) );
							}

							if ($counter > 2) { $counter = 1; 
								$output .= '<li class="es-carousel_li ' . $format . ' clearfix">';
							}

								$output .= '<div class="inner">';

								if ( $thumb == 'true' ) {
									if (has_post_thumbnail($post_id) && $mediaType == 'Image') {															
										$prettyType = 'prettyPhoto-'.$rand;
										$output .= '<figure class="featured-thumbnail thumbnail">';
											$output .= '<a href="' . $url . '" title="' . $post_title . '" rel="' .$prettyType.'">';
												$output .= '<img src="' . $image . '" alt="' . $post_title . '" />';
											$output .= '<span class="zoom-icon"></span></a>';
										$output .= '</figure>';

									} elseif ($mediaType != 'Video' && $mediaType != 'Audio') {

										$attachments = get_children( array(
											'orderby'        => 'menu_order',
											'order'          => 'ASC',
											'post_type'      => 'attachment',
											'post_parent'    => $post_id,
											'post_mime_type' => 'image',
											'post_status'    => null,
											'numberposts'    => -1
										) );

										if ( $attachments ) {
											$k = 0;
											foreach ( $attachments as $attachment_id => $attachment ) {
												$prettyType       = "prettyPhoto-".$rand ."[gallery".$i."]";
												$image_attributes = wp_get_attachment_image_src( $attachment_id, 'full' );
												$img              = aq_resize( $image_attributes[0], $thumb_width, $thumb_height, true );
												$alt              = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );

												if ( $k == 0 ) {
													if (has_post_thumbnail($post_id)) {		
														$output .= '<figure class="featured-thumbnail thumbnail">';
															$output .= '<a href="' .$image_attributes[0].'" title="' . $post_title . '" rel="' .$prettyType.'">';
																$output .= '<img src="' . $image . '" alt="' . $alt . '" />';
															//$output .= '<span class="zoom-icon"></span></a>';
														//$output .= '</figure>';
													} else {
														$output .= '<figure class="featured-thumbnail thumbnail">';
															$output .= '<a href="' .$image_attributes[0].'" title="' . $post_title . '" rel="' .$prettyType.'">';
																$output .= '<img src="' . $img . '" alt="' . $alt . '" />';
															//$output .= '<span class="zoom-icon"></span></a>';
														//$output .= '</figure>';
													}
												} else {
													$output .= '<figure class="featured-thumbnail thumbnail" style="display:none;">';
													$output .= '<a href="'.$image_attributes[0].'" title="'.get_the_title($post_id).'" rel="' .$prettyType.'">';
												}
												$output .= '<span class="zoom-icon"></span></a></figure>';
												$k++;		
											}
										} elseif (has_post_thumbnail($post_id)) {
											$prettyType = 'prettyPhoto-'.$rand;
											$output .= '<figure class="featured-thumbnail thumbnail">';
												$output .= '<a href="' .$url.'" title="' . $post_title . '" rel="' .$prettyType.'">';
													$output .= '<img src="' . $image . '" alt="' . $alt . '" />';
												$output .= '<span class="zoom-icon"></span></a>';
											$output .= '</figure>';
										}
									} else {
										$prettyType = 'prettyPhoto-'.$rand;
										$output .= '<figure class="featured-thumbnail thumbnail">';
											$output .= '<a href="' .$url.'" title="' . $post_title . '" rel="' .$prettyType.'">';
												$output .= '<img src="' . $image . '" alt="' . $alt . '" />';
											$output .= '<span class="zoom-icon"></span></a>';
										$output .= '</figure>';
									}
								}

								$output .= '<div class="desc">';

									// post date
									if ( $date == 'yes' ) {
										$output .= '<time datetime="' . get_the_time( 'Y-m-d\TH:i:s', $post_id ) . '">' . get_the_date() . '</time>';
									}

									// post author
									if ( $author == 'yes' ) {
										$output .= '<em class="author">&nbsp;<span>' . __('by', CHERRY_PLUGIN_DOMAIN) . '</span>&nbsp;<a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '">' . get_the_author_meta( 'display_name' ) . '</a> </em>';
									}

									// post comment count
									if ( $comments == 'yes' ) {
										$comment_count = $post->comment_count;
										if ( $comment_count >= 1 ) :
											$comment_count = $comment_count . ' <span>' . __( 'Comments', CHERRY_PLUGIN_DOMAIN ) . '</span>';
										else :
											$comment_count = $comment_count . ' <span>' . __( 'Comment', CHERRY_PLUGIN_DOMAIN ) . '</span>';
										endif;
										$output .= '<a href="'. $post_permalink . '#comments" class="comments_link">' . $comment_count . '</a>';
									}

									// post title
									if ( !empty($post_title{0}) ) {
										$output .= '<h5><a href="' . $post_permalink . '" title="' . $post_title_attr . '">';
											$output .= $post_title;
										$output .= '</a></h5>';
									}

									// post excerpt
									if ( !empty($excerpt{0}) ) {
										$output .= $excerpt_count > 0 ? '<p class="excerpt">' . my_string_limit_words( $excerpt, $excerpt_count ) . '</p>' : '';
									}

									// post more button
									$more_text_single = esc_html( wp_kses_data( $more_text_single ) );
									if ( $more_text_single != '' ) {
										$output .= '<a href="' . get_permalink( $post_id ) . '" class="btn btn-primary" title="' . $post_title_attr . '">';
											$output .= __( $more_text_single, CHERRY_PLUGIN_DOMAIN );
										$output .= '</a>';
									}
								$output .= '</div>';
								$output .= '</div>';
							if ($counter % 2 == 0) { 	
								$output .= '</li>';
							}
							$counter++;
							$i++;
						}
						wp_reset_postdata(); // restore the global $post variable

					$output .= '</ul>';
				$output .= '</div></div>';
				$output .= '<script>
					jQuery(document).ready(function(){
						jQuery("#carousel-' . $carousel_uniqid . '").elastislide({
							imageW  : ' . $thumb_width . ',
							minItems: ' . $min_items . ',
							speed   : 600,
							easing  : "easeOutQuart",
							margin  : ' . $spacer . ',
							border  : 0
						});
					})';
				$output .= '</script>';
			$output .= '</div>';

			return $output;
		}
		add_shortcode('carousel', 'shortcode_carousel');
	}

	//Recent Testimonials
	if (!function_exists('shortcode_recenttesti')) {

		function shortcode_recenttesti($atts, $content = null) {
			extract(shortcode_atts(array(
					'num'           => '5',
					'thumb'         => 'true',
					'excerpt_count' => '30',
					'custom_class'  => '',
			), $atts));

			// WPML filter
			$suppress_filters = get_option('suppress_filters');

			$args = array(
					'post_type'        => 'testi',
					'numberposts'      => $num,
					'orderby'          => 'post_date',
					'suppress_filters' => $suppress_filters
				);
			$testi = get_posts($args);

			$itemcounter = 0;

			$output = '<div class="testimonials '.$custom_class.'">';

			global $post;
			global $my_string_limit_words;

			foreach ($testi as $k => $post) {
				//Check if WPML is activated
				if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
					global $sitepress;

					$post_lang = $sitepress->get_language_for_element($post->ID, 'post_testi');
					$curr_lang = $sitepress->get_current_language();
					// Unset not translated posts
					if ( $post_lang != $curr_lang ) {
						unset( $testi[$k] );
					}
					// Post ID is different in a second language Solution
					if ( function_exists( 'icl_object_id' ) ) {
						$post = get_post( icl_object_id( $post->ID, 'testi', true ) );
					}
				}
				setup_postdata($post);
				$excerpt        = get_the_excerpt();
				$content        = get_the_content();
				$testiname      = get_post_meta(get_the_ID(), 'my_testi_caption', true);
				$testiurl       = get_post_meta(get_the_ID(), 'my_testi_url', true);
				$testiinfo      = get_post_meta(get_the_ID(), 'my_testi_info', true);
				$attachment_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
				$url            = $attachment_url['0'];
				$image          = aq_resize($url, 370, 228, true);

				$output .= '<div class="testi-item list-item-'.$itemcounter.'">';
					if ($thumb == 'true') {
						if ( has_post_thumbnail($post->ID) ){
							$output .= '<figure class="featured-thumbnail">';
							$output .= '<img src="'.$image.'" alt="" />';
							$output .= '</figure>';
						}
					}
					$output .= '<blockquote class="testi-item_blockquote">';
						
						$output .= '<a href="'.get_permalink($post->ID).'">';
							$output .= my_string_limit_words($content,$excerpt_count);
						$output .= '</a><div class="clear"></div>';

					$output .= '</blockquote>';

					$output .= '<small class="testi-meta">';
						if( $testiname!="" ) {
							$output .= '<span class="user">';
								$output .= $testiname;
							$output .= '</span>';
						}

						if( $testiinfo!="" ) {
							$output .= ', <span class="info">';
								$output .= $testiinfo;
							$output .= '</span><br>';
						}

						if( $testiurl!="" ) {
							$output .= '<a href="'.$testiurl.'">';
								$output .= $testiurl;
							$output .= '</a>';
						}

					$output .= '</small>';

				$output .= '</div>';
				$itemcounter++;

			}
			wp_reset_postdata(); // restore the global $post variable
			$output .= '</div>';
			return $output;
		}
		add_shortcode('recenttesti', 'shortcode_recenttesti');

	}

	//------------------------------------------------------
	//  Related Posts
	//------------------------------------------------------
	if(!function_exists('cherry_related_posts')){
		function cherry_related_posts($args = array()){
			global $post;
			$default = array(
				'post_type' => get_post_type($post),
				'class' => 'related-posts',
				'class_list' => 'related-posts_list',
				'class_list_item' => 'related-posts_item',
				'display_title' => true,
				'display_link' => true,
				'display_thumbnail' => true,
				'width_thumbnail' => 170,
				'height_thumbnail' => 160,
				'before_title' => '<h3 class="related-posts_h">',
				'after_title' => '</h3>',
				'posts_count' => 4
			);
			extract(array_merge($default, $args));

			$post_tags = wp_get_post_terms($post->ID, $post_type.'_tag', array("fields" => "slugs"));
			$tags_type = $post_type=='post' ? 'tag' : $post_type.'_tag' ;
			$suppress_filters = get_option('suppress_filters');// WPML filter
			$blog_related = apply_filters( 'cherry_text_translate', of_get_option('blog_related'), 'blog_related' );
			if ($post_tags && !is_wp_error($post_tags)) {
				$args = array(
					"$tags_type" => implode(',', $post_tags),
					'post_status' => 'publish',
					'posts_per_page' => $posts_count,
					'ignore_sticky_posts' => 1,
					'post__not_in' => array($post->ID),
					'post_type' => $post_type,
					'suppress_filters' => $suppress_filters
					);
				query_posts($args);
				if ( have_posts() ) {
					$output = '<div class="'.$class.'">';
					$output .= $display_title ? $before_title.$blog_related.$after_title : '' ;
					$output .= '<ul class="'.$class_list.' clearfix">';
					while( have_posts() ) {
						the_post();
						$thumb   = has_post_thumbnail() ? get_post_thumbnail_id() : PARENT_URL.'/images/empty_thumb.gif';
						$blank_img = stripos($thumb, 'empty_thumb.gif');
						$img_url = $blank_img ? $thumb : wp_get_attachment_url( $thumb,'full');
						$image   = $blank_img ? $thumb : aq_resize($img_url, $width_thumbnail, $height_thumbnail, true) or $img_url;

						$output .= '<li class="'.$class_list_item.'">';
						$output .= $display_thumbnail ? '<figure class="thumbnail featured-thumbnail"><a href="'.get_permalink().'" title="'.get_the_title().'"><img data-src="'.$image.'" alt="'.get_the_title().'" /></a></figure>': '' ;
						$output .= $display_link ? '<a href="'.get_permalink().'" >'.get_the_title().'</a>': '' ;
						$output .= '</li>';
					}
					$output .= '</ul></div>';
					echo $output;
				}
				wp_reset_query();
			}
		}
	}

	/*-----------------------------------------------------------------------------------*/
	/* Custom Comments Structure
	/*-----------------------------------------------------------------------------------*/
	if ( !function_exists( 'mytheme_comment' ) ) {
		function mytheme_comment($comment, $args, $depth) {
			$GLOBALS['comment'] = $comment;
		?>
		<li <?php comment_class('clearfix'); ?> id="li-comment-<?php comment_ID() ?>">
			<div id="comment-<?php comment_ID(); ?>" class="comment-body clearfix">
				<div class="wrapper">
					<div class="comment-author vcard">
						<?php echo get_avatar( $comment->comment_author_email, 80 ); ?>
						<?php printf('<span class="author">%1$s</span>', get_comment_author_link()) ?>
					</div>
					<?php if ($comment->comment_approved == '0') : ?>
						<em><?php echo theme_locals("your_comment") ?></em>
					<?php endif; ?>
					<div class="extra-wrap">
						<div class="comment-text"><?php comment_text(); ?></div>

						<div class="extra-wrap">
							<div class="reply">
								<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
							</div>
							<div class="comment-meta commentmetadata"><?php printf('%1$s', get_comment_date()) ?></div>
						</div>
					</div>
				</div>				
			</div>
	<?php }
	}
?>