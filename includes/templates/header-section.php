<!-- Header Section -->
<div class="header-container">
        
        <div class="header-top">
            <div class="container">
                
                        <!-- Search Form -->
                        <div class="header-tools">
                            <?php if (is_user_logged_in()) : ?>
                                <a class="signin-link" href="<?php
                                    $inventory_url = function_exists('tcs_inventory_url')
                                        ? tcs_inventory_url()
                                        : home_url('/my-collection/');
                                    echo esc_url($inventory_url);
                                ?>">
                                    <span class="signin-icon" aria-hidden="true"><i class="bi bi-person-circle"></i></span>
                                    <span class="signin-text">My Collection</span>
                                </a>
                                <a class="signin-link" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">
                                    <span class="signin-icon" aria-hidden="true"><i class="bi bi-box-arrow-right"></i></span>
                                    <span class="signin-text">Sign out</span>
                                </a>
                            <?php else : ?>
                                <button class="signin-link" type="button" data-bs-toggle="modal" data-bs-target="#tcs-signin-modal" aria-haspopup="dialog" aria-controls="tcs-signin-modal">
                                    <span class="signin-icon" aria-hidden="true"><i class="bi bi-box-arrow-in-right"></i></span>
                                    <span class="signin-text">Sign In</span>
                                </button>
                                <a class="signin-link" href="<?php echo esc_url(home_url('/register/')); ?>">
                                    <span class="signin-icon" aria-hidden="true"><i class="bi bi-person-plus"></i></span>
                                    <span class="signin-text">Register</span>
                                </a>
                            <?php endif; ?>
                        </div>

            </div>
        </div>

        <!-- Logo and Navigation -->
        <div class="header-main">
            <div class="container">
            <div class="logo">
                <a href="<?php echo esc_url(home_url()); ?>" title="The Collectible Spot">
                    <img src="<?php echo esc_url('/wp-content/uploads/2025/03/collectible-spot-logo-300x282.webp'); ?>" 
                        alt="Collectible Spot Logo" 
                        width="300" height="282" 
                    >
                </a>
            </div>

                <!-- Bootstrap Navbar -->
                <nav class="header-nav navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse" id="navbarNav">
                            <?php
                                wp_nav_menu(array(
                                    'theme_location' => 'main',
                                    'menu_class' => 'navbar-nav mb-0',
                                    'container' => false,
                                    'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                                    'walker' => new Bootstrap_NavWalker(), 
                                ));
                            ?>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <div class="header-search">
                <div class="container">
                    <!-- Signin Modal Link -->
                    <form class="d-flex w-100 mx-auto" style="max-width: 400px;" action="<?php echo esc_url(home_url('/')); ?>" method="get">
                        <div class="input-group" >
                            <input class="form-control" type="search" placeholder="Search Auctions & Collectibles" aria-label="Search" name="s" value="<?php echo get_search_query(); ?>">
                            <button class="input-group-text btn" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>

                    </form>
                </div> 
        </div>

</div>
