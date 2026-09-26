<?php
/**
 * Template for displaying the conditional h5p item.
 *
 * @author   ThimPress
 * @package  Learnpress/H5p/Templates
 * @version  3.0.1
 */

/**
 * Prevent loading this file directly
 */

use LearnPress\H5P\Model\UserH5PModel;
use LearnPress\Models\UserItems\UserItemModel;

defined( 'ABSPATH' ) || exit(); ?>

<?php

if ( ! isset( $userH5PModel ) ) {
	return;
}

$graduation    = '';
$h5p_id_assign = 0;
if ( $userH5PModel instanceof UserH5PModel ) {
	$h5pPostModel  = $userH5PModel->get_h5p_model();
	$h5p_id_assign = $h5pPostModel->get_h5p_interact();
	$graduation    = $userH5PModel->get_graduation();
}

if ( ! $h5p_id_assign ) {
	return;
}

if ( $userH5PModel->get_status() === UserItemModel::STATUS_COMPLETED ) {
	return;
}
?>

<div class="learn_press_h5p_condition <?php echo esc_attr( $graduation ); ?>">
	<?php echo do_shortcode( "[h5p id='{$h5p_id_assign}']" ); ?>
</div>
