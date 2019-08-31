<?php /* Template Name: Custom Profile */
    $user_id = um_profile_id();
    $user_data = get_userdata($user_id);
    $user_data->avatar = get_avatar_url($user_id);
    $user_data->verification = $user_data->personal_verification;

    $recommendedByID = $user_data->recommended_by; 
    if (isset($recommendedByID)) {
        $user_data->recommendedBy = $recommendedByUser = get_userdata($recommendedByID)->display_name;
    }

    $user_data->joined = date('F Y', strtotime($user_data->data->user_registered));
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
                <span id="attended"> </span>
            </div>
            <div id="hosted-wrap" style="display : none ">
                <label> Hosted: </label>
                <span id="hosted"> </span>
            </div>
        </div>

        <div class="sessions recommendedBy" style="display : none">
            <div id="recommended-wrap">Recommended by <span id="recommended"></span></div>
        </div>

        <div class="schedule"></div>
    </div>


</div><!-- .content-area -->


</div><!-- Container end -->

</div><!-- Wrapper end -->

<script>
    const data              = <?php echo json_encode($user_data)  ?>;
    let sessions            = <?php echo json_encode($classes)  ?>;
    const $                 = jQuery;

    jQuery(document).ready(function() {
        sessions.sort(function(a, b){
            let today = new Date();
            let dateA = moment(a.meta.date + a.meta.time_of_class.replace(' ', ''), 'DD-MM-YYYY h:mm A').toDate()
            let dateB = moment(b.meta.date + b.meta.time_of_class.replace(' ', ''), 'DD-MM-YYYY hh:mm A').toDate()

            return dateA < dateB ? -1 : dateA > dateB ? 1 : 0;
        });

        sessions.filter(session => session.meta.host_name_.ID === data.ID);
        if (data.roles.indexOf('host') !== -1) {
            $('.schedule').append('<div class="session-hosted">Sessions Hosted </div>');    
            sessions.map(session => {

                const sessionDate = moment(session.meta.date, 'DD-MM-YYYY').toDate();
                const formattedDate = moment(sessionDate).format('dddd, MMMM DD, YYYY');

                let item = `<div class="session">`;

                if (session.thumbnail[0]) { 
                     item += `
                         <div class="left-chunk">
                            <img src=${session.thumbnail[0]} />
                         </div>`
                }

                item += `
                     <div class="right-chunk">
                         <h1>
                             <div class="title"> ${session.post_title} </div>
                             <a href=${session.host_profile} class="host">
                                <span class="dashicons dashicons-admin-users"></span>
                                ${session.meta.host_name_.display_name} </a>
                             </a>
                         </h1>
                         <div class="details">
                             <div class="date"> ${formattedDate} </div>
                             <div class="time"> ${session.meta.time_of_class} </div>
                             <div class="address"> ${session.meta.address} </div>
                         </div>
                         <div class="post-content"> ${session.post_content} </div>
                      </div>
                  </div>
              `;
                $('.schedule').append(item);    
            });
        }


        /* Get data from passed sessions */
        let passedSessions = sessions.filter(session => moment(session.meta.date, 'DD-MM-YYYY').toDate() < new Date());

        let hostedCount = 0;
        let attendedCount = 0;

        passedSessions.forEach(session => {
            const userId = data.ID;
            if (session.registered_users && session.registered_users.indexOf(userId) > -1) {
                attendedCount++;
            }
            if (session.meta.host_name_.ID === userId) {
                hostedCount++;
            }
        });


        console.log(data);

        /* Update DOM */
        if (data.roles.indexOf('host') > -1) {
            jQuery('#hosted-wrap').show();
            jQuery('#hosted').text(hostedCount);
        }
        jQuery('#attended').text(attendedCount);

        jQuery('#name').text(data.data.display_name);
        jQuery('#picture').attr("src", data.data.avatar);
        jQuery('#joined').text('Joined ' + data.data.joined);

        if (data.data.recommendedBy) {
            jQuery('.recommendedBy').show();
            jQuery('#recommended').text(data.data.recommended);
        }
        
        if (data.data.verification && data.data.verification.length === 3) {
            jQuery('.verified.yes').show();
        } else {
            jQuery('.verified.no').show();
        }
    });
</script>
<?php get_footer(); ?>
