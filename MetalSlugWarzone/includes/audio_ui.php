<?php
declare(strict_types=1);
require_once __DIR__.'/audio.php';
require_once __DIR__.'/audio_context.php';

/** One global, image-free sound dock. All JSON is HTML attribute escaped. */
function msw_audio_controls(): void {
    $data=msw_audio_bootstrap(msw_user());
    $manifest=require __DIR__.'/../config/audio.php';
    foreach(['tracks','effects'] as $kind){
        $data[$kind]=[];
        foreach($manifest[$kind] as $id=>$entry){
            $data[$kind][$id]=['url'=>msw_url($entry['file']),'title'=>$entry['title'],'duration'=>$entry['duration']??0];
        }
    }
    $data['context']=msw_audio_page_context();
    ?>
    <aside class="msw-audio" data-msw-audio="<?=msw_e(json_encode($data,JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE))?>" aria-label="Game sound controls">
      <section class="msw-audio-panel" id="msw-audio-panel" data-audio-panel role="dialog" aria-modal="false" aria-labelledby="msw-audio-title" hidden>
        <div class="msw-audio-head"><div><strong id="msw-audio-title">WARZONE AUDIO</strong><small>MUSIC &amp; BATTLE EFFECTS</small></div><button type="button" data-audio-close aria-label="Close sound controls">Close</button></div>
        <div class="msw-audio-now"><b data-audio-track>Warzone audio</b><small data-audio-status role="status">Loading sound settings…</small></div>
        <label for="msw-audio-seek">Track position</label><input id="msw-audio-seek" data-audio-seek type="range" min="0" max="1" step="0.1" value="0" disabled><div class="msw-audio-time" data-audio-time>0:00 / 0:00</div>
        <div class="msw-audio-actions"><button type="button" data-audio-play>Pause music</button><button type="button" data-audio-mute-panel aria-pressed="false">Mute all</button></div>
        <label for="msw-audio-music">Music level <output for="msw-audio-music" data-audio-music-value>55%</output></label><input id="msw-audio-music" data-audio-music type="range" min="0" max="100" step="1" value="55">
        <label for="msw-audio-effects">Effects level <output for="msw-audio-effects" data-audio-effects-value>75%</output></label><input id="msw-audio-effects" data-audio-effects type="range" min="0" max="100" step="1" value="75">
        <div class="msw-audio-actions"><button type="button" data-audio-test>Test effect</button><button type="button" data-audio-retry>Retry audio / save</button></div>
        <p class="msw-audio-save" data-audio-save role="status">Loading account preferences…</p>
      </section>
      <div class="msw-audio-dock"><button type="button" data-audio-toggle aria-expanded="false" aria-controls="msw-audio-panel">Sound <span data-audio-badge>ON</span></button><button type="button" data-audio-mute aria-pressed="false">Mute all</button></div>
    </aside>
    <script src="<?=msw_e(msw_asset_url('assets/js/audio.js'))?>"></script>
    <?php
}
