<?php 

include('../../../config.php');

include 'lib/EpiCurl.php';
include 'lib/EpiOAuth.php';
include 'lib/EpiTwitter.php';
include 'lib/secret.php';

$instances = $DB->get_recordset('block_instances', array('blockname' => 'elis2'));
	    foreach ($instances as $instance) {
	        $config = unserialize(base64_decode($instance->configdata));
	    }
	    
print_r($config);
die;	    

$twitterObj = new EpiTwitter($consumer_key, $consumer_secret);


$oauth_token = $_GET['oauth_token'];                

{
    $twitterObj->setToken($_GET['oauth_token']);
	$token = $twitterObj->getAccessToken();
	$twitterObj->setToken($token->oauth_token, $token->oauth_token_secret);	  	
	$_SESSION['ot'] = $token->oauth_token;
	$_SESSION['ots'] = $token->oauth_token_secret;    
    $twitterObj->setToken($_SESSION['ot'], $_SESSION['ots']);          
    $twitterInfo= $twitterObj->get_accountVerify_credentials();
	$twitterInfo->response;
	
	$username = $twitterInfo->screen_name;
	$profilepic = $twitterInfo->profile_image_url;

    set_config($twitterer.'_twitter_username',$twitterInfo->screen_name,'twitter'); 
    set_config($twitterer.'_twitter_ot',$_SESSION['ot'],'twitter'); 
    set_config($twitterer.'_twitter_ots',$_SESSION['ots'],'twitter'); 
    
    echo('username ' . $twitterInfo->screen_name . '<br />'); 
    echo($twitterer.'_twitter_ot ' . $_SESSION['ot'] . '<br />'); 
    echo($twitterer.'_twitter_ots '. $_SESSION['ots'] . '<br />'); 
    
    echo tweetit("{$twitterInfo->screen_name} Tweeter configured #kgv_lionel");
    
    global $CFG;
    
    redirect($CFG->wwwroot);
}

?> 