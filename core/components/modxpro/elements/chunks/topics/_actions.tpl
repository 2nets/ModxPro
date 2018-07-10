<div class="buttons">
    <a href="/topic/{$item.id}" class="btn btn-sm btn-outline-primary">
        <i class="far fa-edit"></i> {$.en ? 'Edit topic' : 'Изменить заметку'}
    </a>
    {if $item.published}
        <a href="#draft/{$item.id}" class="btn btn-sm btn-outline-danger">
            <i class="far fa-power-off"></i> {$.en ? 'Move to drafts' : 'Убрать в черновики'}
        </a>
    {else}
        <a href="#publish/{$item.id}" class="btn btn-sm btn-outline-success">
            <i class="far fa-bolt"></i> {$.en ? 'Publish topic' : 'Опубликовать заметку'}
        </a>
    {/if}
</div>