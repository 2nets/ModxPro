{var $auth = $_modx->isAuthenticated()}
{var $admin = $_modx->isMember('Administrator')}
{if $item.deleted && !$admin}
    <div id="comment-{$item.id}" class="comment-row deleted">
        <div class="comment-wrapper">
            <div class="comment-dot-wrapper">
                <div class="comment-dot"></div>
            </div>
            <div class="comment-text">
                {$.en ? 'This comment was deleted' : 'Это сообщение было удалено'}
            </div>
        </div>
    </div>
{else}
    {if $item.deleted}
        {var $class = ' deleted'}
    {else}
        {var $class = ''}
        {if $item.createdby == $topic.createdby}
            {var $class = $class ~~ 'author'}
        {/if}
        {if $seen && $item.createdon > $seen && $_modx->user.id != $item.createdby}
            {var $class = $class ~~ 'unseen'}
        {/if}
        {if $item.rating < -5}
            {var $class = $class ~~ 'bad bad5'}
        {elseif $item.rating < 0}
            {var $class = $class ~~ 'bad bad' ~ abs($item.rating)}
        {/if}
    {/if}
    <div id="comment-{$item.id}" class="comment-row{$class}">
        <div class="comment-wrapper">
            <div class="comment-dot-wrapper">
                <div class="comment-dot"></div>
            </div>
            <div class="comment-meta d-flex flex-wrap no-gutters align-items-center item-data" data-id="{$item.id}" data-type="comment">
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
                    <div class="d-flex mr-md-5">
                        <div class="goto">
                            {if $item.parent}
                                <span class="mr-3" data-dir="up" data-id="{$item.parent}">
                                    <i class="far fa-arrow-to-top"></i>
                                </span>
                            {/if}
                            {if $item.children}
                                <span class="mr-3" data-dir="down" data-id="0" style="display: none;">
                                    <i class="far fa-arrow-to-bottom"></i>
                                </span>
                            {/if}
                        </div>
                        <div class="link">
                            <a href="#comment-{$item.id}">
                                <i class="far fa-hashtag"></i>
                            </a>
                        </div>
                        <div class="star ml-3{if $item.star} active{/if}">
                            {if $auth}
                                <a href="#">
                                    <div> <span class="placeholder">{$item.stars ?: ''}</span></div>
                                </a>
                            {else}
                                <div> {$item.stars ?: ''}</div>
                            {/if}
                        </div>
                    </div>
                    <div class="ml-md-5">
                        <div class="rating">
                            {if $item.can_vote}
                                <a href="#" class="vote up{if $item.vote && $item.vote == 1} active{/if}" data-vote="up">
                                    <i class="far fa-arrow-up mr-2"></i>
                                </a>
                            {else}
                                <i class="far fa-arrow-up mr-2 disabled"></i>
                            {/if}
                            {if $auth}
                            <a href="#" class="get_votes">
                                {/if}
                                {if $item.rating > 0}
                                    <span class="placeholder positive">+{$item.rating}</span>
                                {elseif $item.rating < 0}
                                    <span class="placeholder negative">{$item.rating}</span>
                                {else}
                                    <span class="placeholder">{$item.rating}</span>
                                {/if}
                                {if $auth}
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
            </div>
            <div class="comment-text mt-2">
                {$item.content | escape | prism}
            </div>
            {if $auth}
                <div class="comment-footer d-flex flex-wrap justify-content-between mt-2">
                    {if !$item.deleted}
                        <div class="">
                            <a href="#reply/{$item.id}">
                                <i class="far fa-pencil"></i> {$.en ? 'Reply' : 'Ответить'}
                            </a>
                            {if $item.can_edit}
                                <a href="#edit/{$item.id}" class="text-danger ml-3">
                                    <i class="far fa-edit"></i> {$.en ? 'Edit' : 'Изменить'}
                                </a>
                            {/if}
                        </div>
                        {if $admin}
                            <div class="admin-actions">
                                <a href="#delete/{$item.id}" class="text-danger ml-auto">
                                    <i class="far fa-trash"></i> {$.en ? 'Delete' : 'Удалить'}
                                </a>
                                <a href="#remove/{$item.id}" class="text-danger ml-3">
                                    <i class="far fa-times"></i> {$.en ? 'Remove' : 'Уничтожить'}
                                </a>
                            </div>
                        {/if}
                    {else}
                        <div class="small">
                            <i class="far fa-trash"></i>
                            {$item.deletedon | dateago}, {$item.deletedby | user : 'fullname'}
                        </div>
                        <div class="admin-actions">
                            <a href="#restore/{$item.id}" class="text-success ml-auto">
                                <i class="far fa-undo"></i> {$.en ? 'Restore' : 'Восстановить'}
                            </a>
                            <a href="#remove/{$item.id}" class="text-danger ml-3">
                                <i class="far fa-times"></i> {$.en ? 'Remove' : 'Уничтожить'}
                            </a>
                        </div>
                    {/if}
                </div>
            {/if}
        </div>
    </div>
{/if}

{if $level < 10}
<ul class="comments-list">
    {/if}
    {if $item.children}
        {var $level = $level + 1}

        {foreach $item.children as $child}
            {include 'file:chunks/comments/_comment.tpl' item=$child level=$level seen=$seen topic=$topic}
        {/foreach}
    {/if}
    {if $level < 10}
</ul>
{/if}