<div class="d-flex flex-wrap justify-content-between align-items-center">
    <h3>{$.en ? 'Comments' : 'Комментарии'}: <span id="comments-count">{$results.topic.comments ?: 0}</span></h3>
    {if $_modx->isAuthenticated()}
        <div class="subscription custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="subscription"
                   data-type="topic" data-id="{$results.topic.id}"
                   {if ($results.topic.id | subscribed : 'comTopic')}checked{/if}>
            <label class="custom-control-label" for="subscription">
                {$.en ? 'Notice about new comments' : 'Уведомлять о новых комментариях'}
            </label>
        </div>
    {/if}
</div>

<div id="comments" class="comments-list thread">
    {foreach $results.comments as $item}
        {var $level = 0}
        {include 'file:chunks/comments/_comment.tpl' item=$item level=$level seen=$results.seen topic=$results.topic}
    {/foreach}
</div>

{if $_modx->isAuthenticated()}
    {include 'file:chunks/comments/_form.tpl' topic=$results.topic new=$results.new}
{/if}