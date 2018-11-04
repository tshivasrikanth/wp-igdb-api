<div class="rbbwrap">
    <div class="pagination tablenav">
        <h1 class="productstit">IGDB Products</h1>
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo $totalItems; ?> items</span>
            <span class="pagination-links">

                <?php if($curpage != $startpage){ ?>
                    <a class="first-page" href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=igdb_products_rbb&paged=<?php echo $startpage; ?>">
                        <span class="screen-reader-text">First page</span>
                        <span aria-hidden="true">«</span>
                    </a>
                <?php }else{ ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                <?php } ?>

                <?php if($curpage >= 2){ ?>
                    <a class="first-page" href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=igdb_products_rbb&paged=<?php echo $previouspage; ?>">
                        <span class="screen-reader-text">Previous page</span>
                        <span aria-hidden="true">‹</span>
                    </a>
                <?php }else{ ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                <?php } ?>
                
                <span class="paging-input">
                    <label for="current-page-selector" class="screen-reader-text">Current Page</label>
                    <input class="current-page" id="current-page-selector" type="text" name="paged" value="<?php echo $curpage; ?>"
                        size="2" aria-describedby="table-paging">
                    <span class="tablenav-paging-text"> of
                        <span class="total-pages"><?php echo $endpage; ?></span>
                    </span>
                </span>

                <?php if($curpage != $endpage){ ?>
                    <a class="next-page" href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=igdb_products_rbb&paged=<?php echo $nextpage; ?>">
                    <span class="screen-reader-text">Next page</span>
                    <span aria-hidden="true">›</span>
                    </a>
                <?php }else{ ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                <?php } ?>

                <?php if($curpage != $endpage){ ?>
                    <a class="last-page" href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=igdb_products_rbb&paged=<?php echo $endpage; ?>">
                        <span class="screen-reader-text">Last page</span>
                        <span aria-hidden="true">»</span>
                    </a>
                <?php }else{ ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
                <?php } ?>
            </span>
        </div>
    </div>
    <br class="clear">
    <ul id="ebayproducts-list">
        <?php if(count($productResults)){?>
        <?php foreach($productResults as $productVal){ ?>
        <?php 
        $title = $productVal->name;
        $url = $productVal->url;
        $cover_url_id =$productVal->cover_url_id;
        $imageUrl = 'https://images.igdb.com/igdb/image/upload/t_thumb/'.$cover_url_id.'.jpg';
    ?>
        <li>
            <div class="ebayproductimg" style="background-image:url('<?php echo $imageUrl; ?>')"></div>
            <div class="ebayData">
                <div class="ebayproducttit">
                    <h3>
                        <a href="<?php echo $url; ?>" target="_blank">
                            <?php echo $title; ?>
                        </a>
                    </h3>
                </div>
            </div>
            <br class="clear">
        </li>
        <?php } ?>
        <?php }else{ ?>
        <li>No Products Found!</li>
        <?php } ?>

    </ul>
    <div class="pagination tablenav">
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo $totalItems; ?> items</span>
            <span class="pagination-links">

                <?php if($curpage != $startpage){ ?>
                    <a class="first-page" href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=igdb_products_rbb&paged=<?php echo $startpage; ?>">
                        <span class="screen-reader-text">First page</span>
                        <span aria-hidden="true">«</span>
                    </a>
                <?php }else{ ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                <?php } ?>

                <?php if($curpage >= 2){ ?>
                    <a class="first-page" href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=igdb_products_rbb&paged=<?php echo $previouspage; ?>">
                        <span class="screen-reader-text">Previous page</span>
                        <span aria-hidden="true">‹</span>
                    </a>
                <?php }else{ ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                <?php } ?>
                
                <span class="paging-input">
                    <label for="current-page-selector" class="screen-reader-text">Current Page</label>
                    <input class="current-page" id="current-page-selector" type="text" name="paged" value="<?php echo $curpage; ?>"
                        size="2" aria-describedby="table-paging">
                    <span class="tablenav-paging-text"> of
                        <span class="total-pages"><?php echo $endpage; ?></span>
                    </span>
                </span>

                <?php if($curpage != $endpage){ ?>
                    <a class="next-page" href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=igdb_products_rbb&paged=<?php echo $nextpage; ?>">
                    <span class="screen-reader-text">Next page</span>
                    <span aria-hidden="true">›</span>
                    </a>
                <?php }else{ ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                <?php } ?>

                <?php if($curpage != $endpage){ ?>
                    <a class="last-page" href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=igdb_products_rbb&paged=<?php echo $endpage; ?>">
                        <span class="screen-reader-text">Last page</span>
                        <span aria-hidden="true">»</span>
                    </a>
                <?php }else{ ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
                <?php } ?>
            </span>
        </div>
    </div>
</div>