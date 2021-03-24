<?php

class users_widget extends WP_Widget
{
    public function __construct()
    {
        $widget_options = array(
            'classname' => 'users_widget',
            'description' => 'Comments users out',
        );
        parent::__construct('users_widget', 'Users comments', $widget_options);
    }
    public function widget($args, $instance)
    {
        // Instance VARS
        $users_count = $instance[ 'users_count' ];
        $show_empty = $instance[ 'show_empty' ] ? 'true' : 'false';
        $show_count = $instance[ 'show_count' ] ? 'true' : 'false';
        $user_role = $instance[ 'user_role' ];
        $title = apply_filters('widget_title', $instance['title']);
        echo $args['before_widget']; // Before widget html
        if (! empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        $role_list = explode(",", $user_role);

        $users = get_users();
        $total_users = count($users);
        $curData = array();
        foreach ($users as $user) {
            $name = $user->display_name;
            $userId = $user->ID;
            $commentsArgs = array(
              'user_id' => $userId,
              'count' => true
          );
            $commentsCount = get_comments($commentsArgs);
            $role = $user->roles['0'];
            $userData = array(
            'user_id' => $userId,
            'user_name' => $name,
            'user_role' => $role,
            'user_comments' => $commentsCount
          );
            if (in_array($role, $role_list)) {
                if ($show_empty == 'true') {
                    array_push($curData, $userData);
                } else {
                    if ($commentsCount != 0) {
                        array_push($curData, $userData);
                    }
                }
            } else {
            }
        }

        $sortArray = array();
        foreach ($curData as $data) {
            foreach ($data as $key=>$value) {
                if (!isset($sortArray[$key])) {
                    $sortArray[$key] = array();
                }
                $sortArray[$key][] = $value;
            }
        }
        $orderby = "user_comments"; // args for sort
        array_multisort($sortArray[$orderby], SORT_NUMERIC, SORT_DESC, $curData); // Sorting users by comments count

        echo '<div class="usersComments__wr"><ul class="usersComments">'; // Start main wrapper
        $i = 0; // For checking users count
        foreach ($curData as $data) {
            $name = $data['user_name'];
            $role = $data['user_role'];
            $id = $data['user_id'];
            $count = $data['user_comments'];
            if ($i < $users_count) { ?>
                <li class="usersComments__user">
                  <span class="usersComments__user-name"><?= $name ?></span>
                  <?php if ($show_count == 'true') : ?>
                    <span class="usersComments__user-count">(<?= $count ?>)</span>
                  <?php endif; ?>
                </li>
              <?php
              }
            $i++;
        }
        echo '</ul></div>'; // Stop main wrapper
        echo $args['after_widget']; // After widget html
    }

    public function form($instance) // Widget Forms in admin
    {
        if (isset($instance[ 'title' ])) {
            $title = $instance[ 'title' ];
        } else {
            $title = __('New title', 'userswidget');
        }
        $instance['user_role'] = !empty($instance['user_role']) ? explode(",", $instance['user_role']) :  array();
        $instance = wp_parse_args(( array ) $instance, $defaults);

        if (isset($instance[ 'users_count' ])) {
            $users_count = $instance[ 'users_count' ];
        }
        if (isset($instance[ 'user_role' ])) {
            $user_role = $instance[ 'user_role' ];
        } ?>
        <!-- Start Admin widget controls -->
        <div class="widgetControls">
          <div class="widgetControls__title">
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
          </div>
          <div class="widgetControls__usercount">
            <label for="<?php echo $this->get_field_id('users_count'); ?>">Users count:</label>
            <input id="<?php echo $this->get_field_id('users_count'); ?>" name="<?php echo $this->get_field_name('users_count'); ?>" type="number" max="<?=$total_users?>" value="<?php echo ($users_count) ? esc_attr($users_count) : '5'; ?>" size="3" />
          </div>
          <div class="widgetControls__empty">
            <input class="checkbox" type="checkbox" <?php checked($instance[ 'show_empty' ], 'on'); ?> id="<?php echo $this->get_field_id('show_empty'); ?>" name="<?php echo $this->get_field_name('show_empty'); ?>" />
            <label for="<?php echo $this->get_field_id('show_empty'); ?>">Show empty users</label>
          </div>
          <div class="widgetControls__showcount">
            <input class="checkbox" type="checkbox" <?php checked($instance[ 'show_count' ], 'on'); ?> id="<?php echo $this->get_field_id('show_count'); ?>" name="<?php echo $this->get_field_name('show_count'); ?>" />
            <label for="<?php echo $this->get_field_id('show_count'); ?>">Show comments count</label>
          </div>
          <?php // User role
            global $wp_roles;
        $roles = $wp_roles->roles; ?>
          <div class="widgetControls__userrole">
            <label for="<?php echo $this->get_field_id('user_role'); ?>"><?php _e('Select user role:'); ?></label><br />
              <?php
                foreach ($roles as $role) {
                    $checked = "";
                    if (in_array(strtolower($role['name']), $instance['user_role'])) {
                        $checked = "checked='checked'";
                    } ?>
                <input type="checkbox" class="checkbox" id="<?php echo $role['name']; ?>" name="<?php echo $this->get_field_name('user_role[]'); ?>" value="<?php echo strtolower($role['name']); ?>"  <?php echo $checked; ?>/>
                <label for="<?php echo $role['name']; ?>"><?php echo $role['name']; ?></label><br />
              <?php
                } ?>
            </div>
        </div>
        <!-- End Admin widget controls -->
        <?php
    }

    public function update($new_instance, $old_instance) // On update widget
    {
        $instance = $old_instance;
        $instance['title'] = (! empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['user_role'] = !empty($new_instance['user_role']) ? implode(",", $new_instance['user_role']) : 0;
        $instance[ 'users_count' ] = (is_numeric($new_instance[ 'users_count' ])) ? $new_instance[ 'users_count' ] : '3';
        $instance[ 'show_empty' ] = $new_instance[ 'show_empty' ];
        $instance[ 'show_count' ] = $new_instance[ 'show_count' ];
        return $instance;
    }
}

// Reg and activate widget
function users_register_widget()
{
    register_widget('users_widget');
}
add_action('widgets_init', 'users_register_widget');
