<a href="#create" class="btn btn-secondary mt-5" id="comment-form-open">
    {$.en ? 'Write a comment' : 'Написать комментарий'}
</a>

<div id="comment-form-placeholder" class="comment-form">
    <form method="post" id="comment-form" class="mt-5 mb-5" data-topic="{$results.topic.id}">
        <div class="close-preview"></div>
        <div class="preview"></div>
        <input type="hidden" name="id" value="0">
        <input type="hidden" name="parent" value="0">
        <input type="hidden" name="action" value="community/comment/create">

        <div class="form-group">
            <textarea name="content" class="markitup" rows="8" placeholder="{$.en ? 'Comment' : 'Комментарий'}"></textarea>
            <small class="form-text text-muted"></small>
        </div>

        <div class="alert alert-info">
            {if $.en}
                If you insert an image in the <strong>img</strong> tag, please use
                our <a href="//file.modx.pro/en/" target="_blank">File storage</a>.
            {else}
                Если вы вставляте картинку в теге <strong>img</strong>, пожалуйста, воспользуйтесь
                нашим <a href="//file.modx.pro" target="_blank">Файлохранилищем</a>.
            {/if}
        </div>

        <div class="buttons">
            <button class="btn btn-light mr-auto" name="preview"><i class="far fa-eye"></i> {$.en ? 'Preview' : 'Предпросмотр'}</button>
            <button class="btn btn-success" type="submit"><i class="far fa-save"></i> {$.en ? 'Save' : 'Сохранить'}</button>
        </div>
    </form>
</div>

<div id="comments-panel">
    <a href="#load" class="btn btn-light reload"><i class="far fa-sync"></i></a>
    <a href="#next" class="btn btn-light new"{if !$new} style="display:none;"{/if}>{$new}</a>
</div>