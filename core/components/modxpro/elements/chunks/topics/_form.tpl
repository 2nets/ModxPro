<div class="topic-form">
    <form method="post" id="topic-form">
        <div class="close-preview"></div>
        <div class="preview"></div>
        <input type="hidden" name="id" value="{$id ?: 0}">
        <input type="hidden" name="action" value="community/topic/{$id ? 'update' : 'create'}">

        <div class="form-group">
            <select name="parent" class="form-control">
                {foreach $sections as $section}
                    <option value="{$section.id}"{if $section.id == $parent} selected{/if}>{$section.pagetitle}</option>
                {/foreach}
            </select>
            <small class="form-text text-muted topic-parent-desc alert alert-info">{$description}</small>
        </div>

        <div class="form-group mt-3">
            <input type="text" name="pagetitle" id="topic-title" value="{$pagetitle}" autofocus
                   placeholder="{$.en ? 'Topic title' : 'Заголовок'}">
            <small class="form-text text-muted"></small>
        </div>

        <div class="topic-fields d-flex mt-3 no-gutters flex-wrap"{if !$fields} style="display:none"{/if}>{$fields}</div>

        <div class="form-group mt-3">
            <textarea name="content" id="topic-content" class="markitup"
                      rows="30" placeholder="{$.en ? 'Content' : 'Содержимое'}">{$content | htmlentities}</textarea>
            <small class="form-text text-muted"></small>
        </div>

        <div class="buttons">
            <button class="btn btn-light mr-auto" name="preview"><i class="far fa-eye"></i> {$.en ? 'Preview' : 'Предпросмотр'}</button>
            {if !$id}
                <button class="btn btn-info" name="draft"><i class="far fa-power-off"></i> {$.en ? 'Save to drafts' : 'В черновики'}</button>
                <button class="btn btn-primary" name="publish"><i class="far fa-bolt"></i> {$.en ? 'Publish' : 'Опубликовать'}</button>
            {elseif !$published}
                <button class="btn btn-primary" name="publish"><i class="far fa-bolt"></i> {$.en ? 'Publish' : 'Опубликовать'}</button>
                <button class="btn btn-success" type="submit"><i class="far fa-save"></i> {$.en ? 'Save' : 'Сохранить'}</button>
            {else}
                <a href="/{$uri}" class="btn btn-light btn-sm" target="_blank">{$.en ? 'Open' : 'Открыть'}</a>
                <button class="btn btn-info" name="draft"><i class="far fa-power-off"></i> {$.en ? 'Save to drafts' : 'В черновики'}</button>
                <button class="btn btn-success" type="submit"><i class="far fa-save"></i> {$.en ? 'Save' : 'Сохранить'}</button>
            {/if}
        </div>
    </form>
</div>