<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/blogs">{$.en ? 'Blogs' : 'Разделы'}</a></li>
        <li class="breadcrumb-item"><a href="/{$section_uri}">{$section_pagetitle}</a></li>
    </ol>
</nav>

<div id="topic-page">
    <h1 class="topic-title">
        {$pagetitle}
    </h1>

    {if $can_edit}
        {include 'file:chunks/topics/_actions.tpl' item=$_pls}
    {/if}

    <div class="topic-content">
        {$content | jevix | prism}
    </div>
    {include 'file:chunks/topics/_meta.tpl' item=$_pls user=$user}
    {include 'file:chunks/promo/page.tpl'}

    {if $section_uri != 'work'}
        <div class="topic-comments">
            {var $res = 'community/comment/getcomments' | processor : [
            'topic' => $id,
            'limit' => 0,
            ]}

            {$res.results}
        </div>
    {else}
        <div class="alert alert-warning mt-5">
            {if $.en}
                <p>Comments in this section <b>are disabled</b>, so you must specify your contacts directly in the
                    topic, or activate sending <a href="//id.modx.pro">messages from your profile</a>.</p>
                <p>Please note that <b>modx.pro</b> does not bear any responsibility for doing the work or paying for
                    the order. This is just a message board, then you communicate outside of our site.</p>
            {else}
                <p>Комментарии в этом разделе <b>отключены</b>, так что вы должны указать свои контакты прямо в
                    объявлении, или активировать отправку <a href="//id.modx.pro">сообщений из профиля</a>.</p>
                <p>Обратите внимание, что <b>modx.pro</b> не несёт никакой ответственности за выполнение работы или
                    оплату заказа. Это просто доска объявлений, дальше вы общаетесь за пределами нашей площадки.</p>
            {/if}
        </div>
    {/if}
</div>