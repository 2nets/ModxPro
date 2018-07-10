<?php

class AppRouter
{
    /** @var modX $modx */
    public $modx;
    public $config = [];
    /** @var pdoFetch $pdoTools */
    public $pdoTools;
    /** @var FastRoute\Dispatcher $dispatcher */
    public $dispatcher;
    /** @var App */
    public $App;


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx = $modx;
        $this->pdoTools = $modx->getService('pdoFetch');
        $this->App = $modx->getService('App');
        $this->initialize();
    }


    /**
     * Add routes
     */
    public function initialize()
    {
        $this->dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            if (strpos($_SERVER['HTTP_HOST'], 'id.') === 0) {
                $r->addRoute(['GET', 'POST'], '/oauth2[/{action}]', 'authPage');
                $r->addRoute('GET', '/me', 'userProfile');
            } else {
                $r->addRoute('GET', '/{mode:popular|best|all}', 'mainPage');
                $r->addGroup('/users/{user}', function (FastRoute\RouteCollector $r) {
                    $r->addRoute('GET', '', 'userPage');
                    $r->addRoute('GET', '/{page:topics|comments|favorites}[/{subpage:drafts|comments}]', 'userPage');
                    // Redirect from old url
                    $r->addRoute('GET', '/tickets[/drafts]', function () {
                        $redirect = rtrim(str_replace('/tickets', '/topics', $_REQUEST['q']), '/');
                        $this->modx->sendRedirect($redirect, ['responseCode' => 'HTTP/1.1 301 Moved Permanently']);
                    });
                });
                $r->addRoute('GET', '/topic[/{id}]', 'editTopic');
                $r->addRoute('GET', '/{section:[a-z]+}/{id:\d+}', 'viewTopic');
            }
            $r->addRoute('GET', '/outside', 'outside');
            $r->addRoute('GET', '/api', 'api');
        });
    }


    /**
     * Process request
     */
    public function process()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];

        // Remove slash and question signs at the end of url
        if ($uri != '/' && in_array(substr($uri, -1), ['/', '?'])) {
            $this->modx->sendRedirect(rtrim($uri, '/?'), ['responseCode' => 'HTTP/1.1 301 Moved Permanently']);
        }

        // Remove .html extension
        if (preg_match('#\.html$#i', $uri)) {
            $this->modx->sendRedirect(preg_replace('#\.html$#i', '', $uri), ['responseCode' => 'HTTP/1.1 301 Moved Permanently']);
        }
        // Switch contexts and language
        if (strpos($host, 'en.') === 0) {
            $this->modx->switchContext('en');
            $_SESSION['lang'] = 'en';
        } elseif (strpos($host, 'id.') === 0) {
            $this->modx->switchContext('id');
            if (!empty($_GET['lang']) && in_array($_GET['lang'], ['ru', 'en'])) {
                $_SESSION['lang'] = $_GET['lang'];
                unset($_GET['lang']);
                $url = preg_replace('#\?.*#', '', $_SERVER['REQUEST_URI']);
                if (!empty($_GET)) {
                    $url .= '?' . http_build_query($_GET);
                }
                $this->modx->sendRedirect($url);
            } elseif (!empty($_SESSION['lang'])) {
                $this->modx->setOption('cultureKey', $_SESSION['lang']);
            } elseif (!preg_match('#\bru\b#i', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $_SESSION['lang'] = 'en';
                $this->modx->setOption('cultureKey', 'en');
            }
        } else {
            $_SESSION['lang'] = 'ru';
        }

        // FastRoute
        if ($pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $route = $this->dispatcher->dispatch($method, $uri);
        if ($route[0] == FastRoute\Dispatcher::FOUND) {
            if (is_callable($route[1])) {
                $route[1]($route[2]);
            } elseif (is_string($route[1])) {
                $this->{$route[1]}($route[2]);
            }
        }
    }


    /**
     * @param $mode
     */
    public function mainPage($mode)
    {
        $mode = array_shift($mode);
        $data = [
            'mode' => $mode,
        ];

        $params = [
            'limit' => 10,
            'showSection' => true,
        ];
        $month = date('Y-m- H:i:s', time() - (30 * 86400));
        switch ($mode) {
            case 'popular':
                $params = array_merge($params, [
                    'sort' => 'comTopic.views',
                    'dir' => 'desc',
                    'where' => [
                        'comTopic.createdon:>' => $month,
                    ],
                ]);
                break;
            case 'best':
                $params = array_merge($params, [
                    'sort' => 'comTopic.rating desc,comTopic.publishedon',
                    'dir' => 'desc',
                    'where' => [
                        'comTopic.rating:>' => 0,
                        'comTopic.publishedon:>' => $month,
                    ],
                ]);
                break;
        }
        $data['res'] = $this->App->runProcessor('community/topic/getlist', $params);

        $this->modx->resource = $this->modx->getObject('modResource', $this->modx->getOption('site_start'));
        $this->modx->resource->set('content', $this->pdoTools->getChunk('@INLINE ' . $this->modx->resource->content, $data));
        $this->modx->request->prepareResponse();
    }


    /**
     * @param $username
     *
     * @return bool|modUser
     */
    public function getUser($username)
    {
        $where = ['active' => 1];
        if (is_numeric($username)) {
            $where['id'] = (int)$username;
        } else {
            /** @var appUserName $record */
            if (!$record = $this->modx->getObject('appUserName', ['username' => $username])) {
                return false;
            }
            $where['id'] = $record->user_id;
        }

        /** @var modUser $user */
        if ($user = $this->modx->getObject('modUser', $where)) {
            if ($user->isMember('Users')) {
                $redirect = false;
                if (!empty($user->Profile->usename) && $username != strtolower($user->username)) {
                    $redirect = str_replace('/' . $username, '/' . strtolower($user->username), $_REQUEST['q']);
                } elseif (empty($user->Profile->usename) && !is_numeric($username)) {
                    $redirect = str_replace('/' . $username, '/' . $user->id, $_REQUEST['q']);
                }
                if (!empty($redirect)) {
                    $this->modx->sendRedirect($redirect);
                }

                return $user;
            }
        }

        return false;
    }


    /**
     * @param array $vars
     */
    public function userPage(array $vars)
    {
        if (!in_array($this->modx->context->key, ['web', 'en'])) {
            $this->modx->sendRedirect('/');
        }
        if (!$user = $this->getUser($vars['user'])) {
            return;
        }

        if (!empty($vars['subpage'])) {
            if ($vars['subpage'] == 'drafts' && ($vars['page'] != 'topics' || $user->id != $this->modx->user->id)) {
                return;
            } elseif ($vars['subpage'] == 'comments' && $vars['page'] != 'favorites') {
                return;
            }
        }
        $this->modx->resource = $this->modx->getObject('modResource', $this->modx->getOption('users_id'));

        // Prepare data
        if (!$author = $this->modx->getObject('comAuthor', ['id' => $user->id])) {
            return;
        }

        $data = [
            'subpage' => $vars['subpage'],
            'user' => $user->get(['id', 'username', 'external_key']),
            'profile' => $user->Profile->get(['fullname', 'email', 'photo', 'blocked', 'extended', 'comment', 'website', 'city', 'feedback', 'usename']),
            'author' => $author->toArray(),
        ];
        $data['author']['favorites'] = $this->modx->getCount('comStar', ['createdby' => $user->id]);
        if ($user->id == $this->modx->user->id) {
            $data['author']['drafts'] = $this->modx->getCount('comTopic', ['createdby' => $user->id, 'published' => false]);
            $data['author']['topics'] += $data['author']['drafts'];
        }
        $data['tab'] = !empty($vars['page'])
            ? $vars['page']
            : 'info';

        $title = [];
        switch ($vars['page']) {
            case 'topics':
                $title[] = $this->modx->lexicon('user_topics');
                break;
            case 'comments':
                $title[] = $this->modx->lexicon('user_comments');
                break;
            case 'favorites':
                $title[] = $this->modx->lexicon('user_favorites');
                $data['author']['favorite_topics'] = $this->modx->getCount('comStar', ['createdby' => $user->id, 'class' => 'comTopic']);
                $data['author']['favorite_comments'] = $this->modx->getCount('comStar', ['createdby' => $user->id, 'class' => 'comComment']);
                break;
            default:
                if (!empty($data['profile']['website'])) {
                    $data['website'] = [
                        'url' => $data['profile']['website'],
                        'name' => mb_strlen($data['profile']['website'], 'UTF-8') > 20
                            ? mb_substr($data['profile']['website'], 0, 20, 'UTF-8') . '...'
                            : $data['profile']['website'],
                    ];
                } else {
                    $data['website'] = [];
                }
                $services = [
                    'github' => 'https://github.com',
                    'twitter' => 'https://twitter.com',
                    'vkontakte' => 'https://vk.com',
                    'telegram' => '',
                    'skype' => '',
                ];
                foreach ($services as $service => $link) {
                    if (!empty($data['profile']['extended'][$service])) {
                        $data['services'][] = [
                            'user' => $data['profile']['extended'][$service],
                            'link' => $link,
                            'name' => ucfirst($service),
                        ];
                    }
                }
        }
        $title[] = $user->Profile->fullname;
        $title[] = $this->modx->resource->pagetitle;

        $this->modx->resource->set('longtitle', implode(' / ', $title));
        $this->modx->resource->set('content', $this->pdoTools->getChunk('@FILE chunks/users/' . $data['tab'] . '.tpl', $data));

        $this->modx->request->prepareResponse();
    }


    /**
     * @param array $vars
     */
    public function authPage(array $vars)
    {
        if (!$vars) {
            $this->modx->sendRedirect('/');
        }

        /** @var AppAuth $auth */
        $auth = $this->modx->getService('AppAuth', 'AppAuth', MODX_CORE_PATH . 'components/modxpro/model/');
        $server = new OAuth2\Server($auth);
        $request = OAuth2\Request::createFromGlobals();
        $response = new OAuth2\Response();

        switch ($vars['action']) {
            case 'auth':
                if ($server->validateAuthorizeRequest($request, $response)) {
                    if ($this->modx->user->isAuthenticated($this->modx->context->key)) {
                        $agreement = (bool)$this->modx->getCount('appAuthToken', [
                            'client_id' => $request->query('client_id'),
                            'user_id' => $this->modx->user->id,
                        ]);
                        if ($agreement) {
                            $server->handleAuthorizeRequest($request, $response, true, $this->modx->user->id);
                        } elseif ($agree = $request->request('agree')) {
                            $server->handleAuthorizeRequest($request, $response, $agree == 'yes', $this->modx->user->id);
                        } else {
                            $this->modx->resource = $this->modx->getObject('modResource', $this->modx->getOption('site_start'));
                            $this->modx->resource->set('pagetitle', 'OAuth2');
                            $this->modx->resource->set('content', $this->pdoTools->getChunk(
                                '@FILE chunks/oauth/auth.tpl',
                                $auth->getClientDetails($_GET['client_id'])
                            ));
                            $this->modx->request->prepareResponse();
                        }
                    } else {
                        $this->modx->sendForward($this->modx->getOption('site_start'));
                    }
                }
                exit($response->send());
                break;
            case 'token':
                $server->handleTokenRequest($request, $response);
                exit($response->send());
                break;
            case 'profile':
                if (!$server->verifyResourceRequest($request, $response)) {
                    exit($response->send());
                }
                /** @var OAuth2\Controller\ResourceController $controller */
                if ($controller = $server->getResourceController()) {
                    if ($token = $controller->getToken()) {
                        /** @var modUser $user */
                        if ($user = $this->modx->getObject('modUser', ['id' => $token['user_id']])) {
                            $data = [
                                'id' => $user->id,
                                'identifier' => $user->username,
                                'displayname' => $user->Profile->fullname,
                                'dob' => '',
                                'email' => $user->Profile->email,
                                'photoURL' => $user->Profile->photo,
                                'webSiteURL' => $user->Profile->website,
                                'phone' => $user->Profile->phone,
                                'address' => $user->Profile->address,
                                'country' => $user->Profile->country,
                                'region' => $user->Profile->state,
                                'city' => $user->Profile->city,
                                'zip' => $user->Profile->zip,
                                'profileurl' => 'https://modx.pro/users/' .
                                    ($user->Profile->usename ? $user->username : $user->id),
                            ];
                            exit(json_encode($data));
                        }
                    }
                }
                break;
        }

        $this->modx->sendRedirect('/');
    }


    /**
     * Redirects authenticated user to his profile
     */
    public function userProfile()
    {
        if (!$this->modx->user->isAuthenticated()) {
            $url = '/';
        } else {
            if ($this->modx->context->key == 'id') {
                $replace = $this->modx->getOption('cultureKey') == 'en' ? 'en.' : '';
                $host = preg_replace('#^id\.#', $replace, $this->modx->getOption('http_host'));
                $url = '//' . $host . '/users/';
            } else {
                $url = '/users/';
            }
            $url .= $this->modx->user->Profile->get('usename')
                ? $this->modx->user->username
                : $this->modx->user->id;
        }

        $this->modx->sendRedirect($url);
    }


    /**
     * @param $vars
     */
    public function viewTopic($vars)
    {
        $topic = null;
        $c = $this->modx->newQuery('comTopic', ['id' => (int)$vars['id']]);
        $c->innerJoin('modResource', 'Section');
        $c->innerJoin('modUserProfile', 'UserProfile');
        $c->select($this->modx->getSelectColumns('comTopic', 'comTopic'));
        $c->select($this->modx->getSelectColumns('modResource', 'Section', 'section_', ['pagetitle', 'context_key', 'uri']));
        $c->select($this->modx->getSelectColumns('modUserProfile', 'UserProfile', '', ['photo', 'email', 'fullname']));
        /** @var comTopic $topic */
        if (!$topic = $this->modx->getObject('comTopic', $c)) {
            return;
        }

        if ($vars['section'] != $topic->section_uri) {
            $this->modx->sendRedirect('/' . $topic->section_uri . '/' . $topic->id);
        }

        if ($topic->section_context_key != $this->modx->context->key) {
            $host = $this->modx->getOption('http_host');
            $host = $this->modx->getOption('cultureKey') == 'en'
                ? preg_replace('#^en\.#', '', $host)
                : 'en.' . $host;
            $url = '//' . $host . '/' . implode('/', $vars);
            $this->modx->sendRedirect($url);
        }

        $this->modx->resource = $this->modx->getObject('modResource', $this->modx->getOption('blogs_id'));
        if (!$topic->published && ($this->modx->user->id != $topic->createdby && !$this->modx->user->isMember('Administrator'))) {
            header('HTTP/1.0 403 Forbidden');
            $this->modx->resource->set('pagetitle', '');
            $this->modx->resource->set('content', $this->pdoTools->getChunk('@FILE chunks/topics/unpublished.tpl', $topic->toArray()));
        } else {

            $this->modx->resource->set('is_topic', true);
            if ($this->modx->user->id) {
                $topic->set('star', (bool)$this->modx->getCount('comStar', [
                    'class' => 'comTopic',
                    'id' => $topic->id,
                    'createdby' => $this->modx->user->id,
                ]));
            } else {
                $topic->set('star', false);
            }
            $topic->set('can_edit', $topic->canEdit());
            $topic->set('can_vote', $topic->canVote());
            $this->modx->resource->set('pagetitle', $topic->pagetitle);
            $this->modx->resource->set('content', $this->pdoTools->getChunk('@FILE chunks/topics/topic.tpl', $topic->toArray()));
            $topic->addView();
        }
        $this->modx->request->prepareResponse();
    }


    /**
     * @param $vars
     */
    public function editTopic($vars)
    {
        if (!$this->modx->user->isAuthenticated($this->modx->context->key)) {
            $this->modx->sendForward($this->modx->getOption('unauthorized_page'));
        }
        if (!empty($vars['id']) && is_numeric($vars['id'])) {
            $c = $this->modx->newQuery('comTopic', ['id' => (int)$vars['id']]);
            $c->innerJoin('comSection', 'Section');
            $c->select($this->modx->getSelectColumns('comTopic', 'comTopic'));
            $c->select('Section.context_key');
            if ($c->prepare() && $c->stmt->execute()) {
                $topic = $c->stmt->fetch(PDO::FETCH_ASSOC);
            }
            if (empty($topic) || ($this->modx->user->id != $topic['createdby'] && !$this->modx->user->isMember('Administrator'))) {
                return;
            } elseif ($topic['context_key'] != $this->modx->context->key) {
                $host = $this->modx->getOption('http_host');
                $host = $this->modx->getOption('cultureKey') == 'en'
                    ? preg_replace('#^en\.#', '', $host)
                    : 'en.' . $host;
                $url = '//' . $host . '/topic/' . implode('/', $vars);
                $this->modx->sendRedirect($url);
            }
        } else {
            $topic = $this->modx->newObject('comTopic');
            $where = [
                'class_key' => 'comSection',
                'alias' => 'help',
                'published' => true,
                'deleted' => false,
                'context_key' => $this->modx->context->key,
            ];
            if (!empty($vars['id'])) {
                $where['alias'] = trim($vars['id']);
            }
            if ($help = $this->modx->getObject('comSection', $where)) {
                $topic->set('parent', $help->get('id'));
            } else {
                $this->modx->sendRedirect('/topic');
            }
            $topic = $topic->toArray();
        }

        $this->modx->setPlaceholder('sidebar', $this->App->pdoTools->getChunk('@FILE chunks/topics/_rules.tpl'));
        $sections = $this->modx->getIterator('comSection', [
            'class_key' => 'comSection',
            'published' => true,
            'deleted' => false,
            'context_key' => $this->modx->context->key,
        ]);
        /** @var comSection $section */
        foreach ($sections as $section) {
            if ($section->checkPolicy('save')) {
                $topic['sections'][] = $section->get(['id', 'pagetitle']);
            }
        }

        $params = $topic;
        $params['id'] = $topic['parent'];
        /** @var modProcessorResponse $res */
        if ($res = $this->App->runProcessor('community/topic/getsection', $params)) {
            $topic = array_merge($topic, $res['object']);
        }

        $this->modx->resource = $this->modx->getObject('modResource', $this->modx->getOption('blogs_id'));
        $this->modx->resource->set('pagetitle', $this->modx->lexicon('topic'));
        $this->modx->resource->set('content', $this->pdoTools->getChunk('@FILE chunks/topics/_form.tpl', $topic));

        $this->modx->request->prepareResponse();
    }


    /**
     *
     */
    public function outside()
    {
        if (empty($_GET['url'])) {
            return;
        }

        $url = $_GET['url'];
        $html = "<script>window.opener = null;window.location.replace('{$url}');</script>";
        $html .= "<noscript><META http-equiv=\"refresh\" content=\"0;URL = '{$url}'\"></noscript>";

        exit($html);
    }


    /**
     *
     */
    public function api()
    {
        $response = [
            'success' => false,
            'message' => '',
            'object' => [],
        ];

        $req = $_REQUEST;
        if (!empty($req['action'])) {
            switch ($req['action']) {
                case 'comments':
                    $c = $this->modx->newQuery('comTopic', ['published' => true]);
                    $c->innerJoin('modUserProfile', 'UserProfile');
                    $c->innerJoin('comComment', 'Comment', 'Comment.id = comTopic.last_comment');
                    $c->select('Comment.id, Comment.content as text, Comment.createdon, Comment.createdby');
                    $c->select('comTopic.pagetitle, comTopic.uri, UserProfile.fullname as name');
                    $c->sortby('comTopic.publishedon', 'desc');
                    $c->limit((int)$_REQUEST['limit'] != 0 ? (int)$_REQUEST['limit'] : 3);
                    if (!empty($req['packages'])) {
                        $packages = array_map('trim', explode(',', $req['packages']));
                        if (!empty($packages) && $packages[0] != '') {
                            foreach ($packages as $package) {
                                $c->orCondition([
                                    'comTopic.pagetitle:LIKE' => "%{$package}%",
                                ], null, 2);
                            }
                        }
                    }
                    if ($c->prepare() && $c->stmt->execute()) {
                        $response['success'] = true;
                        $response['object'] = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                    break;
            }
        }
        $response['time'] = microtime(true) - $this->modx->startTime;

        exit(json_encode($response, JSON_UNESCAPED_UNICODE));
    }
}