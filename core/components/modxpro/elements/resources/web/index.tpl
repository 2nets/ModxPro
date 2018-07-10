{if empty($mode)}
    {var $data = [
        'limit' => 10,
        'showSection' => true,
        'sort' => 'comTopic.important desc, comTopic.publishedon',
        'where' => [
            'comTopic.publishedon:>' => (time() - 30*86400) | date : 'Y-m-d H:i:s'
            'Section.alias:NOT IN' => ['help', 'work'],
            ['comTopic.important:>=' => 0, 'OR:comTopic.rating:>' => -3]
        ],
    ]}
    {var $res = 'community/topic/getlist' | processor : $data}
    {if !$res.total}
        {var $data['where']['comTopic.publishedon:>'] = (time() - 365*86400) | date : 'Y-m-d H:i:s'}
        {var $res = 'community/topic/getlist' | processor : $data}
    {/if}
{/if}

{include 'file:chunks/_banner.tpl'}
<ul class="nav main-tickets-filter nav-pills justify-content-center justify-content-md-end mb-5 mb-md-3">
    <li class="nav-item">
        <a class="nav-link{if $mode == ''} active{/if}" href="/">{$.en ? 'New' : 'Новые'}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link{if $mode == 'popular'} active{/if}" href="/popular">{$.en ? 'Popular' : 'Популярные'}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link{if $mode == 'best'} active{/if}" href="/best">{$.en ? 'Best' : 'Лучшие'}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link{if $mode == 'all'} active{/if}" href="/all">
            {$.en ? 'All, including questions' : 'Все, включая вопросы'}
        </a>
    </li>
</ul>
<div class="topics-list">
    {$res.results ?: '<div class="alert alert-info">'~($.en ? 'There`s nothing here' : 'Здесь ничего нет') ~'</div>'}
    {include 'file:chunks/promo/page.tpl'}
    {include 'file:chunks/_pagination.tpl' res=$res}
</div>
