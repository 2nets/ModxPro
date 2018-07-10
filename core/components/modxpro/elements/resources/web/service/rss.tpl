{var $res = 'community/topic/getlist' | processor : [
    'limit' => 20,
    'tpl' => '@FILE chunks/topics/rss.tpl',
    'showSection' => true,
    'getPages' => false,
    'where' => $.get.blogs
        ? ['Section.alias:IN' => ($.get.blogs | split : ',')]
        : [],
]}
{$res.results}