<?php 

require_once (dirname( __FILE__ ) . '/sowprog_events_configuration.php');
require_once (dirname( __FILE__ ) . '/sowprog_events_output.php');

if (!class_exists('SowprogEventsVirtualPage'))
{
	class SowprogEventsVirtualPage {
		private $slug = NULL;
		private $title = NULL;
		private $content = NULL;
		private $author = NULL;
		private $date = NULL;

		public function __construct($args)
		{
			if (!isset($args['slug']))
				throw new Exception('No slug given for virtual page');

			$this->slug = $args['slug'];
			$this->title = isset($args['title']) ? $args['title'] : '';
			$this->content = isset($args['content']) ? $args['content'] : '';
			$this->author = isset($args['author']) ? $args['author'] : 1;
			$this->date = isset($args['date']) ? $args['date'] : current_time('mysql');
			$this->dategmt = isset($args['date']) ? $args['date'] : current_time('mysql', 1);

			add_filter('the_posts', array(&$this, 'virtualPage'));
		}

		// filter to create virtual page content
		public function virtualPage($posts)
		{
			global $wp, $wp_query;
			
			$sowprogEventsConfiguration = new SowprogEventsConfiguration();
			
			if (count($posts) == 0 &&
			(strcasecmp($wp->request, $this->slug) == 0 || $wp->query_vars['page_id'] == $this->slug))
			{
				//create a fake post intance
				$post = new stdClass;
				// fill properties of $post with everything a page in the database would have
				$post->ID = -42;                          // use an illegal value for page ID
				$post->post_author = $this->author;       // post author id
				$post->post_date = $this->date;           // date of post
				$post->post_date_gmt = $this->dategmt;
				$post->post_content = $this->content;
				$post->post_title = $this->title;
				$post->post_excerpt = '';
				$post->post_status = 'publish';
				$post->comment_status = 'closed';        // mark as closed for comments, since page doesn't exist
				$post->ping_status = 'closed';           // mark as closed for pings, since page doesn't exist
				$post->post_password = '';               // no password
				$post->post_name = $this->slug;
				$post->to_ping = '';
				$post->pinged = '';
				$post->modified = $post->post_date;
				$post->modified_gmt = $post->post_date_gmt;
				$post->post_content_filtered = '';
				$post->post_parent = 0;
				$post->guid = $sowprogEventsConfiguration->getCurrentURL();
				$post->menu_order = 0;
				$post->post_mime_type = '';
				$post->comment_count = 0;
				$post->page_template = '';

				// set filter results
				$posts = array($post);

				// reset wp_query properties to simulate a found page
				if ($sowprogEventsConfiguration->getShowAsPage()) {
					$wp_query->init();
					$post->post_type = 'page';
					$wp_query->is_page = TRUE;
					$wp_query->is_home = FALSE;
					$wp_query->is_archive = FALSE;
					$wp_query->is_category = FALSE;
					unset($wp_query->query['error']);
					$wp_query->query_vars['error'] = '';
					$wp_query->is_404 = FALSE;
				} else {
					$post->post_type = 'post';
					$wp_query->is_page = FALSE;
					$wp_query->is_single = TRUE;
					$wp_query->is_singular = TRUE;
					$wp_query->is_home = FALSE;
					$wp_query->is_archive = FALSE;
					$wp_query->is_category = FALSE;
					unset($wp_query->query['error']);
					$wp_query->query_vars['error'] = '';
					$wp_query->is_404 = FALSE;
				}
			}

			return ($posts);
		}
	}
}

function sowprog_create_virtual()
{
	$sowprogEventsConfiguration = new SowprogEventsConfiguration();
	
	$agenda_base_url = $sowprogEventsConfiguration->getAgendaPageFullURL();
	$current_url = $sowprogEventsConfiguration->getCurrentURLNoParameters();
	
	if (trim($current_url, '/') == trim($agenda_base_url, '/')) {
		if ( ! defined( 'DONOTCACHEPAGE' ) )
			define( "DONOTCACHEPAGE", true );

		if ( ! defined( 'DONOTCACHEOBJECT' ) )
			define( "DONOTCACHEOBJECT", true );

		if ( ! defined( 'DONOTCACHEDB' ) )
			define( "DONOTCACHEDB", true );

		nocache_headers();

		global $swc_data;
		global $swc_output;
		$sowprogEventsOutput = new SowprogEventsOutput();
		$swc_output = $sowprogEventsOutput->output_main_page();

		$title='Agenda';
		if($swc_data[title]) {
			$title = $swc_data[title];
		}

		$args = array('slug' => $sowprogEventsConfiguration->getAgendaPage(),
				'title' => $title,
				'content' => '[sp_events_private]'
		);
		$pg = new SowprogEventsVirtualPage($args);
	}
}

function sowprog_the_title( $title ) {
	$sowprogEventsConfiguration = new SowprogEventsConfiguration();
	
	$agenda_base_url = $sowprogEventsConfiguration->getAgendaPageFullURL();
	$current_url = $sowprogEventsConfiguration->getCurrentURLNoParameters();
		
	if (trim($current_url, '/') == trim($agenda_base_url, '/')) {
		global $swc_data;
		if($swc_data[title]) {
			$title = $swc_data[title] . ' | ' . $title;
		}
	}
	return $title;
}
add_action('wp_title', 'sowprog_the_title');

add_action('init', 'sowprog_create_virtual');

function sp_events_private_shortcode( $atts ) {
	global $swc_output;
	return $swc_output;
}
add_shortcode( 'sp_events_private', 'sp_events_private_shortcode' );


?>