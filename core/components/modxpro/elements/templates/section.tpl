{extends 'file:templates/base.tpl'}

{block 'content'}
    {var $res = 'community/topic/getlist' | processor : [
        'where' => ['comTopic.parent' => $_modx->resource.id],
    ]}
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <h1 class="section-title">
            {$_modx->resource.pagetitle}
        </h1>
        {if $_modx->isAuthenticated()}
            <div class="subscription custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="subscription"
                       data-type="section" data-id="{$_modx->resource.id}"
                       {if ($_modx->resource.id | subscribed)}checked{/if}>
                <label class="custom-control-label" for="subscription">
                    {$.en ? 'Notice about new topics' : 'Уведомлять о новых заметках'}
                </label>
            </div>
        {/if}
    </div>
    <div class="buttons">
        <a href="/topic/{$_modx->resource.alias}" class="btn btn-outline-primary mb-3">
            {$.en ? 'Write a topic' : 'Написать заметку'}
        </a>
    </div>
    <div class="topics-list">
        {$res.results}
        {include 'file:chunks/promo/page.tpl'}
        {include 'file:chunks/_pagination.tpl' res=$res}
    </div>
{/block}