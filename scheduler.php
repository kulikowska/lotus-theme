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



    function signUp(event) {
        //console.log(classID);
        console.log($(event.target).data('class-id'));

        let body = {
           'id' : $(event.target).data('class-id'),
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
            }
        })
        .catch(error => console.log(error.message));
    }

    $(document).ready(function() {
        console.log(classes[0].registered_users);


        // Build DOM structure
        for (var i = 0; i < classes.length; i++) {
           //console.log(classes[i].registered_users, ' length');
           //console.log(classes[i].meta.slots_available, 'slots available');

           const registeredCount = classes[i].registered_users ? classes[i].registered_users.length : 0;
           const signedUp = (registeredCount > 0 && classes[i].registered_users.indexOf(userId) !== -1) ? true : false;

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
                  `;


                   if (signedUp) {
                     item += `
                         <button type="button" data-class-id=${classes[i].ID} class="btn btn-outline-info" onclick="signUp(event)">Cancel Sign Up</button>
                     `
                   } else {
                     item += `
                         <button type="button" data-class-id=${classes[i].ID} class="btn btn-outline-info" onclick="signUp(event)">Sign Up</button>
                     `
                   }


                   if (registeredCount  >= classes[i].meta.slots_available) {
                        item += `
                            Class Full!   
                        `
                   } else {
                        item += `
                             <div class="spots">
                                ${(classes[i].meta.slots_available - registeredCount)} of ${classes[i].meta.slots_available} open
                            </div>
                        `
                   }

                   item += `
                                </div>
                            </div>
                        </div>
                   `
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
</style>

<?php get_footer(); ?>
