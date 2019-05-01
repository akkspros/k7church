<?php

add_action('ch_UserPanelPublic', 'ch_PanelPublic', 10, 3);
function ch_PanelPublic($user_id, $length, $current_user)
{

    global $wpdb;
    $userId = $current_user->ID;
    $count = $wpdb->get_var(' SELECT COUNT(comment_ID)  FROM ' . $wpdb->comments . ' WHERE user_id = "' . $userId . '"'); ?>


    <div class="ch-header">
        <h3><?php echo $current_user->display_name; ?></h3>
    </div>

    <div class="ch-row">
        <div class="ch-col-3 ch-menu">
            <ul>
                <li>
                    <p><?php echo get_avatar($user_id, 200, '', '', array()); ?><p>
                </li>
                <li><?php echo __('Posts', 'k7') . "\t" . count_user_posts($user_id); ?></li>
                <li><?php echo __('Comments', 'k7') . "\t" . $count; ?></li>
                <li>*</li>
            </ul>
        </div>

        <div class="ch-col-9">

            <div class="ch-tab">
                <button class="ch-tablinks"
                        onclick="openTabs(event, 'tab1')"><?php echo __('Recent Activity', 'k7'); ?></button>
                <button class="ch-tablinks"
                        onclick="openTabs(event, 'tab2')"><?php echo __('Information', 'k7'); ?></button>
                <button class="ch-tablinks"
                        onclick="openTabs(event, 'tab3')"><?php echo __('ANOTHER', 'k7'); ?></button>
            </div>

            <div id="tab1" class="ch-tabcontent">
                <h3>Activity</h3>
                <table class="ch-table">
                    <tbody>
                    <?php
                    $args = array('user_id' => $user_id);// use user_id
                    $comments = get_comments($args);
                    foreach ($comments as $comment) : ?>
                        <tr>
                            <td><strong><?php echo($comment->comment_author); ?><br></strong><a
                                        href="<?php echo get_comment_link($comment->comment_ID); ?>"><?php echo $comment->comment_content; ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="tab2" class="ch-tabcontent">
                <h3></h3>
                <div class="panelt">
                    <div class="ch-panel-heading">Email <i class=""></i>
                    </div>
                    <div class="ch-body"><a
                                href="<?php echo $current_user->user_email; ?>"><?php echo $current_user->user_email; ?></a>
                    </div>
                </div>
                <div class="ch-default">
                    <div class="ch-panel-heading">Website <i class=""></i>
                    </div>
                    <div class="ch-body"><a
                                href="<?php echo $current_user->user_url; ?>"><?php echo $current_user->user_url; ?></a>
                    </div>
                </div>

            </div>

            <div id="tab3" class="ch-tabcontent">
                <h3>ANOTHER</h3>
                <h2><?php echo __('Description', 'k7'); ?></h2>
                <p><?php $authorDesc = the_author_meta('description');
                    echo $authorDesc; ?>
                </p>
            </div>


        </div>
    </div>

<?php }

  