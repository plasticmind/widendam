<?php

define('WIDEN_CLIENT_REGISTRATION', 'faa9c149bdcdfefc08231710cdfe4854643ac7ab.app.widen.com');

function widendam_api_get_login_link($returnLink)
{
    $collectiveDomain = get_option('widen_domain');
    return "https://" . $collectiveDomain . "/allowaccess?client_id=" . WIDEN_CLIENT_REGISTRATION . "&redirect_uri=" . $returnLink;
}

function widendam_api_authenticate($authCode)
{
    $collectiveDomain =get_option('widen_domain');
    $endpoint = 'https://' . $collectiveDomain . '/api/rest/oauth/token';

    $data = "{\n    \"authorization_code\": \"".$authCode."\",\n    \"grant_type\" : \"authorization_code\"\n}";
    $auth = 'Basic ' . base64_encode(widendam_wordpress_client_registration());

    $options = array(
        'body' => $data,
        'timeout' => 60,
        'headers' => array('Content-Type' => 'application/json', 'Authorization' => $auth));

    return wp_remote_post($endpoint, $options);
}

function widendam_api_deauthorize()
{
    return widendam_api_call("oauth/logout");
}

function widendam_api_search_by_expression($searchTerm, $start, $max)
{
    $parms = "?start=" . $start . "&max=" . $max . "&options=embedCodes,downloadUrl";
    return widendam_api_call("asset/search/" . $searchTerm . "/" . $parms);
}

function widendam_api_search_by_uuid($uuid)
{
    return widendam_api_call("asset/uuid/" . $uuid);
}

function widendam_api_get_embed_codes($uuid)
{
    return widendam_api_call("asset/uuid/" . $uuid . "/embed/code");
}

function widendam_api_call($method)
{
    $accessToken = widendam_get_access_token();
    $endpoint = widendam_get_endpoint($method);

    if (isset($accessToken) && isset($endpoint))
    {
        $auth = 'Bearer ' . $accessToken;

        $options = array('headers' => array('Authorization' => $auth), 'timeout' => 60);

        $response = wp_remote_get($endpoint, $options);

        if (is_wp_error($response))
        {
            echo('Error Making API call: ' . $response->get_error_message());
            return null;
        }

        $http_status = $response['response']['code'];
        if ($http_status != '200')
        {
            echo('Error Response from API call [' . $http_status . '] ');
        }

        return json_decode($response['body']);
    }
}

function widendam_get_endpoint($method)
{
    $collectiveDomain =  get_option('widen_domain');
    if (isset($collectiveDomain))
    {
        return "https://" . $collectiveDomain . "/api/rest/" . $method;
    }
}

function widendam_get_access_token()
{
    return get_option('widen_access_token');
}

function widendam_wordpress_client_registration()
{
    // Client Registration with Widen Collective is the same for all Wordpress plugins
    return WIDEN_CLIENT_REGISTRATION . ':' . '7bd607a027ff3dad67566fe789a9a1dce0e154eb';
}

function widendam_validate_collective_ping()
{
    $collectiveDomain = get_option('widen_domain');
    $endpoint = 'https://' . $collectiveDomain . '/collective.ping';

    $response = wp_remote_get($endpoint);
    return $response['response']['code'];
}

function widendam_validate_rest_post()
{
    $collectiveDomain = get_option('widen_domain');
    $endpoint = 'https://' . $collectiveDomain . '/api/rest/oauth/prelogin';

    $response = wp_remote_post($endpoint);
    return $response['response']['code'];
}
