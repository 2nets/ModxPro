<a href="?hauth_action=login&amp;provider={$title}"
   class="{$provider ?: 'facebook google twitter vkontakte yandex'}"
   rel="nofollow"
   title="{('provider_' ~ ($title | lower)) | lexicon}"
   data-toggle="tooltip"
   data-animation="false"
   data-placement="bottom"></a>