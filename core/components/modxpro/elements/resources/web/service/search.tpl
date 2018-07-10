<form method="get" action="/search">
    <div class="input-group">
        <input type="text" name="query" class="form-control" value="{$.get.query | escape}"
               placeholder="{$.en ? 'Search' : 'Поиск'}">
        <div class="input-group-append">
            <button class="input-group-text">
                <i class="far fa-search"></i>
            </button>
        </div>
    </div>
</form>

{if $.get.query}
    <div class="mt-5">
        {var $res = 'search/search' | processor : [
        'query' => $.get.query,
        'limit' => 20,
        ]}
    </div>
    <div class="topics-list">
        <h4 class="topic-title">
            {if $res.total}
                {if $.en}
                    Found {$res.total | number} {$res.total | declension : 'topic|topics'}
                {else}
                    {$res.total | declension : 'Найдена|Найдено|Найдено'}
                    {$res.total | number} {$res.total | declension : 'заметка|заметки|заметок'}
                {/if}
            {else}
                {$.en ? 'Nothing found' : 'Ничего не найдено'}
            {/if}
        </h4>
        {$res.results}

        {include 'file:chunks/_pagination.tpl' res=$res}
    </div>
{/if}