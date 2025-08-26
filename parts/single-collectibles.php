 <div class="container-fluid d-flex p-0 m-0">

     <?php                        
      
         $ebay_item_id = get_post_meta(get_the_ID(), 'ebay_item_id', true); 
                                     
         $bids = get_post_meta(get_the_ID(), 'ebay_bid_count', true);
         $date_string = get_post_meta(get_the_ID(), 'ebay_end_date', true);
         $date = new DateTime($date_string, new DateTimeZone('UTC')); 
         $date->setTimezone(new DateTimeZone('America/New_York'));

         $end_time = $date->format('g:i A T');
         $end_date = $date->format('Y-m-d'); 

         $ebay_price = get_post_meta(get_the_ID(), 'ebay_price', true);
         $buy_now_price = get_post_meta(get_the_ID(), 'ebay_buy_now_price', true);

         if($buy_now_price || $ebay_price ) {          
             $url = get_post_meta(get_the_ID(), 'ebay_url', true);  
             $image_url = get_post_meta(get_the_ID(), 'ebay_image_url', true); 
             $high_res_image_url = get_high_res_ebay_image($image_url);                      
         } else {
             $discogs_price = get_post_meta(get_the_ID(), 'discogs_price', true);                              
             $url = get_post_meta(get_the_ID(), 'discogs_url', true);
             $image_url = get_post_meta(get_the_ID(), 'discogs_image_uri', true);
         }

       

     ?>

     <div class="image">
         <!-- eBay Image -->
         <?php
             // Get high-resolution image URL                                                    
                 
             if (!empty($high_res_image_url)) {
                     echo '<div class="mb-4 d-flex text-center">';
                     echo '<img src="' . esc_url($high_res_image_url) . '" alt="eBay Item Image" />';
                     echo '</div>';
             } else{
                 
                 if (has_post_thumbnail()) {
                     echo '<div class="mb-4 d-flex text-center">';
                     the_post_thumbnail('large');
                     echo '</div>';
                 } else {
                     echo '<div class="mb-4 d-flex text-center">';
                     echo '<img src="' . esc_url($image_url ) . '" alt="eBay Item Image" />';
                     echo '</div>';
                 }

             }                         
         ?>
    </div>
    <div class="pt-0 p-4">
            <!-- Content -->          
    
            <div class="post-content mb-4">   
                 <?php  
                 echo $ebay_price ? '<span class="fw-bold">Price: </span>' .  esc_html($ebay_price) . '<br><br>' : 
                     '<span class="fw-bold">Price: </span>' .  esc_html($discogs_price ? $discogs_price : '')  . '<br><br>'; 
                 ?>
                 <?php echo (is_numeric($bids)) ? '<span class="fw-bold">Bids: </span>' . $bids . '<br>' : ''; ?> 
                 <?php echo $buy_now_price ? '<span class="fw-bold">Buy Now Price: </span>' .  esc_html($buy_now_price) . '<br>' : ''; ?> 
                 <?php echo $date_string ? '<span class="fw-bold">End Date: </span>' . esc_html($end_date ? $end_date : '') . ' ' . esc_html($end_time ? $end_time : '') . '<br><br>' : ''; ?>

                 <!-- Buy Button -->
             
                 <div class="mb-5 d-flex">

                    <a href="<?php echo esc_url($url); ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener noreferrer">
                        <?php  
                            echo ($buy_now_price || $ebay_price) ? ($buy_now_price ? 'Bid Now' : 'Buy on Ebay') : "Buy on Discogs"; 
                        ?>
                    </a>
                    <?php if(is_user_logged_in()): ?>
                    <button class="add-to-wishlist"
                        data-type="post"        
                        data-item-id="<?php the_ID(); ?>"
                        data-title="<?php the_title(); ?>"
                        data-ebay-id="<?php echo esc_attr($ebay_item_id); ?>"
                        data-item-url="<?php echo esc_url($url); ?>"
                        data-image-url="<?php echo esc_url($image_url); ?>">
                        Add to Wishlist
                    </button>
                    <?php endif; ?>
                </div> 

                <?php the_content(); ?>

            </div>                        
       </div>
    </div>
                       
