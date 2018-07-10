{include 'file:chunks/users/_header.tpl' profile=$profile author=$author}
{var $res = 'community/comment/getlist' | processor : [
    'limit' => 20,
    'user' => $user.id,
]}

<div class="user-content">
    <div class="comments-list">
        {$res.results ?: '<div class="alert alert-info">'~($.en ? 'There`s nothing here' : 'Здесь ничего нет') ~'</div>'}
        {include 'file:chunks/_pagination.tpl' res=$res}
    </div>
</div>