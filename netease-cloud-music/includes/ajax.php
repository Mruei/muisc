<?php
add_action('wp_ajax_ncmu_local_song_action', 'ncmu_local_song_action');
add_action('wp_ajax_nopriv_ncmu_local_song_action', 'ncmu_local_song_action');
function ncmu_local_song_action() {
    check_ajax_referer('ncmu_ajax', 'nonce');
    $aid = $_POST['album'] ?? '';
    $idx = intval($_POST['idx'] ?? -1);
    $do  = $_POST['do'] ?? '';
    $albums = get_option('ncmu_local_albums', []);
    if (!isset($albums[$aid]['songs'][$idx])) wp_send_json_error(['msg'=>'not found']);
    switch ($do) {
        case 'like':
            $albums[$aid]['songs'][$idx]['likes'] = intval($albums[$aid]['songs'][$idx]['likes'])+1;
            break;
        case 'play':
            $albums[$aid]['songs'][$idx]['plays'] = intval($albums[$aid]['songs'][$idx]['plays'])+1;
            break;
        default: wp_send_json_error(['msg'=>'badop']);
    }
    update_option('ncmu_local_albums', $albums);
    wp_send_json_success([
        'likes'=>intval($albums[$aid]['songs'][$idx]['likes']),
        'plays'=>intval($albums[$aid]['songs'][$idx]['plays'])
    ]);
}