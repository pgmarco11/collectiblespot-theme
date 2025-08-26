
<div class="collectible-footer container-fluid mt-0">
    <div id="Newsletter" class="row newsletter-signup py-4">
        <div class="col-lg-12 text-center">
            <h4 class="mb-3">Subscribe to Our Newsletter</h4>
            <div class="newsletter-form">
                <?php                    
                    echo do_shortcode('[mc4wp_form id=3517]'); 
                ?>
            </div>
        </div>
    </div>
    <div class="row social-links pt-4">
        <div class="col-lg-12">
                <div class="text-center text-lg-right mr-lg-5">
                    <?php
                        $email_address = get_option('email_address');
                        $facebook_url = get_option('facebook_url');
                        $x_url = get_option('x_url');
                        $instagram_url = get_option('instagram_url');
                        $youtube_url = get_option('youtube_url');
                    ?>
                    <!-- Social Links Section -->
                    <div class="mb-4">
                        <?php if ($email_address): ?>
                            <a href="mailto:<?php echo esc_attr($email_address); ?>" class="btn" aria-label="Email">
                                <i class="fas fa-envelope"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($youtube_url): ?>
                            
                            <a href="<?php echo esc_url($youtube_url); ?>" target="_blank" class="btn" aria-label="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($x_url): ?>
                        <a href="<?php echo esc_url($x_url); ?>" target="_blank" class="btn" aria-label="X (Twitter)">
                            <i class="fab fa-twitter"></i> 
                        </a>
                    <?php endif; ?>
                    <?php if ($instagram_url): ?>
                        <a href="<?php echo esc_url($instagram_url); ?>" target="_blank" class="btn" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                        <?php if ($facebook_url): ?>
                            <a href="<?php echo esc_url($facebook_url); ?>" target="_blank" class="btn" aria-label="Facebook">
                                <i class="fab fa-facebook"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
        </div>
    </div>
    <div class="row site-info">
        <div class="col-lg-12">
                <div class="text-center text-lg-center">
                    <ul class="pt-2">
                        <li><p>&copy; <?php echo date("Y"); ?> <?php bloginfo( 'name' ); ?>.&nbsp;</p></li>
                        <li><p><span>Website built by </span><a href="https://www.pgiammarco.com">Peter Giammarco</a>.&nbsp;</p></li>
                        <li><p><a href="/privacy-policy">Privacy Policy</a></p></li>
                    </ul>
                </div>
        </div>
    </div>
</div>
