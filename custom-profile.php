<?php /* Template Name: Custom Profile */
    $user_id = um_profile_id();
    $user_data = get_userdata($user_id);
    $user_avatar = get_avatar_url($user_id);
    $user_verification = $user_data->personal_verification;

    $recommendedByID = $user_data->recommended_by; 
    if (isset($recommendedByID)) {
        $recommendedByUser = get_userdata($recommendedByID)->display_name;
    }

    $joinedDate = date('F Y', strtotime($user_data->data->user_registered));
?>

<?php get_header(); ?>

	<div class="user-profile-container">
        <div class="center-me">
            <span id="name"></span>
            <img alt="profile picture" id="picture"></span>
            <span id="joined"></span>

            <span class="verified yes" style="display : none">
                <span class="dashicons dashicons-yes"></span>
                <span> Verified </span>
            </span>
            <span class="verified no" style="display : none">
                <span class="dashicons dashicons-no"></span>
                <span>Not Verified </span>
            </span>
        </div>

        <div class="sessions">
            <h1> Sessions </h1>
            <div>
                <label> Attended: </label>
                <span> 5 </span>
            </div>
            <div>
                <label> Hosted: </label>
                <span> 2 </span>
            </div>
        </div>

        <div class="sessions recommendedBy" style="display : none">
            <div id="recommended-wrap">Recommended by <span id="recommended"></span></div>
        </div>
    </div>


</div><!-- .content-area -->


</div><!-- Container end -->

</div><!-- Wrapper end -->

<script>
    const data              = <?php echo json_encode($user_data)  ?>;
    const avatar            = <?php echo json_encode($user_avatar)  ?>;
    const verification      = <?php echo json_encode($user_verification)  ?>;
    const recommended       = <?php echo json_encode($recommendedByUser)  ?>;
    const joined            = <?php echo json_encode($joinedDate)  ?>;

    jQuery(document).ready(function() {
        console.log('data' , data);
        console.log('avatar' , avatar);
        console.log('recommended', recommended);
        console.log('verification ', verification);

        jQuery('#name').text(data.data.display_name);
        jQuery('#picture').attr("src", avatar);
        jQuery('#joined').text('Joined ' + joined);

        if (recommended) {
            console.log('show me');
            jQuery('.recommendedBy').show();
            jQuery('#recommended').text(recommended);
        }
        
        if (verification && verification.length === 3) {
            jQuery('.verified.yes').show();
        } else {
            jQuery('.verified.no').show();
        }
    });
</script>
<?php get_footer(); ?>
