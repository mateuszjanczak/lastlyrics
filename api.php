<?php

$json = array();

if(isset($_GET['user']) && $_GET['user'] != "")
{
    $user = $_GET['user'];
    getRecentTrack($user);
    header('Content-Type: application/json');
    echo json_encode($json);
}

function curl($url, $cookie){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if(isset($cookie))
    {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    $result = curl_exec($ch);
    if(curl_errno($ch))
    {
        echo 'Error:'.curl_error($ch);
    }
    curl_close($ch);
    return $result;
}

function getRecentTrack($user){
    $url = "https://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=$user&api_key=7ba451ac68f69c472dc036b5a7bd8306&format=json";
    $getdata = curl($url, 0);
    $data = json_decode($getdata, true);
    global $json;
    getProfile($user);
    if(isset($data['recenttracks']['track']['0']['@attr']['nowplaying']))
    {
        $json['scrobblingnow'] = true;
        $json['artist'] = $artist = $data['recenttracks']['track']['0']['artist']['#text'];
        //$json['title'] = $title = $data['recenttracks']['track']['0']['name'];
        $json['title'] = $title = preg_replace("/\([^)]+\)/", "", $data['recenttracks']['track']['0']['name']);
        if(!isset($_GET['readonly']) && $_GET['user'])
        {
            getGenius($artist, $title);
        }
    }
    else
    {
        $json['scrobblingnow'] = false;
    }
}

function getProfile($user){
    $url = "https://ws.audioscrobbler.com/2.0/?method=user.getInfo&user=$user&api_key=7ba451ac68f69c472dc036b5a7bd8306&format=json";
    $getdata = curl($url, 0);
    $data = json_decode($getdata, true);
    global $json;
    $json['user'] = $data['user']['name'];
    $json['count'] = $data['user']['playcount'];
    $json['avatar'] = $data['user']['image'][1]["#text"];
}

function getGenius($artist, $title){
    $query = urlencode($artist.' '.$title);
    $url = "https://api.genius.com/search?access_token=8JEddJLmToPqzJXlTN-dTMdvZDubK16xQ_jSls01TwLgddmruFL1iDbjgGkRNvIp&q=".urlencode($artist.' '.$title);
    $getdata = curl($url, 0);
    $data = json_decode($getdata, true);
    $genius = $data['response']['hits']['0']['result']['id'] ?? 'nobody';
    getLyrics($genius);
}

function getLyrics($id){
    $url = "https://genius.com/api/songs/".$id."/lyrics_for_edit_proposal";
    $cookie = "_rapgenius_session=BAh7CToPc2Vzc2lvbl9pZEkiJTgyMmNjOTMzYzQ3ZTk1ZjI1MGE1ZTRjZTExNThkZTZmBjoGRUY6EF9jc3JmX3Rva2VuSSIxUDQ1Nk9tM1UxaFNUYStCdFVzem10TTA4NkoyUkhtbzkyWVkyeGZPaWlaaz0GOwZGSSIVdXNlcl9jcmVkZW50aWFscwY7BlRJIgGAZjljOWQ1NGFhYmM3NTdjODAyMjc0YWFjZjliYmM2MjM3OTkwMjAxOWE3NTBlOTQ0YjQ0ZGFhYzA5YTlkNTc0NWI5OGM5YTczNWRjZmRiZWZiYjhjMDNmNmEzODMzNzMzNmViNDkxYjlkNTU3MzFiNTlhM2FlNTVmNzJlOGJlZmQGOwZUSSIYdXNlcl9jcmVkZW50aWFsc19pZAY7BlRpA1i7aA%3D%3D--f8b09e8e3117f698d56c642a0a0f6766d2c2c73e";
    $getdata = curl($url, $cookie);
    $data = json_decode($getdata, true);
    global $json;
    $json['lyrics'] = $data['response']['lyrics_for_edit_proposal']['body']['plain'] ?? false;
}

?>