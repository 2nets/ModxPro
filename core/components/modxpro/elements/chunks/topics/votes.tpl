{if count($results)}
    {foreach $results as $item}
        <div>
            {var $tpl = '<div class="avatar"><a href="{link}" class="text-'~ ($item.value > 0 ? 'success' : 'danger') ~'"><img src="{image1}" width="{width}" height="{height}" srcset="{image2} 2x" alt="{alt}">'~ $item.fullname ~'</a></div>'}
            {$item | avatar : 25 : $tpl}
        </div>
    {/foreach}
{else}
    <div>{$.en ? 'No votes' : 'Голосов нет'}</div>
{/if}