<?php
/**
 * @package FBNotes
 * @version 1.0
 */
/*
Plugin Name: FBNotes
Description: Create posts from notes.
Author: Tope Olufon
Version: 1.0
*/
ini_set('user_agent', 'Mozilla/5.0');
require_once('twitter-api-php/TwitterAPIExchange.php');
require_once('simple_html_dom.php');
function createpostfromnote()
{
$settings = array(
    'oauth_access_token' => "token",
    'oauth_access_token_secret' => "token_secret",
    'consumer_key' => "consumer_key",
    'consumer_secret' => "consumer_secret"
);
$currdir = WP_PLUGIN_DIR."/fbnotes/tweets.txt";
$ids=array();
$sinceid = file_get_contents($currdir);
$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
$requestMethod = "GET";
$getfield = "?q=screen_name=handle&since_id=$sinceid";
$twitter = new TwitterAPIExchange($settings);
$twitter = new TwitterAPIExchange($settings);
$string = json_decode($twitter->setGetfield($getfield)
->buildOauth($url, $requestMethod)
->performRequest(),$assoc = TRUE);
if($string["errors"][0]["message"] != "") {echo "<h3>Sorry, there was a problem.</h3><p>Twitter returned the following error message:</p><p><em>".$string[errors][0]["message"]."</em></p>";exit();}
foreach($string as $items)
    {   
        $id = $items['id'];
        array_push($ids,$id);
        $thetweet = $items['text'];
        preg_match_all('!https?://\S+!', $thetweet, $matches);
        $thetweet = $matches[0][0];
        if ($thetweet != ""){
        $options  = array('http' => array('user_agent' => 'custom user agent string'));
        $context  = stream_context_create($options);
        $shortlink = $thetweet;
        $realLocation = get_headers($shortlink,1);
        $fulllink =  $shortlink;
        $html = file_get_html($fulllink);
         
        $page = $html->find('a[class=_2yug]', 0);
        $page = $page->plaintext;
            
        $html = $html->find('div[id=content]', 0);
        $title = $html->find('div[class=_4lmk _5s6c]', 0);
        $title =  "(".$page.")".$title->plaintext;
        $bgimg = $html->find('div[class=_5bdz]', 0);
        $bgimg = html_entity_decode($bgimg);
        preg_match('#\((.*?)\)#', $bgimg, $match);
        $bgimg= $match[1];
//file_put_contents(WP_PLUGIN_DIR."/".$title.".html", $bgimg);
        $bg = "<img class='noteheaderimg' src='$bgimg'>";
        $html = $html;
        foreach($html->find('div[class=_5bdz]') as $div) {
            $div->innertext = $bg;
        }
        $new_post = array(
            'post_title'    => $title,
            'post_content'  => $html,
            'post_status'   => 'draft',
            'post_date'     => date( 'Y-m-d H:i:s' ),
            'post_author'   => 'test',
            'post_type'     => 'post',
            'post_category' => array(0)
        );
        $post_id = wp_insert_post( $new_post );
        //  echo"
        // <script>
        //     console.log($id)
        // </script>";
        }
    }
if (!empty($ids)) {
    $maxid = max($ids);
    file_put_contents($currdir, $maxid);
}
}
add_action('init', 'createpostfromnote');
?>


<style>
.fb_content div{
background-repeat: no-repeat;
background-size: cover;
}

.noteheaderimg{
    width: 610px;
    height: 380px;
}

.fb_content ._2cuy{
    margin: 1em;
}

.fb_content ._5bdz{
/*height:100%;*/
}
._3uhg, ._39k2{display:none}
</style>


