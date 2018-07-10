{var $profile = '/users/' ~ ($_modx->user.usename ? $_modx->user.username : $_modx->user.id)}
<div class="btn-group">
    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
        <span class="user-name d-none d-lg-inline">
            {$_modx->user.fullname}
            <span class="far fa-chevron-down"></span>
        </span>
        <span class="user-icon d-lg-none"><i class="fa fa-user"></i></span>
    </button>
    <div class="dropdown-menu user-menu">
        <div class="header">
            <a href="{$profile}" class="avatar">
                {$_modx->user | avatar : 80 : '<img src="{image1}" srcset="{image2} 2x" width="{width}" alt="{alt}">'}
            </a>
            <div class="wrapper">
                <div class="email">{$email}</div>
                <div>
                    {var $rating = $_modx->user | author : 'rating'}
                    {$.en ? 'Rating' : 'Рейтинг'}:
                    {if $rating > 0}
                        <span class="text-success font-weight-bold">{$rating | number : 1}</span>
                    {elseif $rating < 0}
                        <span class="text-danger font-weight-bold">{$rating | number : 1}</span>
                    {else}
                        {$rating | number : 1}
                    {/if}
                </div>
            </div>
        </div>
        <div class="links">
            <a href="/topic"><i class="far fa-pencil"></i> {$.en ? 'Make a topic' : 'Написать заметку'}</a>
            <a href="{$profile}"><i class="far fa-user"></i> {$.en ? 'My profile' : 'Мой профиль'}</a>
            <a href="https://id.{'http_host' | config | preg_replace : '#^en\.#' : ''}">
                <i class="far fa-cog"></i> {$.en ? 'My settings' : 'Мои настройки'}
            </a>
            {if !$authorized}
                <div class="dropdown-divider"></div>
            {/if}
            <a href="?action=auth/logout"><i class="far fa-sign-out fa-flip-horizontal"></i> {$.en ? 'Log out' : 'Выйти'}</a>
        </div>
        {if $authorized?}
            <div class="authorized">
                <div class="dropdown-divider"></div>
                {foreach $authorized as $id => $user}
                    <div class="d-flex align-items-center item">
                        <a href="?action=auth/change&user_id={$id}" class="d-flex avatar align-items-center">
                            {$user | avatar : 64 : '<img src="{image1}" srcset="{image2} 2x" width="{width}" alt="{alt}">'}
                            <div class="d-flex flex-column">
                                <div class="name">{$user.fullname}</div>
                                <div class="email small">{$user.email}</div>
                            </div>
                        </a>
                        <a href="?action=auth/logout&user_id={$id}" class="ml-auto far fa-sign-out"></a>
                    </div>
                {/foreach}
            </div>
        {/if}
    </div>
</div>
