{if !$item.id}
<div class="comments-list">
    {/if}
    <div class="comment-row{if $item.createdby == $topic.createdby} author{/if}">
        <div class="comment-wrapper">
            <div class="comment-meta d-flex flex-wrap no-gutters align-items-center item-data">
                <div class="col-12 col-md-6 d-flex align-items-center justify-content-center justify-content-md-start">
                    {$item | avatar : 30}
                    <div class="ml-2 created">
                        <div class="author">
                            <a href="/users/{$item.usename ? $item.username : $item.createdby}">{$item.fullname}</a>
                        </div>
                        <div class="date">{$item.createdon | dateago}</div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mt-2 mt-md-0 col-md-3 ml-md-auto d-flex justify-content-around justify-content-md-end">
                    <div class="d-flex">
                        <div class="link">
                            <i class="far fa-hashtag"></i>
                        </div>
                        <div class="star ml-3{if $item.star} active{/if}">
                            <div> {$item.stars ?: ''}</div>
                        </div>
                    </div>
                    <div class="ml-md-5">
                        <div class="rating">
                            <i class="far fa-arrow-up mr-2 disabled"></i>
                            {if $item.rating > 0}
                                <span class="placeholder positive">+{$item.rating}</span>
                            {elseif $item.rating < 0}
                                <span class="placeholder negative">{$item.rating}</span>
                            {else}
                                <span class="placeholder">{$item.rating}</span>
                            {/if}
                            <i class="far fa-arrow-down ml-2 disabled"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="comment-text mt-2">
                {$item.content | jevix | prism}
            </div>
        </div>
    </div>
    {if !$item.id}
</div>
{/if}