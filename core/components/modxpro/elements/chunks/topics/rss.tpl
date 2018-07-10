<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    {var $link = $_modx->resource.id | url : ['scheme' => 'full'] : $.get | replace : 'q=rss&amp;' : ''}
    <channel>
        <title>{$_modx->resource.pagetitle} / {$_modx->config.site_name}</title>
        <link>{$link}</link>
        <description>{$_modx->resource.description}</description>
        <language>{$.en ? 'en' : 'ru'}</language>
        <copyright></copyright>
        <ttl>120</ttl>
        <atom:link href="{$link}" rel="self" type="application/rss+xml" />

        {foreach $results as $item}
            <item>
                <title>{$item.pagetitle | cdata}</title>
                <link>{$_modx->config.site_url}{$item.uri}</link>
                <description>{$item.introtext | abs_url | cdata}</description>
                <pubDate>{$item.createdon | date : 'r'}</pubDate>
                <guid>{$_modx->config.site_url}{$item.uri}</guid>
            </item>
        {/foreach}

    </channel>
</rss>