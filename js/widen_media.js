jQuery('#widen_search_results').on('click', '.widen-thumbnail-link', function()
{
    var details_url = this.id;

    var overlay;
    var container;
    var iframe;

    // iframe and overlay divs must be created
    var body = document.getElementsByTagName("body")[0];

    overlay = document.createElement("div");
    overlay.setAttribute("id", "widendam-overlay");
    overlay.setAttribute("style",
        "position: fixed;"+
            "top: 0px;"+
            "right: 0px;"+
            "bottom: 0px;"+
            "left: 0px;"+
            "z-index: 10000;"+
            "background-color: rgb(0,0,0);"+
            "background-color: rgba(0,0,0,0.5);"
    );

    var closeIcon = document.createElement("span");

    container = document.createElement("div");
    container.setAttribute("id", "widendam-container");
    container.setAttribute("style",
        "position: fixed;"+
            "top: 50px;"+
            "bottom: 50px;"+
            "left: 50px;"+
            "right: 50px;"+
            "background-color: rgb(255,255,255);"
    );

    iframe = document.createElement("iframe");
    iframe.src = details_url;
    iframe.style.position = "absolute";
    iframe.style.top = "0px";
    iframe.style.left = "0px";
    iframe.style.border = "0px";
    iframe.style.width = "100%";
    iframe.style.height = "100%";
    iframe.id = "widendam-iframe";

    container.appendChild(iframe);
    overlay.appendChild(container);
    body.appendChild(overlay);
});

jQuery(document).on('click', '#widendam-overlay', function()
{
    var overlay = jQuery('#widendam-overlay' );
    var container = jQuery('#widendam-container');
    var iframe = jQuery('#widendam-iframe');

    iframe.remove();
    container.remove();
    overlay.remove();
});

jQuery('#widen_searcher').submit(function()
{
    var qry = jQuery('#searchQuery').val();
    var fields = widen_prepareFields('widen_search');
    fields['term'] = qry;

    jQuery.ajax({
        type: "post",
        dataType: "html",
        url: "admin-ajax.php",
        data: fields,
        beforeSend: function()
        {
            jQuery('#widen_media_loading').show();
        },
        complete: function()
        {
            jQuery('#widen_media_loading').hide();
        },
        success: function(html)
        {
            jQuery("#widen_add_to_library_success").hide();
            jQuery("#widen_search_results").html(html);
        }
    });
    return false;
});

jQuery('#widen_search_results').on('change', '.widen_embed_select', function()
{
    var select_id = this.id
    var id_array = select_id.split('_');
    var id_number = id_array[id_array.length - 1];
    var text_area_id = '#embed_textarea_' + id_number;
    var text_area_value = this.value;
                    
    jQuery(text_area_id).val(text_area_value);
});

jQuery('#widen_search_results').on('mouseenter', '.widen-thumbnail', function()
{
    var thumbnail_id = this.id
    var id_array = thumbnail_id.split('_');
    var id_number = id_array[id_array.length - 1];
    var large_preview_id = '#widen_large_preview_' + id_number;

    jQuery(large_preview_id).show();
});

jQuery('#widen_search_results').on('mouseleave', '.widen-thumbnail', function()
{
    var thumbnail_id = this.id
    var id_array = thumbnail_id.split('_');
    var id_number = id_array[id_array.length - 1];
    var large_preview_id = '#widen_large_preview_' + id_number;

    jQuery(large_preview_id).hide();
});

jQuery('#widen_search_results').on('submit', '.widen_add_to_media_library_form', function(){ return widen_addToMedia(jQuery(this)) });
function widen_addToMedia(form){

    var fields = widen_prepareFields('widen_addToMediaLibrary');

    jQuery.each(form.serializeArray(), function(i, field) {
        fields[field.name] = field.value;
    })

    jQuery.ajax({
        type: "post",
        url: "admin-ajax.php",
        data: fields,
        beforeSend: function()
        {
            jQuery('#widen_media_loading').show();
        },
        complete: function() {},
        success: function(response)
        {
            jQuery("#widen_add_to_library_success").show();
            jQuery('#widen_search_results').show();
            jQuery('#widen_media_loading').hide();
        }
    });

    return false;
}

jQuery('#widen_search_results').on('submit', '.widen_embed_in_post', function(){ return widen_embedInPost(jQuery(this)) });
function widen_embedInPost(form)
{
    var fields = widen_prepareFields('widen_embedInPost');

    jQuery.each(form.serializeArray(), function(i, field) {
        fields[field.name] = field.value;
    })

    var text_area_id = '#' + fields["codeTextId"];
    var embed_code = jQuery(text_area_id).val();

    // Wordpress does not respond well to forced size attributes in img tags
    var clean = embed_code.replace(/height=\"\d*\"/i, '');
    clean = clean.replace(/width=\"\d*\"/i, '');

    var win = window.dialogArguments || opener || parent || top;
    win.send_to_editor(clean);

    return false;
}

jQuery('#widen_search_results').on('click', '.widen_nav_link', function() {return widen_pagedSearch(jQuery(this)) });
function widen_pagedSearch(link)
{
    var qry = jQuery('#searchQuery').val();
    var fields = widen_prepareFields('widen_search');
    fields['term'] = qry;
    fields['paged'] = link.attr('page');

    jQuery.ajax({
        type: "post",
        dataType: "html",
        url: "admin-ajax.php",
        data: fields,
        beforeSend: function()
        {
            jQuery('#widen_media_loading').show();
        },
        complete: function()
        {
            jQuery('#widen_media_loading').hide();
        },
        success: function(html)
        {
            jQuery("#widen_add_to_library_success").hide();
            jQuery("#widen_search_results").html(html);
        }
    });

}

function widen_prepareFields(action)
{
    var fields = {};
    fields['action']  = action;

    var url = document.URL.split("?");
    var queryString = url[1];

    if (queryString)
    {
        var pairs = queryString.split('&');
        for(i in pairs)
        {
            var split = pairs[i].split('=');
            fields[decodeURIComponent(split[0])] = decodeURIComponent(split[1]);
        }
    }

    return fields;
}
