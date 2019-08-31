<?php /* Template Name: Scheduler */
    if ( !is_user_logged_in() ) {
        auth_redirect();
    }

    /*
    $classes = get_posts(array( 'post_type' => 'classes'));
    foreach($classes as $i => $class) {
        $meta = get_fields($class->ID);
        $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $class->ID ), 'single-post-thumbnail' );

        $classes[$i]->meta          = $meta;
        $classes[$i]->host_profile  = um_user_profile_url($meta->host_name_->ID);
        $classes[$i]->thumbnail     = $thumbnail;
        $classes[$i]->registered_users = get_field('registered_users', $class->ID);
    }
    */

?>

<?php get_header(); ?>

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
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div><!-- .content-area -->


</div><!-- Container end -->

</div><!-- Wrapper end -->

<script>
    let classes       = <?php echo json_encode($classes)  ?>;
    const currentUser   = <?php echo json_encode($currentUser)  ?>;
    const url           = <?php echo json_encode(get_site_url( $wp->request )) ?>;
    const users         = <?php echo json_encode($users)  ?>;
    const $             = jQuery;
    //console.log(url, classes, users, currentUser);
    console.log(classes);


    $(document).ready(function() {

        // Sort classes by date, only show classes from today onwards if basic or host user
        classes.sort(function(a, b){
            let today = new Date();

            let dateA = moment(a.meta.date + a.meta.time_of_class.replace(' ', ''), 'DD-MM-YYYY h:mm A').toDate()
            let dateB = moment(b.meta.date + b.meta.time_of_class.replace(' ', ''), 'DD-MM-YYYY hh:mm A').toDate()

            return dateA > dateB ? -1 : dateA < dateB ? 1 : 0;
        });

        //const basicOrHost = currentUser.roles.indexOf('basic') !== -1 || currentUser.roles.indexOf('host') !== -1 ? true : false;
        const basicOrHost = currentUser.roles.indexOf('administrator') === -1 ? true : false;
        if (basicOrHost) {
            classes  = classes.filter(klass => moment(klass.meta.date, 'DD-MM-YYYY').toDate() > new Date());
        }

        classes.reverse();

        // Build DOM structure
        for (var i = 0; i < classes.length; i++) {

            const classDate = moment(classes[i].meta.date, 'DD-MM-YYYY').toDate();
            const formattedDate = moment(classDate).format('dddd, MMMM DD, YYYY');

            let item = `<div class="a-class">`;

            if (classes[i].thumbnail[0]) { 
                 item += `
                     <div class="left-chunk">
                        <img src=${classes[i].thumbnail[0]} />
                     </div>`
            }

            item += `
                 <div class="right-chunk">
                     <h1>
                         <div class="title"> ${classes[i].post_title} </div>
                         <a href=${classes[i].host_profile} class="host"> 
                            <span class="dashicons dashicons-admin-users"></span>
                            ${classes[i].meta.host_name_.display_name}
                         </a>
                     </h1>
                     <div class="details">
                         <div class="date"> ${formattedDate} </div>
                         <div class="time"> ${classes[i].meta.time_of_class} </div>
                         <div class="address"> ${classes[i].meta.address} </div>
                     </div>
                     <div class="post-content"> ${classes[i].post_content} </div>

                     <div class="sign-up" id=${classes[i].ID}>
                         ${drawSignUp(classes[i].ID, classes[i].registered_users, classes[i].meta.slots_available)}
                     </div>
                  </div>
              </div>
          `;
            $('.schedule').append(item);    
        }
    });


    function drawSignUp(classId, registered, slotsAvailable, update) {

        // Registered will equal fail only when the user has tried to sign up for a class that is already full
        if (registered  !== 'fail') {
            const registeredCount = registered ? registered.length : 0;
            const signedUp = (registeredCount > 0 && registered.indexOf(currentUser.ID) !== -1) ? true : false;

            let item = '';

            if (signedUp || (slotsAvailable - registeredCount !== 0)) {
                item += `<button type="button" data-class-id=${classId} class="btn btn-outline-info" onclick="signUp(event)"> ${signedUp ? 'Cancel Sign Up' : 'Sign Up'}</button>`
            }

            if (registeredCount  >= slotsAvailable) {
                item += `Class Full!`
            } else {
                item += `
                     <div class="spots">
                        ${(slotsAvailable - registeredCount)} of ${slotsAvailable} open
                    </div>`
            }

            const adminOrHost = currentUser.roles.indexOf('administrator') !== -1 || currentUser.roles.indexOf('host') !== -1 ? true : false;
            if (adminOrHost) {
                item += `<button type="button" class="btn btn-outline-dark details-button" data-toggle="modal" data-target="#details" onClick="getModalDetails(${classId})">
                        Details
                    </button>`
            }

            if (update) {
                $('#' + classId).html(item);
            } else {
                return item;
            }
        } else {
            let danger = `<div class="alert alert-danger" role="alert">
                            Oops, this class is already full! 
                          </div>`
            $('#' + classId).html(danger);
        }
    }

    function signUp(event) {
        let classId = $(event.target).data('class-id');

        let body = {
           'id' : classId,
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
               drawSignUp(classId, json.data.registered, json.data.slots, true);

               // Update classes data
               const idxToUpdate = classes.findIndex(klass => klass.ID === classId);
               classes[idxToUpdate].registered_users = json.data.registered;

            } else {
               drawSignUp(classId, 'fail');
            }
        })
        .catch(error => console.log(error.message));
    }

    function getModalDetails(classId) {
        thisClass = classes.find(klass => klass.ID === classId);

        $('.modal-title').html(`${thisClass.post_title}`);
        $('.list-group').html('');

        thisClass.registered_users.map(ID => {
            let thisUser = users.find(user => user.ID === ID);
            $('.list-group').append(`
                <div class="list-group-item"> 
                    ${thisUser.data.display_name} 
                    <a href="${thisUser.data.profile_url}">
                        <button type="button" class="btn btn-outline-dark">
                            View Profile
                        </button>
                    </a>
                </div>
            `);
        });
    }
</script>
<?php get_footer(); ?>
