{extends 'file:chunks/email/default.tpl'}
{block 'content'}
    {var $link = ('site_url' | config) ~ $section.alias}
    {if $.en}
        <p>
            User <strong>{$user.fullname}</strong> have published a new topic "<a href="{$link}">{$topic.pagetitle}</a>"
            in the section <a href="{$link}/{$topic.id}">{$section.pagetitle}</a>:
        </p>
        <pre>{$topic.content | strip_tags : '<br>' | esc | truncate : 1000}</pre>
        <a href="{$link}/{$topic.id}">Read by link</a>
    {else}
        <p>
            Пользователь <strong>{$user.fullname}</strong> опубликовал новую заметку "<a href="{$link}/{$topic.id}">{$topic.pagetitle}</a>"
            в секции "<a href="{$link}">{$section.pagetitle}</a>":
        </p>
        <pre>{$topic.content | strip_tags : '<br>' | esc | truncate : 1000}</pre>
        <a href="{$link}/{$topic.id}">Прочитать по ссылке</a>
    {/if}
{/block}