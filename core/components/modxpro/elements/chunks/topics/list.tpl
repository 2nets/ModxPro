{foreach $results as $item}
    <div class="topic-row{if $item.important} important{/if}">
        {if $showSection}
            <div class="section">
                <a href="/{$item.section_uri}">
                    <i class="fal fa-folder-open"></i> {$item.section_title} /
                </a>
            </div>
        {/if}
        <h2 class="topic-title">
            <a href="/{$item.uri}">{$item.pagetitle}</a>
            {if $item.important}
                <sup class="badge" title="{$.en ? 'Important topic' : 'Важная запись'}">
                    <i class="fa fa-exclamation"></i>
                </sup>
            {/if}
        </h2>
        {if $item.createdby == $_modx->user.id}
            {include 'file:chunks/topics/_actions.tpl' item=$item}
        {/if}

        <div class="topic-content">
            {$item.introtext | prism}
        </div>

        {include 'file:chunks/topics/_meta.tpl' item=$item}
    </div>
{/foreach}