{foreach $results as $item}
    <div class="comment-row">
        <div class="d-flex no-gutters align-items-center">
            {$item | avatar : 25}
            <div class="ml-2 created">
                <div class="author">
                    <a href="/users/{$item.usename ? $item.username : $item.createdby}">{$item.fullname}</a>
                </div>
                <div class="date">{$item.createdon | dateago}</div>
            </div>
        </div>
        <a href="/{$item.uri}#comment-{$item.id}" class="link">
            <div class="text">
                {$item.content | strip_tags | escape | truncate : 150}
            </div>
        </a>
        <div class="d-flex align-items-center meta">
            <i class="far fa-file mr-1"></i>
            {$item.pagetitle | truncate : 100}
            {if strpos($item.uri, 'work') !== 0}
                <i class="far fa-comment ml-auto"></i><span class="ml-1">{$item.comments}</span>
            {/if}
        </div>
    </div>
{/foreach}