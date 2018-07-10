{foreach $results as $item}
    <div class="topic-row">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h2 class="topic-title">
                <a href="/{$item.uri}">{$item.pagetitle}</a>
            </h2>
            {if $_modx->isAuthenticated()}
                <div class="subscription custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input subscription" id="subscription-{$item.id}"
                           data-type="section" data-id="{$item.id}"
                           {if ($item.id | subscribed)}checked{/if}>
                    <label class="custom-control-label" for="subscription-{$item.id}">
                        {$.en ? 'Notice about new topics' : 'Уведомлять о новых заметках'}
                    </label>
                </div>
            {/if}
        </div>
        <div class="topic-content">
            {$item.description}
            <ul class="last-topics">
                {var $res = 'community/topic/getlist' | processor : [
                    'limit' => 5,
                    'fastMode' => true,
                    'getPages' => false,
                    'where' => ['parent' => $item.id],
                    'tpl' => '@FILE chunks/sections/topics.tpl ',
                ]}
                {$res.results}
            </ul>
        </div>

        {include 'file:chunks/sections/_meta.tpl' item=$item}
    </div>
{/foreach}