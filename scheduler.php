<?php /* Template Name: Scheduler */
    $classes = get_posts(array( 'post_type' => 'classes'));
    foreach($classes as $i => $class) {
        $meta = get_fields($class->ID);
        $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $class->ID ), 'single-post-thumbnail' );

        $classes[$i]->meta      = $meta;
        $classes[$i]->thumbnail = $thumbnail;
        $classes[$i]->registered_users = get_field('registered_users', $class->ID);
    }

?>

<?php get_header(); ?>

	<div class="schedule"></div>


</div><!-- .content-area -->


</div><!-- Container end -->

</div><!-- Wrapper end -->

<script>
    const classes   = <?php echo json_encode($classes)  ?>;
    const userId    = <?php echo get_current_user_id() ?>;
    const url       = <?php echo json_encode(get_site_url( $wp->request )) ?>;
    console.log(userId, url);
    const $ = jQuery;


    function drawSignUp(classId, registered, slotsAvailable, update) {

        // Registered will evaluate to false only when the user has tried to sign up for a class that is already full
        if (registered) {
            const registeredCount = registered ? registered.length : 0;
            const signedUp = (registeredCount > 0 && registered.indexOf(userId) !== -1) ? true : false;

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
           'user' : userId 
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
            } else {
               drawSignUp(classId, false);
            }
        })
        .catch(error => console.log(error.message));
    }

    $(document).ready(function() {
        console.log(classes[0].registered_users);

        // Build DOM structure
        for (var i = 0; i < classes.length; i++) {
            let item = 
                `
                 <div class="a-class">
                     <div class="left-chunk">
                         <img src=${classes[i].thumbnail[0]} />
                     </div>
                     <div class="right-chunk">
                         <h1>
                             <div class="title"> ${classes[i].post_title} </div>
                             <div class="host"> ${classes[i].meta.host_name_.display_name} </div>
                         </h1>
                         <div class="details">
                             <div class="date"> ${classes[i].meta.date} </div>
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
</script>

<style>
    .schedule {
        width : 90%;
        margin : 20px auto;
        font-family : Source Sans Pro;
    }
    .a-class {
        display : flex;
        margin : 30px 30px 100px;
    }
    .left-chunk {
        max-width : 35%;
        margin-right : 5%;
    }
    .right-chunk {
        min-width : 60%;
        max-width : 70%;
    }
    h1 {
        font-size : 28px;
        display : flex;
        align-items : center;
        justify-content : space-between;
    }
    .host {
        font-size: 22px;
    }
    .details {
        color : #7a7a7a;
        margin-bottom : 15px;
    }
    .sign-up {
        display : flex;
        flex-direction : column;
        justify-content : center;
        align-items : center;
        border-top : 1px solid #ddd;
        padding : 20px;
        margin-top : 20px;
    }
    .sign-up .spots {
        color : #7a7a7a;
        margin-top : 5px;
    }
    @media screen and (max-width: 650px) {
        .a-class {
            flex-direction : column;
        }
        .left-chunk {
            max-width : 100%;
            margin-bottom : 20px;
            margin-right : 0;
        }
        .right-chunk {
            max-width : 100%;
            margin-bottom : 20px;
        }
    }
</style>

<?php get_footer(); ?>
