<div class="topic-meta d-flex flex-wrap no-gutters align-items-center item-data" data-id="{$item.id}" data-type="topic">
    <div class="col-12 col-md-6 d-flex align-items-center justify-content-center justify-content-md-start">
        {if !$user}
            {$item | avatar : 48}
            <div class="ml-2 created">
                <div class="author">
                    <a href="/users/{$item.usename ? $item.username : $item.createdby}">{$item.fullname}</a>
                </div>
                <div class="date">{$item.createdon | dateago}</div>
            </div>
        {else}
            <div class="date">
                <i class="far fa-calendar-alt"></i> {$item.createdon | dateago}
            </div>
        {/if}
    </div>
    <div class="meta col-12 col-md-6 mt-3 mt-md-0 col-md-3 ml-md-auto d-flex justify-content-around justify-content-md-end">
        <div class="star{if $item.star} active{/if}">
            {if $_modx->isAuthenticated()}
                <a href="#">
                    <div> <span class="placeholder">{$item.stars ?: ''}</span></div>
                </a>
            {else}
                <div> {$item.stars ?: ''}</div>
            {/if}
        </div>
        <div class="views ml-md-3">
            <i class="far fa-eye"></i> {number_format($item.views, 0, ',', ' ')}
        </div>
        <div class="comments ml-md-3">
            {if strpos($item.uri, 'work') !== 0}
                {if !$_modx->resource.is_topic}
                    <a href="/{$item.uri}#comments">
                {/if}
                <i class="far fa-comment"></i> {$item.comments}
                {if !$_modx->resource.is_topic && $item.new_comments}
                    <span class="text-success"> +{$item.new_comments}</span>
                {/if}
                {if !$_modx->resource.is_topic}
                    </a>
                {/if}
            {/if}
        </div>
        <div class="rating ml-md-5">
            {if $item.can_vote}
                <a href="#" class="vote up{if $item.vote && $item.vote == 1} active{/if}" data-vote="up">
                    <i class="far fa-arrow-up mr-2"></i>
                </a>
            {else}
                <i class="far fa-arrow-up mr-2 disabled"></i>
            {/if}
            {if $_modx->isAuthenticated()}
                <a href="#" class="get_votes">
            {/if}
            {if $item.rating > 0}
                <span class="placeholder positive">+{$item.rating}</span>
            {elseif $item.rating < 0}
                <span class="placeholder negative">{$item.rating}</span>
            {else}
                <span class="placeholder">{$item.rating}</span>
            {/if}
            {if $_modx->isAuthenticated()}
                </a>
            {/if}
            {if $item.can_vote}
                <a href="#" class="vote down{if $item.vote && $item.vote == -1} active{/if}" data-vote="down">
                    <i class="far fa-arrow-down ml-2"></i>
                </a>
            {else}
                <i class="far fa-arrow-down ml-2 disabled"></i>
            {/if}
        </div>
    </div>
</div>