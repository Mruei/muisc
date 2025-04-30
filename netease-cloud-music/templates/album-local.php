<?php
$aid = $_GET['local_album_id'] ?? '';
$local_albums = get_option('ncmu_local_albums', []);
$album = isset($local_albums[$aid]) ? $local_albums[$aid] : null;
if (!$album) { echo '<p>专辑不存在</p>'; return;}
?>
<div class="ncmusic-root">
  <div class="ncmusic-album-header">
    <img class="ncmusic-cover" src="<?php echo esc_url($album['cover']); ?>">
    <div class="ncmusic-meta">
      <h1><?php echo esc_html($album['title']); ?></h1>
      <div class="ncmusic-album-intro"><?php echo nl2br(esc_html($album['desc'])); ?></div>
    </div>
  </div>
  <div class="ncmusic-song-list">
    <table>
      <thead><tr><th>#</th><th>标题</th><th>操作</th></tr></thead>
      <tbody>
        <?php foreach ($album['songs'] as $idx=>$song): ?>
        <tr class="ncmusic-row" data-idx="<?php echo $idx; ?>" data-mp3="<?php echo esc_attr($song['mp3']); ?>" data-lrc="<?php echo esc_attr($song['lrc']); ?>">
          <td><?php echo sprintf("%02d", $idx+1); ?></td>
          <td class="ncmusic-title-cell"><?php echo esc_html($song['title']); ?></td>
          <td>
            <button class="ncmusic-play-btn" title="播放"><i class="ncm-icon ncm-icon-play"></i></button>
            <span class="ncmusic-like">❤ <span class="ncmusic-like-num"><?php echo intval($song['likes']); ?></span></span>
            <span class="ncmusic-play-num">▶<?php echo intval($song['plays']); ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="ncmusic-player" style="display:none">
    <div class="ncmusic-player-info">
      <span class="ncmusic-player-title"></span>
      <span class="ncmusic-player-close" title="关闭">×</span>
    </div>
    <audio id="ncmusic-audio" controls preload="auto"></audio>
    <div class="ncmusic-lyrics-wrap">
      <div class="ncmusic-lyrics" id="ncmusic-lyrics"></div>
    </div>
  </div>
</div>
<script>
window.ncmLocalSongs = <?php echo json_encode($album['songs']);?>;
window.ncmLocalAlbumId = '<?php echo esc_js($aid);?>';
</script>