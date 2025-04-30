document.addEventListener('DOMContentLoaded', function() {
  // 只在专辑页激活
  if(!window.ncmLocalSongs) return;

  let songs = window.ncmLocalSongs, album = window.ncmLocalAlbumId;
  let playerBox = document.querySelector('.ncmusic-player');
  let audio = null;
  let curIdx = 0;

  // 点击行或播放按钮
  document.querySelectorAll('.ncmusic-row').forEach((row, idx)=>{
    row.addEventListener('click', function(e){
      if(e.target.classList.contains('ncmusic-like')) return;
      playSong(idx);
    });
    row.querySelector('.ncmusic-play-btn').onclick = (e) => {
      e.stopPropagation(); playSong(idx);
    };
    row.querySelector('.ncmusic-like').onclick = function(e){
      e.stopPropagation();
      ncmLocalSongAction('like', idx, res=>{
        if(res.success) this.querySelector('.ncmusic-like-num').textContent = res.data.likes;
        else alert(res.data.msg||'点赞失败');
      });
    };
  });

  function playSong(idx){
    curIdx = idx;
    let song = songs[idx];
    document.querySelectorAll('.ncmusic-row').forEach((tr,i)=>tr.classList.toggle('playing',i===idx));
    playerBox.style.display='block';
    playerBox.querySelector('.ncmusic-player-title').textContent = song.title;
    if(!audio) audio = playerBox.querySelector('#ncmusic-audio');
    audio.src = song.mp3;
    audio.play();
    playerBox.querySelector('#ncmusic-lyrics').innerHTML = '歌词加载中...';
    if(song.lrc && song.lrc.length>5) fetch(song.lrc).then(r=>r.text()).then(lrc=>renderLrc(lrc));
    else playerBox.querySelector('#ncmusic-lyrics').innerHTML = '<span style="color:#ccc;">暂无歌词</span>';
    audio.onplay = function(){
      if(!audio._played){ncmLocalSongAction('play', idx);audio._played=1;}
    };
  }

  function renderLrc(lrc){
    let lines = lrc.split('\n').map(line=>{
      let m = line.match(/\[(\d+):(\d+)(?:\.(\d+))?\](.*)/);
      if(!m) return null;
      return {time:parseInt(m[1])*60+parseInt(m[2])+(parseInt(m[3]||0)/100), text:m[4]};
    }).filter(Boolean);
    let html = lines.map((l,i)=>`<div class="ncmusic-lrc-line" data-idx="${i}" data-time="${l.time}">${l.text}</div>`).join('');
    let lrcBox = playerBox.querySelector('#ncmusic-lyrics'); lrcBox.innerHTML = html;
    let lrcLines = lrcBox.querySelectorAll('.ncmusic-lrc-line');
    audio.ontimeupdate = function(){
      let cur = 0;
      for(let i=0;i<lines.length;i++) if(audio.currentTime>=lines[i].time) cur=i;
      lrcLines.forEach((el,i)=>el.classList.toggle('active',i===cur));
      let active = lrcBox.querySelector('.ncmusic-lrc-line.active');
      if(active) lrcBox.scrollTop = active.offsetTop - lrcBox.offsetTop - 40;
    };
  }

  // 关闭播放器
  playerBox.querySelector('.ncmusic-player-close').onclick = ()=>{playerBox.style.display='none';};
  // 自动播放第一首
  playSong(0);

  function ncmLocalSongAction(act, idx, cb){
    jQuery.post(NCMU_AJAX.ajaxurl, {
      action:'ncmu_local_song_action',
      nonce:NCMU_AJAX.nonce,
      album:album,
      idx:idx,
      do:act
    }, function(res){if(cb) cb(res);});
  }
});