<?php /* Template Name: Scheduler */
    if ( !is_user_logged_in() ) {
        auth_redirect();
    }
?>

<?php get_header(); ?>

    <?php while ( have_posts() ) : the_post(); ?>

        <div class="main-schedule-wrap">
            <div class="schedule"></div>

            <div class="modal fade" id="details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="detailModalLabel">Modal title</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <ul class="list-group">
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-dark details-button" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endwhile; // end of the loop. ?>


</div><!-- .content-area -->


</div><!-- Container end -->

</div><!-- Wrapper end -->

<script>
    let sessions        = <?php echo json_encode($classes)  ?>;
    const currentUser   = <?php echo json_encode($currentUser)  ?>;
    const url           = <?php echo json_encode(get_site_url( $wp->request )) ?>;
    const users         = <?php echo json_encode($users)  ?>;
    const $             = jQuery;
    //console.log(url, sessions, users, currentUser);
    //console.log(sessions);

    $(document).ready(function() {

        // Sort classes by date, only show classes from today onwards if non admin user
        sessions.sort(function(a, b){
            let dateA = moment(a.meta.date + a.meta.time_of_class.replace(' ', ''), 'DD-MM-YYYY h:mm A').toDate()
            let dateB = moment(b.meta.date + b.meta.time_of_class.replace(' ', ''), 'DD-MM-YYYY hh:mm A').toDate()

            return dateA < dateB ? -1 : dateA > dateB ? 1 : 0;
        });

        //const basicOrHost = currentUser.roles.indexOf('basic') !== -1 || currentUser.roles.indexOf('host') !== -1 ? true : false;
        const basicOrHost = currentUser.roles.indexOf('administrator') === -1 ? true : false;
        if (basicOrHost) {
            sessions  = sessions.filter(session => moment(session.meta.date, 'DD-MM-YYYY').toDate() > new Date());
        }

        // Build DOM structure
        for (var i = 0; i < sessions.length; i++) {

            const classDate = moment(sessions[i].meta.date, 'DD-MM-YYYY').toDate();
            const formattedDate = moment(classDate).format('dddd, MMMM DD, YYYY');

            let item = `<div class="session">`;

            if (sessions[i].thumbnail[0]) { 
                 item += `
                     <div class="left-chunk">
                        <img src=${sessions[i].thumbnail[0]} />
                     </div>`
            }

            item += `
                 <div class="right-chunk">
                     <h1>
                         <div class="title"> ${sessions[i].post_title} </div>
                         <a href=${sessions[i].host_profile} class="host"> 
                            <span class="dashicons dashicons-admin-users"></span>
                            Hosted by
                            ${sessions[i].meta.host_name_.display_name}
                         </a>
                     </h1>
                     <div class="details">
                         <div class="date"> ${formattedDate} </div>
                         <div class="time"> ${sessions[i].meta.time_of_class} </div>
                         <div class="address"> ${sessions[i].meta.address} </div>
                     </div>
                     <div class="post-content"> ${sessions[i].post_content} </div>

                     <div class="sign-up" id=${sessions[i].ID}>
                         ${drawSignUp(sessions[i].ID, sessions[i].registered_users, sessions[i].meta.slots_available)}
                     </div>
                  </div>
              </div>
          `;
            $('.schedule').append(item);    
        }
    });


    function drawSignUp(sessionId, registered, slotsAvailable, update) {

        // Registered will equal fail only when the user has tried to sign up for a class that is already full
        if (registered  !== 'fail') {
            const registeredCount = registered ? registered.length : 0;
            const signedUp = (registeredCount > 0 && registered.indexOf(currentUser.ID) !== -1) ? true : false;

            let item = '';

            if (signedUp || (slotsAvailable - registeredCount !== 0)) {
                item += `<button type="button" data-session-id=${sessionId} class="btn btn-outline-info custom-button-color" onclick="signUp(event)"> ${signedUp ? 'Cancel Sign Up' : 'Sign Up'}</button>`
            }

            if (registeredCount  >= slotsAvailable) {
                item += `Session Full!`
            } else {
                item += `
                     <div class="spots">
                        ${(slotsAvailable - registeredCount)} of ${slotsAvailable} open
                    </div>`
            }

            item += `<button type="button" class="btn btn-outline-dark details-button" data-toggle="modal" data-target="#details" onClick="getModalDetails(${sessionId})">
                        Who else is attending
                    </button>`

            if (update) {
                $('#' + sessionId).html(item);
            } else {
                return item;
            }
        } else {
            let danger = `<div class="alert alert-danger" role="alert">
                            Oops, this class is already full! 
                          </div>`
            $('#' + sessionId).html(danger);
        }
    }

    function signUp(event) {
        let sessionId = $(event.target).data('session-id');

        let body = {
           'id' : sessionId,
           'user' : currentUser.ID 
        }

        let header = new Headers({ "Content-Type"  : "application/json" });

        fetch(url + '/wp-json/lotus/signup',{
          method : 'POST',
          credentials : 'same-origin',
          headers : header,
          body : JSON.stringify(body)
        })
        .then(response => response.json())
        .then(json => {
            console.log(json)

            if (json.success) {

               // Update the DOM
               drawSignUp(sessionId, json.data.registered, json.data.slots, true);

               // Update sessions data
               const idxToUpdate = sessions.findIndex(session => session.ID === sessionId);
               sessions[idxToUpdate].registered_users = json.data.registered;

            } else {
               drawSignUp(sessionId, 'fail');
            }
        })
        .catch(error => console.log(error.message));
    }

    function getModalDetails(sessionId) {
        thisSession = sessions.find(session => session.ID === sessionId);

        $('.modal-title').html(`${thisSession.post_title}`);
        $('.list-group').html('');

        thisSession.registered_users.map(ID => {
            let thisUser = users.find(user => user.ID === ID);
            $('.list-group').append(`
                <div class="list-group-item"> 
                    ${thisUser.data.display_name} 
                    <a href="${thisUser.data.profile_url}">
                        <button type="button" class="btn btn-outline-dark details-button">
                            View Profile
                        </button>
                    </a>
                </div>
            `);
        });
    }
</script>
<?php get_footer(); ?>
