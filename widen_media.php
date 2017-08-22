<div id="widen_media_loading" style="display:none;"></div>
<div id="widen_add_to_library_success" class="updated" style="display:none;"><p><strong>File Added to Media Library</strong></p></div>
<?php
    //session_start();
    wp_register_style('widen_css', plugins_url('css/widen.css',__FILE__ ));
    wp_enqueue_style('widen_css');
    
    wp_enqueue_script(
                      "widen_media",
                      plugins_url('js/widen_media.js', __FILE__),
                      "jquery"
                      );
    ?>

<div id="widen_media_body" class="wrap">
    <div id="icon-upload" class="icon32"></div>

    <h2>Search for Assets in the Widen DAM</h2>
    <br/>

    <?php include("widen_searcher.php"); ?>

    <br/>
    <br/>
    <div id="widen_search_results" />

</div>




