<?php
function ncmu_get_url($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function ncmu_cache_cron_interval($schedules) {
    $hours = max(1,intval(get_option('ncmu_cache_hours',24)));
    $schedules['ncmu_custom_cache'] = [
        'interval' => $hours*3600,
        'display' => "每{$hours}小时"
    ];
    return $schedules;
}
add_filter('cron_schedules', 'ncmu_cache_cron_interval');

add_action('ncmu_cron_refresh_cache', 'ncmu_refresh_cloud_cache');
if (!wp_next_scheduled('ncmu_cron_refresh_cache')) {
    $hours = max(1,intval(get_option('ncmu_cache_hours',24)));
    wp_schedule_event(time(), 'ncmu_custom_cache', 'ncmu_cron_refresh_cache');
}

function ncmu_refresh_cloud_cache() {
    $uid = get_option('ncmu_cloud_uid');
    $api = get_option('ncmu_cloud_api', 'http://127.0.0.1:3000');
    if(!$uid || !$api) return;
    $playlists = @json_decode(ncmu_get_url($api.'/user/playlist?uid='.$uid),1);
    if(!$playlists || !isset($playlists['playlist'])) return;
    $cache = [];
    foreach($playlists['playlist'] as $plist) {
        $detail = @json_decode(ncmu_get_url($api.'/playlist/detail?id='.$plist['id']),1);
        if(!$detail || !isset($detail['playlist'])) continue;
        $tracks = [];
        foreach($detail['playlist']['tracks'] as $s) {
            $lrc = @json_decode(ncmu_get_url($api.'/lyric?id='.$s['id']),1);
            $tracks[] = [
                'id'=>$s['id'],
                'name'=>$s['name'],
                'ar'=>array_map(function($a){return $a['name'];},$s['ar']),
                'al'=>$s['al']['name']??'',
                'cover'=>$s['al']['picUrl']??'',
                'lrc'=>$lrc['lrc']['lyric']??'',
                'likes'=>0,
                'plays'=>0
            ];
        }
        $cache[$plist['id']] = [
            'id'=>$plist['id'],
            'name'=>$plist['name'],
            'cover'=>$plist['coverImgUrl'],
            'desc'=>$plist['description']??'',
            'tracks'=>$tracks
        ];
    }
    update_option('ncmu_cloud_cache',$cache);
}