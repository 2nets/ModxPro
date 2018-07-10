<?php

$_lang['access_denied'] = 'Доступ запрещён';
$_lang['csrf_error'] = 'Неверный CSRF токен! Пожалуйста, перезагрузите страницу.';

$_lang['section'] = 'Раздел';
$_lang['section_type'] = 'Раздел с заметками';
$_lang['topic'] = 'Заметка';
$_lang['topic_type'] = 'Заметка';
$_lang['topic_draft_confirm'] = 'Вы уверены, что хотите убрать эту заметку <b>в черновики</b>?';
$_lang['topic_publish_confirm'] = 'Вы уверены, что хотите убрать <b>опубликовать</b> эту заметку?';
$_lang['comment_remove_confirm'] = 'Вы уверены, что хотите убрать <b>уничтожить</b> этот комментарий?';

$_lang['user_info'] = 'Инфо';
$_lang['user_topics'] = 'Заметки';
$_lang['user_comments'] = 'Комментарии';
$_lang['user_favorites'] = 'Избранное';

$_lang['feedback_err_auth'] = 'Для отправки сообщений вы должны быть авторизованы';
$_lang['feedback_err_fields'] = 'Вы должны написать тему и текст сообщения';
$_lang['feedback_err_user'] = 'Не могу найти указанного пользователя';
$_lang['feedback_err_disabled'] = 'Этот пользователь не хочет получать сообщения';
$_lang['feedback_err_send'] = 'Не могу отправить письмо';
$_lang['feedback_success'] = 'Ваше сообщение успешно отправлено!';

$_lang['subject_new_topic'] = 'Новая заметка в секции "[[+section]]"';
$_lang['subject_new_comment'] = 'Новый комментарий к заметке "[[+topic]]"';
$_lang['subject_new_reply'] = 'Ответ на ваш комментарий к заметке "[[+topic]]"';

$_lang['provider_facebook'] = 'Facebook';
$_lang['provider_google'] = 'Google';
$_lang['provider_twitter'] = 'Twitter';
$_lang['provider_vkontakte'] = 'Вконтакте';
$_lang['provider_yandex'] = 'Яндекс';
$_lang['auth_err_email'] = 'Вы должны указать свой email, который использовался при регистрации';
$_lang['auth_err_username'] = 'Пожалуйста, укажите логин';
$_lang['auth_err_username_wrong'] = 'Логин может состоять из букв английского алфавита, цифр и подчеркивания.';
$_lang['auth_err_username_exists'] = 'Этот логин уже занят, увы.';
$_lang['auth_err_password'] = 'Вы забыли указать свой пароль. Если вы его не помните, то можно сделать сброс на соседней вкладке.';

$_lang['app_slack_ok'] = 'Проверьте свой почтовый ящик!';
$_lang['app_slack_err'] = 'Неизвестная ошибка';
$_lang['app_slack_err_email'] = 'Вы забыли указать email';
$_lang['app_slack_err_invited'] = 'Вам уже было отправлено приглашение';

$_lang['vote_err_ended'] = 'Время для голосования закончилось';
$_lang['topic_err_no_content'] = 'Вы забыли написать заметку';
$_lang['topic_err_empty_field'] = 'Это поле обязательно для заполнения';
$_lang['topic_err_rating'] = 'Вам нужен рейтинг <b>[[+required]]</b> для публикации в этом разделе';
$_lang['topic_err_permission'] = 'Вы не можете создавать заметки в этом разделе';
$_lang['topic_err_cut'] = 'Длина текста [[+length]] символов. Вы должны указать тег &lt;cut/&gt для создания превью, если текст больше [[+max]] символов.';
$_lang['topic_success'] = 'Заметка сохранена!';
$_lang['topic_subscribe'] = 'Вы <b>подписались</b> на уведомления о новых комментариях в этой теме';
$_lang['topic_unsubscribe'] = 'Вы <b>отписались</b> от уведомлений о комментариях в этой теме';
$_lang['section_subscribe'] = 'Вы <b>подписались</b> на уведомления о новых заметках в этой теме';
$_lang['section_unsubscribe'] = 'Вы <b>отписались</b> от уведомлений о заметках в этой теме';
$_lang['comment_err_no_content'] = 'Вы забыли написать комментарий';
$_lang['comment_err_edit_time'] = 'Время редактирования комментария истекло';
$_lang['comment_err_topic'] = 'Не могу найти ветку комментариев';
$_lang['comment_success'] = 'Комментарий сохранён!';
$_lang['comment_link_success'] = 'Ссылка на комментарий скопирована!';
$_lang['rss_link_success'] = 'Ссылка на rss ленту скопирована!';