{foreach $results as $idx => $item}
    <div class="search-item mb-4">
        <b>{$start + $idx + 1}.</b> <a href="{$item.uri}">{$item.pagetitle}</a>
        <div class="intro mt-2 mb-2">
            {$item.introtext | jevix}
        </div>
        <div class="d-flex flex-wrap align-items-center small text-secondary">
            <div class="mr-3"><i class="far fa-calendar"></i> {$item.createdon | dateAgo}</div>
            <div class="mr-3">
                <a href="/users/{$item.usename ? $item.username : $item.createdby}">
                    <i class="far fa-user"></i> {$item.fullname}
                </a>
            </div>
            <div class="mr-3">
                <a href="/{$item.section_uri}"><i class="far fa-folder-open"></i> {$item.section_title}</a>
            </div>
            <div title="{$.en ? 'Search weight' : 'Поисковый вес'}"><i class="far fa-weight"></i> {$item.weight | number}</div>
        </div>
    </div>
{/foreach}