{var $res = 'community/comment/getlist' | processor : [
    'limit' => 20,
]}

{include 'file:chunks/_banner.tpl'}
<div class="comments-list">
    <h4 class="section-title">
        {if $.en}
            Total {$res['total'] | number} {$res['total'] | declension : 'comment|comments'}
        {else}
            Всего {$res['total'] | number} {$res['total'] | declension : 'комментарий|комментария|комментариев'}
        {/if}
    </h4>
    {$res.results}

    {include 'file:chunks/_pagination.tpl' res=$res}
</div>