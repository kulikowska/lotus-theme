<?php /* Template Name: Custom Profile */
    if ( !is_user_logged_in() ) {
        auth_redirect();
    }

    $user_id = um_profile_id();
    $user_data = get_userdata($user_id);
    $user_data->avatar = get_avatar_url($user_id);
    $user_data->verification = $user_data->personal_verification;

    $recommendedByID = $user_data->recommended_by; 
    if (isset($recommendedByID)) {
        //$user_data->recommendedBy = $recommendedByUser = get_userdata($recommendedByID)->display_name;
        $user_data->recommendedBy = $recommendedByID;
    }

    $user_data->joined = date('F Y', strtotime($user_data->data->user_registered));
?>

<?php get_header(); ?>

    <?php while ( have_posts() ) : the_post(); ?>
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

            <div class="sessions verification">
                <h1> Verification</h1>

                <!--
                <span class="verified license no">
                    <span class="dashicons dashicons-id"></span>
                    <span> Driver's License</span>
                </span>
                <span class="verified facebook no">
                    <span class="dashicons dashicons-facebook-alt"></span>
                    <span> Facebook</span>
                </span>
                <span class="verified mobile no">
                    <span class="dashicons dashicons-phone"></span>
                    <span> Mobile</span>
                </span>
                !-->

                <span class="verified license no">
                    <span class="dashicons dashicons-no"></span>
                    <span> Driver's License</span>
                </span>
                <span class="verified facebook no">
                    <span class="dashicons dashicons-no"></span>
                    <span> Facebook</span>
                </span>
                <span class="verified mobile no">
                    <span class="dashicons dashicons-no"></span>
                    <span> Mobile</span>
                </span>
            </div>
            <div class="sessions recommendedBy" style="display : none">
                <div id="recommended-wrap">Recommended by <span id="recommended"></span></div>
            </div>

            <div class="schedule"></div>
        </div>
    <?php endwhile; // end of the loop. ?>


</div><!-- .content-area -->


</div><!-- Container end -->

</div><!-- Wrapper end -->

<script>
    const data              = <?php echo json_encode($user_data)  ?>;
    const currentUser       = <?php echo json_encode($currentUser)  ?>;
    const allUsers          = <?php echo json_encode($users)  ?>;
    let sessions            = <?php echo json_encode($classes)  ?>;
    const $                 = jQuery;

    jQuery(document).ready(function() {
        sessions.sort(function(a, b){
            let today = new Date();
            let dateA = moment(a.meta.date + a.meta.time_of_class.replace(' ', ''), 'DD-MM-YYYY h:mm A').toDate()
            let dateB = moment(b.meta.date + b.meta.time_of_class.replace(' ', ''), 'DD-MM-YYYY hh:mm A').toDate()

            return dateA < dateB ? -1 : dateA > dateB ? 1 : 0;
        });

        let thisHostsSessions = sessions.filter(session => session.meta.host_name_.ID === data.ID);

        const basicOrHost = currentUser.roles.indexOf('administrator') === -1 ? true : false;
        let futureSessions = thisHostsSessions;
        if (basicOrHost) {
            futureSessions = thisHostsSessions.filter(session => moment(session.meta.date, 'DD-MM-YYYY').toDate() > new Date());
        }

        if (data.roles.indexOf('host') !== -1 || data.roles.indexOf('um_host') !== -1) {
            $('.schedule').append('<div class="session-hosted">Upcoming Sessions</div>');    
            futureSessions.map(session => {

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
                                Hosted by
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


        /* Update DOM */
        if (data.roles.indexOf('host') > -1 || data.roles.indexOf('um_host') > -1) {
            jQuery('#hosted-wrap').show();
            jQuery('#hosted').text(hostedCount);
        }
        jQuery('#attended').text(attendedCount);

        jQuery('#name').text(data.data.display_name);
        jQuery('#picture').attr("src", data.data.avatar);
        jQuery('#joined').text('Joined ' + data.data.joined);

        if (data.data.recommendedBy) {
            let recommendedByUser = allUsers.find(user => user.ID == data.data.recommendedBy);
            jQuery('.recommendedBy').show();
            jQuery('#recommended').html(`<a href="${recommendedByUser.data.profile_url}"> ${recommendedByUser.data.display_name} </a>`);
        }
        
        if (data.data.verification && data.data.verification.length === 3) {
            jQuery('.verified.yes').show();
        } else {
            jQuery('.verified.no').show();
        }

        if (data.data.verification.length > 0) {
            data.data.verification.forEach(type => {

                let map = {
                    'Drivers License' : 'license',
                    'Facebook'        : 'facebook',
                    'Mobile'          : 'mobile'
                }

                $(`.${map[type]}`).removeClass('no');
                $(`.${map[type]} .dashicons`).removeClass('dashicons-no');
                $(`.${map[type]}`).addClass('yes');
                $(`.${map[type]} .dashicons`).addClass('dashicons-yes');
            });
        }
    });
</script>
<?php get_footer(); ?>
