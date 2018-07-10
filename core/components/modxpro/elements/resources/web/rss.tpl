<div id="rss-config">
    <div class="input-group">
        <input type="text" class="form-control" id="rss-input" value="{'site_url' | config}rss">
        <div class="input-group-append">
            <button class="input-group-text" name="copy"><i class="far fa-copy"></i></button>
            <button class="input-group-text" name="link"><i class="far fa-share"></i></button>
        </div>
    </div>
    <small>
        {if $.en}
            Copy and paste the link into your RSS reader. We recommend
        {else}
            Скопируйте и вставьте ссылку в свой RSS ридер. Мы рекомендуем
        {/if}
        <a href="https://www.inoreader.com/" target="_blank">InoReader</a>.
    </small>


    <div class="mt-5">
        {if $.en}
            By default, all blogs are displayed, but you can specify only the required ones.
        {else}
            По умолчанию выводятся все блоги, но вы можете указать только нужные.
        {/if}
    </div>
    {var $res = 'community/section/getlist' | processor : [
        'limit' => 0,
        'tpl' => '@FILE chunks/sections/rss.tpl',
    ]}
    {$res.results}
    {'<script>requirejs(["app/rss"]);</script>' | htmlToBottom}
</div>