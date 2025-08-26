<?php

function u_head(){
    ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <?php
}
add_action('wp_head', 'u_head', 5);