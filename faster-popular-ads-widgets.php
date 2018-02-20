<?php
/*
Plugin Name:  Faster Popular Ads Widgets for ClassiPress
Plugin URI:   https://github.com/nateinaction/faster-popular-ads-widgets-for-classipress
Description:  Replace the standard ClassiPress Popular Posts widgets and make them faster
Version:      20180215
Author:       Nate Gay
Author URI:   https://nategay.me/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

function register_faster_popular_posts_widgets()
{
    register_widget('Faster_CP_Widget_Top_Ads_Today');
    register_widget('Faster_CP_Widget_Top_Ads_Overall');
}

add_action('widgets_init', 'register_faster_popular_posts_widgets');

/**
 * Adds Popular Ads Today widget.
 */
class Faster_CP_Widget_Top_Ads_Today extends WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    function __construct()
    {
        parent::__construct(
            'faster_top_ads_today',
            esc_html__('Faster ClassiPress Top Ads Today', 'text_domain'),
            array('description' => esc_html__('Display the top ads today.', 'text_domain'),)
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        faster_cp_count_widget(10, 'today');
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : esc_html__('New title', 'text_domain');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Title:',
                    'text_domain'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        return $instance;
    }

}

/**
 * Adds Popular Ads Overall widget.
 */
class Faster_CP_Widget_Top_Ads_Overall extends WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    function __construct()
    {
        parent::__construct(
            'faster_top_ads_overall',
            esc_html__('Faster ClassiPress Top Ads Overall', 'text_domain'),
            array('description' => esc_html__('Popular Ads Overall.', 'text_domain'),)
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        faster_cp_count_widget(5);
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : esc_html__('New title', 'text_domain');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Title:',
                    'text_domain'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        return $instance;
    }

}

/**
 * Displays list of popular ads/posts.
 *
 * @param string $filter
 * @param int $limit
 */
function faster_cp_count_widget($limit = 10, $filter = 'total')
{
    $popular_posts = get_option("faster_popular_posts_${filter}");

    echo '<ul class="pop">';
    if (!empty($popular_posts)) {

        $num_to_display = min($limit, count($popular_posts));
        for ($index = 0; $index < $num_to_display; $index++) {
            echo '<li>';
            echo '<a href="' . $popular_posts[$index]['permalink'] . '">';
            echo $popular_posts[$index]['title'];
            echo '</a> ';
            echo '(' . $popular_posts[$index]['count'] . '&nbsp;' . __('views', APP_TD) . ')';
            echo '</li>';
        }

    } else {
        echo '<li>' . __('No ads viewed yet.', APP_TD) . '</li>';
    }
    echo '</ul>';
}

/**
 * Register total and today crons
 *
 * These queries appear to be taking 40+ seconds. Splitting them up will allow each to have ~60s completion time on WPE
 */
function fasterRegisterPopularPostCrons()
{
    add_action('faster_popular_posts_total', 'totalCron');
    if (!wp_next_scheduled('faster_popular_posts_total')) {
        wp_schedule_event(time(), 'hourly', 'faster_popular_posts_total');
    }

    add_action('faster_popular_posts_today', 'todayCron');
    if (!wp_next_scheduled('faster_popular_posts_today')) {
        wp_schedule_event(time(), 'hourly', 'faster_popular_posts_today');
    }
}

fasterRegisterPopularPostCrons();

function totalCron()
{
    $total = longQuery();
    update_option('faster_popular_posts_total', $total);
}

function todayCron()
{
    $today = longQuery('today');
    update_option('faster_popular_posts_today', $today);
}

function longQuery($filter = 'total')
{
    $args = array(
        'post_type' => APP_POST_TYPE,
        'posts_per_page' => 10,
        'paged' => 1,
        'no_found_rows' => true,
        // ignore expired ads
        'meta_query' => array(
            array(
                'key' => 'cp_sys_expire_date',
                'value' => current_time('mysql'),
                'compare' => '>=',
                'type' => 'datetime'
            ),
        ),
    );

    $popular = new CP_Popular_Posts_Query($args, $filter);
    $posts = array();
    if ($popular->have_posts()) {
        while ($popular->have_posts()) {
            $popular->the_post();
            $post = array(
                'permalink' => get_permalink(),
                'title' => get_the_title(),
                'count' => appthemes_get_stats_by(get_the_ID(), $filter),
            );
            array_push($posts, $post);
        }
    }
    wp_reset_postdata();

    return $posts;
}
