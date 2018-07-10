{extends 'file:chunks/email/default.tpl'}
{block 'content'}
    {var $link = ('site_url' | config) ~ $topic.uri}
    {if $.en}
        <p>A new comment in the topic <a href="{$link}"><b>{$topic.pagetitle}</b></a> from <b>{$user.fullname}</b>:</p>
        <pre>{$comment.raw | strip_tags : '<br>' | esc | truncate : 1000}</pre>
        <p>You can reply to the comment by <a href="{$link}#reply/{$comment.id}">following this link</a>.</p>
    {else}
        <p>Новый комментарий в теме <a href="{$link}"><b>{$topic.pagetitle}</b></a> от <b>{$user.fullname}</b>:</p>
        <pre>{$comment.raw | strip_tags : '<br>' | esc | truncate : 1000}</pre>
        <p>Вы можете ответить на него, пройдя <a href="{$link}#reply/{$comment.id}">по этой ссылке</a>.</p>
    {/if}
{/block}