<?php

/**
 * Downloads and adds file from the given url, and adds it to the WordPress media library.
 * Dies on exit.
 *
 * POST parameters:
 *  urlToAdd (required):        Downloads file from this location to add to media library
 *  filenameToAdd (required):   File will be added to library with this filename
 *  pid (optional):             File will be associated with this page/post if present
 *  caption (optional):         Text from Widen metadata to be used as image caption
 *
 * Returns:
 *   String with redirect link to the Media Library page
 *
 */
function widen_addToMediaLibrary ()
{
    require_once(ABSPATH . 'wp-load.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $newFilename = $_POST['filenameToAdd'];
    $fileUrl = $_POST['urlToAdd'];
    $postId = get_post_id();

    if (isset($_POST['description']))
    {
        $description = $_POST['description'];
    }

    $uuid = $_POST['uuid'];
    // TODO: Get Description metadata from below call
    // $asset = widendam_api_search_by_uuid($uuid);
    $description = "";

    global $wpdb;

    //directory to import to
    $artDir = 'wp-content/uploads/importedmedia/';

    //if the directory doesn't exist, create it
    if(!file_exists(ABSPATH.$artDir))
    {
        mkdir(ABSPATH.$artDir);
    }

    if (@fclose(@fopen($fileUrl, "r")))
    {
        //make sure the file actually exists
        copy($fileUrl, ABSPATH.$artDir.$newFilename);


        $siteurl = get_option('siteurl');
        $file_info = getimagesize(ABSPATH.$artDir.$newFilename);

        //create an array of attachment data to insert into wp_posts table
        $artdata = array();
        $artdata = array(
            'post_author' => 1,
            'post_date' => current_time('mysql'),
            'post_date_gmt' => current_time('mysql'),
            'post_title' => $newFilename,
            'post_status' => 'inherit',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_name' => sanitize_title_with_dashes(str_replace("_", "-", $newFilename)),
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql'),
            'post_parent' => $postId,
            'post_type' => 'attachment',
            'guid' => $siteurl.'/'.$artDir.$newFilename,
            'post_mime_type' => $file_info['mime'],
            'post_content' => $description
        );

        $uploads = wp_upload_dir();
        $save_path = $uploads['basedir'].'/importedmedia/'.$newFilename;

        //insert the database record
        $attach_id = wp_insert_attachment( $artdata, $save_path, $postId );

        //generate metadata and thumbnails
        if ($attach_data = wp_generate_attachment_metadata( $attach_id, $save_path))
        {
            wp_update_attachment_metadata($attach_id, $attach_data);
        }
    }

    echo ('redirect='.admin_url('upload.php'));
    die();
}
add_action( 'wp_ajax_widen_addToMediaLibrary', 'widen_addToMediaLibrary' );


/**
 * Makes DamQuery API call to Widen Collective, renders one page of search results
 * Dies on exit.
 *
 * POST parameters:
 *  term (required):            The search term as entered by the user
 *  paged (optional):           The page number of the search results to obtain
 *  pid (optional):             Page/Post the user was editing if user got here from Edit page
 *
 * Returns:
 *   html markup displaying the relevant search results
 *
 */

function widen_search()
{
    $query = $_POST['term'];
    $query = str_replace(' ', '%20', $query);

    $postId = get_post_id();
    $current_page = 1;
    if (isset($_POST['paged']))
    {
        $current_page = $_POST['paged'];
    }
    $page_size = 20;

    $offset = ($current_page - 1) * $page_size;

    $queryResult = widendam_api_search_by_expression($query, $offset, '20');
    $total_results = $queryResult->numResults;

    ?>

    <div id="resultsWindow">
        <?php
        if ($total_results > 0)
        {
            include("widen_media_nav.php");
            $assetArray = $queryResult->assets;
            $assetCounter=0;
            ?>
        <div class="resultRow">
            <table class="wp-list-table widefat fixed media">
                <thead><th class="widen-thumbnail-column"></th>
                <th>File</th>
                <th width="400px">Embed Links</th>
                <th width="150px"></th>
                </thead>
                <?php
                foreach ($assetArray as $asset) {

                $assetCounter++;

                $filename = $asset->name;
                $uuid = $asset->uuid;
                $fileType = $asset->type;
                $thumbnail = $asset->previews->preview125;
                $hover = $asset->previews->preview300;
                $originalUrl = $asset->downloadUrl;
                $libraryUrl = $asset->previews->preview2048;

                $detailsUrl = $asset->detailsUrl;

                if ($assetCounter % 2 == 0) {
                    ?>
                <tr>
                    <?php
                    }
                    else {
                    ?>
                <tr class="alternate">
                    <?php
                    }
                    ?>
                    <td class="widen-thumbnail-column" style="padding-top:15px; padding-bottom:15px;">
                        <!-- a href="javascript:void(0)" class="widen-thumbnail-link" id="<?php echo($detailsUrl) ?> " -->
                        <img id="widen_thumbnail_<?php echo($assetCounter) ?>"
                             class="widen-thumbnail"
                             src="<?php echo($thumbnail); ?>"
                             alt="Image Not Available"/>
                        <!-- /a -->
                    </td>

                    <td valign="top" style="padding-top:15px; padding-bottom:15px;">

                        <div id="widen_large_preview_<?php echo($assetCounter) ?>"
                             style="display: none; position: absolute; z-index: 110; left: 40; top: 10;" >
                            <img src="<?php echo($hover); ?>"
                                 alt="Image Not Available"/>
                        </div>



                        <strong><?php echo($filename); ?></strong>
                        <br/>
                        <!--
                        <a href="<?php echo($detailsUrl); ?>" target="_blank">link</a>
                        <br/>
                        -->

                        <?php echo($fileType); ?>
                        <br/><br/>


                        <a href="<?php echo($originalUrl); ?>">&nbsp;Download</a>

                        <form class="widen_add_to_media_library_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                            <input type="hidden" name="urlToAdd" value="<?php echo($libraryUrl) ?>" />
                            <input type="hidden" name="filenameToAdd" value="<?php echo($filename) ?>" />
                            <input type="hidden" name="uuid" value="<?php echo($uuid) ?>" />
                            <input type="submit" value="Add to Wordpress Media Library" class="widen-link-submit" />
                        </form>

                    </td>
                    <td valign="top" text-align="left" width="400px" style="padding-top:15px; padding-bottom:15px;">
                        <?php
                        if (has_embed_codes($asset))
                        {
                            ?>
                        <select id="embed_select_<?php echo($assetCounter) ?>" class="widen_embed_select">
                            <?php

                            $firstCode = null;
                            reset($asset->embedCodes);
                            while (list($key, $val) = each($asset->embedCodes))
                            {
                                if (!isset($firstCode))
                                {
                                    $firstCode = $val;
                                }
                                ?>
                                <option value="<?php echo(htmlspecialchars($val)) ?>"><?php echo(htmlspecialchars($key)) ?></option>
                            <?php
                            }

                                ?>
                        </select>

                        <textarea id="embed_textarea_<?php echo($assetCounter) ?>" readonly rows="3" cols="55" style="float:left;"><?php echo($firstCode); ?></textarea>
                        <?php
                        }
                            ?>
                    </td>

                    <td valign="bottom" text-align="left" width="150px" style="padding-top:15px; padding-bottom:15px; vertical-align:bottom">

                        <?php
                        if (has_embed_codes($asset) && ($postId > 0))
                        {
                            ?>

                        <form class="widen_embed_in_post" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                            <input type="hidden" name="codeTextId" value="embed_textarea_<?php echo($assetCounter); ?>" />
                            <input type="hidden" name="filenameToAdd" value="<?php echo($asset->filename) ?>" />
                            <input type="submit" value="Embed in Post" class="widen-link-submit widen_insert" />
                        </form>

                        <?php
                        }
                            ?>
                    </td>

                <?php
                    if($assetCounter==$page_size)
                    {
                    break;
                        ?>

                <div style="clear:both"></div>
        </div>
        <div class="resultRow">

            <?php
            }
        }
                ?>
            </tr>
            </table>
        </div>
        <div style="clear:both"></div>
    </div>
<?php
    }
    include("widen_media_nav.php");
    die();
}
add_action( 'wp_ajax_widen_search', 'widen_search' );

function get_post_id()
{
    if (isset($_POST['post_id']))
    {
        return $_POST['post_id'];
    }

    return 0;
}

function has_embed_codes($asset)
{
    reset($asset->embedCodes);
    while (list($key, $val) = each($asset->embedCodes))
    {
        if (isset($key) && (strlen($key) != 0))
        {
            return true;
        }
    }

    return false;
}
    ?>
