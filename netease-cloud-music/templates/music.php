<?php
$local_albums = get_option('ncmu_local_albums', []);
$per_page = max(1,intval(get_option('ncmu_per_page',12)));
$page = max(1, intval($_GET['ncmu_page'] ?? 1));
$total_local = count($local_albums);
$local_list = array_slice($local_albums, ($page-1)*$per_page, $per_page);
$total_pages = max(ceil($total_local/$per_page),1);
?>
<div class="ncmusic-index">
    <div class="ncmusic-list-wrap">
        <?php foreach ($local_list as $aid=>$alb): ?>
            <div class="ncmusic-album-card">
                <?php if($alb['cover']): ?><div class="ncmusic-album-card-cover"><img src="<?=esc_url($alb['cover'])?>" alt=""></div><?php endif;?>
                <div class="ncmusic-album-card-desc"><?=esc_html($alb['desc'])?></div>
                <a class="ncmusic-btn" href="?local_album_id=<?=$aid?>">进入专辑</a>
            </div>
        <?php endforeach;?>
    </div>
    <?php if($total_pages>1): ?>
    <nav class="ncmusic-pagination">
        <?php for($i=1;$i<=$total_pages;$i++):?>
            <a class="<?=($page==$i?'current':'')?>" href="?ncmu_page=<?=$i?>"><?=$i?></a>
        <?php endfor;?>
    </nav>
    <?php endif;?>
</div>