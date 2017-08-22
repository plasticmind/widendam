<?php
    
    $total_pages = ceil($total_results / $page_size);
    $next_page = $current_page + 1;
    $previous_page = $current_page - 1;
    
    if ($next_page < 1) { $next_page = 1; }
    if ($next_page > $total_pages) { $next_page = $total_pages; }
    
    $asset_range_low = 1 + (($current_page - 1) * $page_size);
    $asset_range_high = $asset_range_low + $page_size - 1;
    if ($asset_range_high > $total_results) { $asset_range_high = $total_results; }
    
    $prev_enabled = $current_page > 1;
    $next_enabled = $current_page < $total_pages;
        
    ?>

<br/>
<strong>
<?php echo($total_results) ?> Assets found for Search term
&quot<?php echo($queryResult->searchDescription) ?>&quot
</strong>

<div class="widen-media-nav">
    <span class="widen-nav-asset-range">
        Displaying Assets <?php echo($asset_range_low) ?> - <?php echo($asset_range_high) ?>

    </span>

<?php if ($prev_enabled) { ?>
    <a class='widen_nav_link' href="#" page="1" title="Go to the first page">«</a>
<?php }
    else
    { ?>
    <a class="disabled">«</a>
<?php } ?>

<?php if ($prev_enabled) { ?>
    <a class='widen_nav_link' href="#" page="<?php echo($previous_page) ?>" title="Go to the previous page">‹</a>
<?php }
    else
    { ?>
    <a class="disabled">‹</a>
<?php } ?>

    Page <?php echo($current_page) ?> of <?php echo($total_pages) ?>

<?php if ($next_enabled) { ?>
    <a class='widen_nav_link' href="#" page="<?php echo($next_page) ?>" title="Go to the next page" >›</a>
<?php }
    else
    { ?>
    <a class="disabled">›</a>
<?php } ?>

<?php if ($next_enabled) { ?>
    <a class='widen_nav_link' href="#" page="<?php echo($total_pages) ?>" title="Go to the last page" >»</a>
<?php }
    else
    { ?>
<a class="disabled">»</a>
<?php } ?>

</div>
<br/>
<br/>




