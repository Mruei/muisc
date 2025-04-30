<?php
add_action('admin_menu', function() {
    add_options_page('本地音乐Ultimate设置', '本地音乐', 'manage_options', 'ncmu-settings', 'ncmu_settings_page');
});
add_action('admin_init', function() {
    register_setting('ncmu_settings', 'ncmu_local_albums');
    register_setting('ncmu_settings', 'ncmu_bind_page');
    register_setting('ncmu_settings', 'ncmu_per_page');
});

function ncmu_settings_page() {
    $local_albums = get_option('ncmu_local_albums', []);
    $bind_page = get_option('ncmu_bind_page', '');
    $per_page = intval(get_option('ncmu_per_page', 12));
    if (!is_array($local_albums)) $local_albums = [];
    $pages = get_pages(['sort_order'=>'asc', 'sort_column'=>'post_title']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ncmu_settings_save'])) {
        check_admin_referer('ncmu_settings_nonce');
        update_option('ncmu_bind_page', intval($_POST['bind_page'] ?? 0));
        update_option('ncmu_per_page', max(1, intval($_POST['per_page'] ?? 12)));
        $album_titles = $_POST['album_title'] ?? [];
        $album_descs  = $_POST['album_desc'] ?? [];
        $album_covers = $_POST['album_cover'] ?? [];
        $song_titles  = $_POST['song_title'] ?? [];
        $song_files   = $_POST['song_mp3'] ?? [];
        $song_lrcs    = $_POST['song_lrc'] ?? [];
        $song_likes   = $_POST['song_likes'] ?? [];
        $song_plays   = $_POST['song_plays'] ?? [];
        $song_album   = $_POST['song_album'] ?? [];
        $albums = [];
        foreach ($album_titles as $ai=>$atitle) {
            if (!$atitle) continue;
            $albums[$ai] = [
                'title'=>sanitize_text_field($atitle),
                'desc'=>sanitize_textarea_field($album_descs[$ai]??''),
                'cover'=>esc_url_raw($album_covers[$ai]??''),
                'songs'=>[]
            ];
        }
        foreach ($song_titles as $si=>$stitle) {
            $ai = $song_album[$si];
            if (!isset($albums[$ai]) || !$stitle) continue;
            $albums[$ai]['songs'][] = [
                'title'=>sanitize_text_field($stitle),
                'mp3'=>esc_url_raw($song_files[$si]??''),
                'lrc'=>esc_url_raw($song_lrcs[$si]??''),
                'likes'=>intval($song_likes[$si]??0),
                'plays'=>intval($song_plays[$si]??0),
            ];
        }
        update_option('ncmu_local_albums', $albums);
        echo '<div class="updated"><p>保存成功！</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>本地音乐Ultimate设置</h1>
        <form method="post" action="">
        <?php wp_nonce_field('ncmu_settings_nonce'); ?>
        <input type="hidden" name="ncmu_settings_save" value="1">
        <div class="ncmu-box">
        <h2>页面绑定</h2>
        <div style="margin:12px 0;">
            <label>请选择一个WordPress页面作为音乐中心：</label>
            <select name="bind_page">
                <option value="0">-- 请选择页面 --</option>
            <?php foreach($pages as $p): ?>
                <option value="<?php echo $p->ID;?>"<?php selected($bind_page,$p->ID);?>><?php echo esc_html($p->post_title);?></option>
            <?php endforeach;?>
            </select>
            <span class="desc">如需新建页面请在"页面"中新建，保存后刷新本页</span>
        </div>
        <div style="margin:10px 0;">
            <label>每页显示专辑数量：</label>
            <input type="number" name="per_page" value="<?php echo $per_page;?>" min="1" max="100" style="width:70px">
        </div>
        </div>
        <div class="ncmu-box">
        <h2>本地专辑与歌曲管理</h2>
        <div class="ncmu-table-wrap">
        <table class="ncmu-table" id="ncmu-album-table">
            <thead><tr><th>专辑标题</th><th>简介</th><th>封面</th><th>操作</th></tr></thead>
            <tbody>
            <?php foreach ($local_albums as $aid=>$alb): ?>
                <tr>
                    <td><input type="text" name="album_title[<?php echo $aid;?>]" value="<?php echo esc_attr($alb['title']);?>"></td>
                    <td><textarea name="album_desc[<?php echo $aid;?>]"><?php echo esc_textarea($alb['desc']);?></textarea></td>
                    <td><input type="url" name="album_cover[<?php echo $aid;?>]" value="<?php echo esc_attr($alb['cover']);?>"></td>
                    <td><button type="button" class="button" onclick="this.closest('tr').remove();">删除</button></td>
                </tr>
            <?php endforeach;?>
            <tr>
                <td><input type="text" name="album_title[]" placeholder="新专辑"></td>
                <td><textarea name="album_desc[]"></textarea></td>
                <td><input type="url" name="album_cover[]" placeholder="封面URL"></td>
                <td></td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="button" onclick="ncmuAddRow('ncmu-album-table')">添加专辑</button>
        </div>
        <h3>歌曲列表</h3>
        <div class="ncmu-table-wrap">
        <table class="ncmu-table" id="ncmu-song-table">
            <thead><tr><th>所属专辑</th><th>歌名</th><th>本地MP3</th><th>本地LRC</th><th>点赞</th><th>播放</th><th>操作</th></tr></thead>
            <tbody>
            <?php foreach ($local_albums as $aid=>$alb): if(!empty($alb['songs'])) foreach($alb['songs'] as $song):?>
                <tr>
                    <td>
                        <select name="song_album[]">
                            <?php foreach($local_albums as $k=>$v): ?>
                                <option value="<?php echo $k;?>" <?php selected($aid, $k);?>><?php echo esc_html($v['title'] ?: '专辑'.$k);?></option>
                            <?php endforeach;?>
                        </select>
                    </td>
                    <td><input type="text" name="song_title[]" value="<?php echo esc_attr($song['title']);?>"></td>
                    <td><input type="url" name="song_mp3[]" value="<?php echo esc_attr($song['mp3']);?>"></td>
                    <td><input type="url" name="song_lrc[]" value="<?php echo esc_attr($song['lrc']);?>"></td>
                    <td><input type="number" name="song_likes[]" value="<?php echo intval($song['likes']??0);?>"></td>
                    <td><input type="number" name="song_plays[]" value="<?php echo intval($song['plays']??0);?>"></td>
                    <td><button type="button" class="button" onclick="this.closest('tr').remove();">删除</button></td>
                </tr>
            <?php endforeach; endforeach;?>
            <tr>
                <td>
                    <select name="song_album[]">
                        <?php foreach($local_albums as $k=>$v): ?>
                            <option value="<?php echo $k;?>"><?php echo esc_html($v['title'] ?: '专辑'.$k);?></option>
                        <?php endforeach;?>
                    </select>
                </td>
                <td><input type="text" name="song_title[]" placeholder="歌名"></td>
                <td><input type="url" name="song_mp3[]" placeholder="本地MP3"></td>
                <td><input type="url" name="song_lrc[]" placeholder="本地LRC"></td>
                <td><input type="number" name="song_likes[]" value="0"></td>
                <td><input type="number" name="song_plays[]" value="0"></td>
                <td></td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="button" onclick="ncmuAddRow('ncmu-song-table')">添加歌曲</button>
        </div>
        </div>
        <hr>
        <p><input type="submit" class="button-primary button-large" value="保存设置"></p>
        </form>
        <script>
        function ncmuAddRow(tableId) {
            var table = document.getElementById(tableId);
            var row = table.tBodies[0].rows[table.tBodies[0].rows.length-1].cloneNode(true);
            row.querySelectorAll('input,textarea').forEach(function(input){
                input.value='';
            });
            table.tBodies[0].appendChild(row);
        }
        </script>
        <style>
        .ncmu-box{background:#fff;border-radius:10px;padding:20px 18px 24px 18px;margin-bottom:36px;box-shadow:0 6px 24px #e8e9eb;}
        .ncmu-table-wrap{overflow-x:auto;}
        .ncmu-table{width:100%;min-width:800px;background:#fafbfc;border-radius:8px;}
        .ncmu-table th,.ncmu-table td{padding:7px 8px;}
        .ncmu-table input[type="text"],.ncmu-table input[type="url"],.ncmu-table input[type="number"],.ncmu-table textarea, .ncmu-table select{width:98%;border-radius:4px;}
        .ncmu-table th{background:#f0f2f5;}
        .ncmu-table tr:nth-child(even){background:#f6f8fa;}
        .desc{color:#888;font-size:.98em;}
        </style>
    </div>
    <?php
}