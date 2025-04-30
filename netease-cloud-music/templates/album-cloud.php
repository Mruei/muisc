<?php
$cloud_cache = get_option('ncmu_cloud_cache', []);
$cid = $_GET['cloud_album_id'] ?? '';
$pl = $cloud_cache[$cid] ?? null;
// 兼容字符串/数组/空值
$vip_only_option = get_option('ncmu_vip_only', []);
if (is_string($vip_only_option)) {
    $vip_only = array_filter(array_map('trim', explode(',', $vip_only_option)));
} elseif (is_array($vip_only_option)) {
    if (count($vip_only_option) === 1 && strpos($vip_only_option[0], ',') !== false) {
        $vip_only = array_filter(array_map('trim', explode(',', $vip_only_option[0])));
    } else {
        $vip_only = array_filter(array_map('trim', $vip_only_option));
    }
} else {
    $vip_only = [];
}
$user_vip = is_user_logged_in() && (user_can(get_current_user_id(),'read') && get_user_meta(get_current_user_id(),'vip',true));
if (!$pl || (in_array($cid, $vip_only) && !$user_vip)) { echo '<p>歌单不存在或仅VIP可见</p>'; return;}
?>
<div class="ncm-album-single">
    <div class="ncm-album-head">
        <div class="ncm-album-cover"><img src="<?=esc_url($pl['cover'])?>" alt=""></div>
        <div class="ncm-album-info">
            <h1><?=esc_html($pl['name'])?></h1>
            <div class="ncm-album-desc"><?=nl2br(esc_html($pl['desc']))?></div>
        </div>
    </div>
    <div class="ncm-album-song-list">
        <ul class="ncm-playlist">
        <?php foreach ($pl['tracks'] as $idx=>$song): ?>
            <li class="ncm-playlist-item"
                data-idx="<?=$idx?>" 
                data-cloud="<?=$cid?>" 
                data-songid="<?=esc_attr($song['id'])?>" 
                data-lrc="<?=esc_attr($song['lrc'])?>">
                <span class="ncm-song-title"><?=esc_html($song['name'].' - '.implode('/',$song['ar']))?></span>
                <span class="ncm-song-meta">
                    <span class="ncm-like-btn" data-act="like">❤<span class="ncm-like-num"><?=intval($song['likes'])?></span></span>
                    <span class="ncm-play-num">▶<?=intval($song['plays'])?></span>
                </span>
            </li>
        <?php endforeach;?>
        </ul>
        <div id="ncm-player-container"></div>
        <script>
        window.ncmCloudSongs = <?php echo json_encode($pl['tracks']);?>;
        window.ncmCloudApi = '<?php echo esc_js(get_option('ncmu_cloud_api',''));?>';
        window.ncmCloudAlbumId = '<?php echo esc_js($cid);?>';
        </script>
    </div>
</div>