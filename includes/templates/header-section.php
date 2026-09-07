<!-- Header Section -->
<div class="header-container">
        
        <div class="header-top">
            <div class="container">
                
                        <!-- Search Form -->
                        <div class="header-tools">
                            <?php if (is_user_logged_in()) : ?>
                                <a
                                    class="signin-link"
                                    href="<?php echo esc_url(
                                        get_post_type_archive_link('collection')
                                    ); ?>"
                                >
                                    <div class="signin-icon">
                                        <i class="bi bi-person-circle"></i>
                                    </div>

                                    <div class="signin-text">
                                        My Collection
                                    </div>
                                </a>
                            <?php else : ?>
                                <a
                                    class="signin-link open-modal"
                                    href="#signin-modal"
                                >
                                    <div class="signin-icon">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                    </div>

                                    <div class="signin-text">
                                        Sign In
                                    </div>
                                </a>

                                <a
                                    class="signin-link"
                                    href="<?php echo esc_url(home_url('/register/')); ?>"
                                >
                                    <div class="signin-icon">
                                        <i class="bi bi-person-plus"></i>
                                    </div>

                                    <div class="signin-text">
                                        Register
                                    </div>
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
                                    'container' => false, // Avoid extra <nav> wrapping
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