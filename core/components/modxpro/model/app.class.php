<?php

class App
{
    /** @var modX $modx */
    public $modx;
    /** @var pdoFetch $pdoTools */
    public $pdoTools;
    public $config = [];
    public $properties = [
        'default' => [
            'topic' => [
                'create' => 1, // rating for creating of topic in this section
                'vote' => 1, // rating you will get for each vote
                'star' => .5, // rating you will get for each star
                'required' => -100, // this rating you need to create topic in this section
                'voting' => 7 * 86400, // time for voting
                //'edit' => 7 * 86400, // time for edit
            ],
            'comment' => [
                'create' => 0,
                'vote' => .2,
                'star' => .2,
                'required' => -100,
                'voting' => 7 * 86400,
                'edit' => 600,
            ],
        ],
        'help' => [
            'topic' => [
                'create' => 0,
                'vote' => .2,
            ],
        ],
        'news' => [
            'topic' => [
                'create' => 10,
                'vote' => 1,
                'required' => 30,
            ],
        ],
        'hosting' => [
            'topic' => [
                'create' => 10,
                'vote' => 1,
                'required' => 30,
            ],
        ],
        'solutions' => [
            'topic' => [
                'create' => 15,
                'vote' => 1,
                'required' => 10,
            ],
        ],
        'work' => [
            'topic' => [
                'create' => 0,
                'vote' => 0,
            ],
        ],
        'crowdfunding' => [
            'topic' => [
                'create' => 10,
            ],
        ],
        'howto' => [
            'topic' => [
                'create' => 20,
                'required' => 10,
            ],
        ],
        'store' => [
            'topic' => [
                'create' => 10,
            ],
        ],
        'components' => [
            'topic' => [
                'create' => 10,
            ],
        ],
        'security' => [
            'topic' => [
                'create' => 10,
                'vote' => 2,
                'required' => 30,
            ],
        ],
        'development' => [
            'topic' => [
                'create' => 10,
                'vote' => 2,
                'required' => 30,
            ],
        ],
        'sites' => [
            'topic' => [
                'create' => 15,
                'vote' => 2,
                'required' => 0,
            ],
        ],
    ];

    const assets_version = '1.04-rc';
    const avatars_path = 'images/avatars/';
    const gravatars_cache = 86400;


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx = $modx;
        $this->pdoTools = $modx->getService('pdoFetch');
        $corePath = MODX_CORE_PATH . 'components/modxpro/';
        $assetsUrl = MODX_ASSETS_URL . 'components/modxpro/';

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',

            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ], $config);
        $this->initialize();
    }


    /**
     * Initialize App
     */
    public function initialize()
    {
        $this->pdoTools = $this->modx->getService('pdoFetch');
        if (!isset($_SESSION['csrf-token'])) {
            $_SESSION['csrf-token'] = bin2hex(openssl_random_pseudo_bytes(16));
        }

        $this->updateModel();

        $this->modx->addPackage('modxpro', $this->config['modelPath']);
        /** @noinspection PhpIncludeInspection */
        require_once $this->config['corePath'] . 'vendor/autoload.php';

        $this->modx->getService('mail', 'AppMail', $this->config['modelPath']);
        $this->modx->lexicon->load('modxpro:default');
        $this->modx->lexicon->load('ru:modxpro:frontend');
        $this->modx->lexicon->load('en:modxpro:frontend');
    }


    /**
     * @param $action
     * @param array $data
     *
     * @return array|bool|mixed
     */
    public function runProcessor($action, array $data = [])
    {
        $action = 'web/' . trim($action, '/');
        /** @var modProcessorResponse $response */
        $response = $this->modx->runProcessor($action, $data, ['processors_path' => $this->config['processorsPath']]);
        if ($response) {
            $data = $response->getResponse();
            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            return $data;
        }

        return false;
    }


    /**
     * @param modSystemEvent $event
     * @param array $scriptProperties
     */
    public function handleEvent(modSystemEvent $event, array $scriptProperties)
    {
        extract($scriptProperties);
        switch ($event->name) {
            case 'pdoToolsOnFenomInit':
                $app = $this;
                $pdo = $this->pdoTools;
                /** @var Fenom|FenomX $fenom */
                $fenom->addAllowedFunctions([
                    'array_keys',
                    'array_values',
                    'strpos',
                    'abs',
                    'strtotime',
                    'time',
                    'date',
                ]);

                $fenom->addAccessorSmart('App', 'App', Fenom::ACCESSOR_PROPERTY);
                $fenom->App = $this;

                $fenom->addAccessorSmart('en', 'en', Fenom::ACCESSOR_PROPERTY);
                $fenom->en = $this->modx->getOption('cultureKey') == 'en';

                $fenom->addAccessorSmart('assets_version', 'assets_version', Fenom::ACCESSOR_PROPERTY);
                $fenom->assets_version = $this::assets_version;

                $fenom->addAccessorSmart('switch_link', 'switch_link', Fenom::ACCESSOR_PROPERTY);
                if ($this->modx->context->key == 'id') {
                    $lang = $this->modx->getOption('cultureKey') == 'en' ? 'ru' : 'en';
                    $fenom->switch_link = strpos($_SERVER['REQUEST_URI'], '?') !== false
                        ? $_SERVER['REQUEST_URI'] . '&lang=' . $lang
                        : $_SERVER['REQUEST_URI'] . '?lang=' . $lang;
                } else {
                    $link = $this->modx->context->key == 'en'
                        ? '//' . preg_replace('#^en\.#', '', $_SERVER['HTTP_HOST'])
                        : '//en.' . $_SERVER['HTTP_HOST'];
                    /** @noinspection PhpUndefinedFieldInspection */
                    $link .= !empty($this->modx->resource) && !empty($this->modx->resource->is_topic)
                        ? preg_replace('#\/\d+(?:\?.*)?#', '', $_SERVER['REQUEST_URI'])
                        : preg_replace('#\?.*#', '', $_SERVER['REQUEST_URI']);
                    /** @noinspection PhpUndefinedFieldInspection */
                    $fenom->switch_link = $link;
                }

                $fenom->addModifier('avatar', function ($data, $size = 30, $tpl = null) use ($app) {
                    return $app->avatar($data, $size, $tpl);
                });

                $fenom->addModifier('processor', function ($action, array $data = []) use ($app) {
                    return $app->runProcessor($action, $data);
                });

                $fenom->addModifier('website', function ($input, $options = 'www.') {
                    if (!$url = parse_url($input)) {
                        return $input;
                    }
                    $output = $url['host'];
                    if (!empty($options)) {
                        $remove = array_map('trim', explode(',', $options));
                        $output = str_replace($remove, '', $output);
                    }

                    return strtolower($output);
                });

                $fenom->addModifier('prism', function ($input) {
                    /** @noinspection HtmlUnknownAttribute */
                    preg_match_all('#(?:<pre><code>|<pre class\="prettyprint">)(.*?)(?:</code></pre>|</pre>)#s', $input, $code);
                    foreach ($code[0] as $idx => $from) {
                        // html, css, javascript
                        $lang = 'markup';
                        $content = str_replace(
                            ['&#91;', '&#93;', '&#96;', '&#123;', '&#125;'],
                            ['[', ']', '``', '{', '}'],
                            htmlspecialchars_decode($from)
                        );
                        if (strpos($content, '<?') !== false || strpos($content, '->') !== false) {
                            $lang = 'php';
                        } elseif (preg_match('#=`\w+`#s', $content) || preg_match('#\[\[#s', $content)) {
                            //$lang = 'modx';
                        } elseif (preg_match('#\b(select|from|update|table|insert|into)\b#is', $input)) {
                            $lang = 'sql';
                        } elseif (preg_match('#\b(location|include|server)\b#s', $input)) {
                            $lang = 'nginx';
                        } elseif (preg_match('#\{(\$|\/|\w+(\s|\(|\|)|\(|\')#', $content)) {
                            $lang = 'smarty';
                        }
                        $input = str_replace(
                            $from,
                            '<pre><code class="language-' . $lang . '">' . trim($code[1][$idx]) . '</code></pre>',
                            $input
                        );
                    }

                    return $input;
                });

                $fenom->addModifier('jevix', function ($input) use ($pdo) {
                    $output = $pdo->runSnippet('Jevix@Typography', [
                        'input' => $input,
                    ]);

                    $output = preg_replace("#(<br>\r\n){3,}#", '<br><br>', $output);

                    return $output;
                });

                $fenom->addModifier('escape', function ($input) use ($pdo) {
                    $output = str_replace(['[', ']', '{', '}'], ['&#91;', '&#93;', '&#123;', '&#125;'], $input);

                    return $output;
                });

                $fenom->addModifier('author', function ($user, $field = 'rating') {
                    if (is_numeric($user)) {
                        $id = $user;
                    } elseif (is_array($user) && !empty($user['id'])) {
                        $id = $user['id'];
                    } else {
                        return false;
                    }
                    if (!$author = $this->modx->getObject('comAuthor', (int)$id)) {
                        return false;
                    }

                    return $author->$field;
                });

                $fenom->addModifier('number', function ($number, $decimals = 0) {
                    return number_format($number, $decimals, '.', ' ');
                });

                $fenom->addModifier('subscribed', function ($id, $class = 'comSection') {
                    return $this->modx->getCount('comSubscriber', [
                        'id' => $id,
                        'createdby' => $this->modx->user->id,
                        'class' => $class,
                    ]);
                });

                $fenom->addModifier('abs_url', function ($input) {
                    preg_match_all('#(?:href|src)=[\'|"](.*?)[\'|"]#s', $input, $matches);
                    if (!empty($matches[1])) {
                        $url = $this->modx->getOption('site_url');
                        foreach ($matches[1] as $v) {
                            if (strpos($v, '://') === false) {
                                $input = str_replace($v, $url . ltrim($v, '/'), $input);
                            }
                        }
                    }

                    return $input;
                });
                break;

            case 'OnHandleRequest':
                if ($this->modx->context->key != 'mgr') {
//                    echo '<pre>';print_r($_SESSION);die;
                    /** @var AppRouter $router */
                    if ($router = $this->modx->getService('AppRouter', 'AppRouter', $this->config['modelPath'])) {
                        $router->process();
                    }
                }
                break;

            case 'OnLoadWebDocument':
                break;

            case 'OnUserFormSave':
                /** @var modUser $user */
                if (!$username = $this->modx->getObject('appUserName', ['username' => $user->username])) {
                    /** @var appUserName $username */
                    $username = $this->modx->newObject('appUserName');
                    $username->fromArray([
                        'username' => $user->username,
                        'user_id' => $user->id,
                    ], '', true, true);
                    $username->save();
                }
                /** @var string $mode */
                if ($mode == modSystemEvent::MODE_NEW) {
                    $user->Profile->set('usename', true);
                    $user->Profile->save();
                }
                break;

            case 'OnPageNotFound':
                break;

            case 'OnWebPagePrerender':
                // Compress output html for Google
                //$this->modx->resource->_output = preg_replace('#\s+#', ' ', $this->modx->resource->_output);
                break;
        }
    }


    /**
     * @param $to
     * @param $subject
     * @param string $body
     * @param array $properties
     *
     * @return bool
     */
    public function sendEmail($to, $subject, $body = '', array $properties = [])
    {
        if (is_numeric($to)) {
            /** @var modUserProfile $profile */
            if ($profile = $this->modx->getObject('modUserProfile', ['internalKey' => $to])) {
                $to = $profile->email;
            } else {
                return false;
            }
        }
        /** @var appMailQueue $queue */
        $queue = $this->modx->newObject('appMailQueue', [
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
                'properties' => $properties,
            ]
        );

        return $this->modx->getOption('app_mail_queue', null, false)
            ? $queue->save()
            : $queue->send();
    }


    /**
     * @param $data
     * @param int $size
     * @param null $tpl
     *
     * @return mixed
     */
    public function avatar($data, $size = 30, $tpl = null)
    {
        if (is_numeric($data)) {
            $data = [];
            if ($user = $this->modx->getObject('modUserProfile', ['internalKey' => (int)$data])) {
                $data = $user->get(['photo', 'email']);
                $data['id'] = $user->id;
            }
        }
        if (empty($tpl)) {
            $tpl = '<div class="avatar"><a href="{link}"><img src="{image1}" width="{width}" height="{height}" srcset="{image2} 2x" alt="{alt}"></a></div>';
        }
        $id = !empty($data['createdby'])
            ? $data['createdby']
            : $data['id'];

        if (!$image = $this->pdoTools->getStore($id . '-' . $size, 'avatars')) {
            $image = $this->getAvatar($data, [$size, $size * 2]);
            $this->pdoTools->setStore($id . '-' . $size, $image, 'avatars');
        }

        $data = [
            '{link}' => '/users/' . ($data['usename'] ? $data['username'] : $id),
            '{image1}' => $image[$size],
            '{image2}' => $image[$size * 2],
            '{width}' => $size,
            '{height}' => $size,
            '{alt}' => @$data['fullname'] ?: '',
        ];
        $avatar = str_replace(array_keys($data), array_values($data), $tpl);

        return $avatar;
    }


    /**
     * @param $data
     * @param array $sizes
     *
     * @return array
     */
    public function getAvatar($data, array $sizes)
    {
        $path = MODX_ASSETS_PATH . $this::avatars_path;
        $url = MODX_ASSETS_URL . $this::avatars_path;

        $id = !empty($data['createdby'])
            ? $data['createdby']
            : $data['id'];
        $gravatar = 'https://www.gravatar.com/avatar/' . md5(strtolower($data['email']));
        $output = [];

        // Download of images from Gravatar is still slow, so disable it
        if (empty($data['photo'])) {
            foreach ($sizes as $size) {
                $output[$size] = $gravatar . '?d=mm&s=' . $size;
            }

            return $output;
        }

        $file = explode('/', $data['photo'] ?: $gravatar);
        $file = array_pop($file);
        $file = explode('.', $file);
        $file = array_shift($file);

        if (!is_dir($path)) {
            mkdir($path);
        }
        $user_path = $id . '/';
        if (!is_dir($path . $user_path)) {
            mkdir($path . $user_path);
        }

        sort($sizes);
        $size = reset($sizes);
        $src = '';
        if (!$time = @filemtime($path . $user_path . $file . '-' . $size . '.jpg')) {
            // Remove old avatars
            if ($files = array_diff(scandir($path . $user_path), ['.', '..'])) {
                foreach ($files as $tmp) {
                    if (strpos($path . $user_path . $tmp, $file) === false) {
                        @unlink($path . $user_path . $tmp);
                    }
                }
            }
            // Copy and process new
            if (!empty($data['photo'])) {
                $src = strpos($data['photo'], '//') === false
                    ? MODX_BASE_PATH . ltrim(parse_url($data['photo'], PHP_URL_PATH), '/')
                    : $data['photo'];
            } else {
                $image = file_get_contents($gravatar . '?d=mm&s=200');
                foreach ($sizes as $size) {
                    $src = $path . $user_path . $file . '-' . $size . '.jpg';
                    file_put_contents($src, $image);
                }
            }
            foreach ($sizes as $size) {
                if ($image = $this->makeThumbnail($src, ['w' => $size, 'h' => $size])) {
                    if (file_put_contents($path . $user_path . $file . '-' . $size . '.jpg', $image)) {
                        $output[$size] = $url . $user_path . $file . '-' . $size . '.jpg?t=' . time();
                    }
                }
            }
        } elseif (empty($data['photo']) && time() - $time > $this::gravatars_cache) {
            // Check cache of Gravatar
            $headers = get_headers($gravatar . '?d=404', true);
            if (strpos($headers[0], '200 OK') !== false && strtotime($headers['Last-Modified']) > $time) {
                foreach ($sizes as $size) {
                    @unlink($path . $user_path . $file . '-' . $size . '.jpg');
                }

                return $this->getAvatar($data, $sizes);
            } else {
                $time = time();
                foreach ($sizes as $size) {
                    touch($path . $user_path . $file . '-' . $size . '.jpg', $time);
                    $output[$size] = $url . $user_path . $file . '-' . $size . '.jpg?t=' . $time;
                }
            }
        } else {
            foreach ($sizes as $size) {
                $output[$size] = $url . $user_path . $file . '-' . $size . '.jpg?t=' . $time;
            }
        }

        return $output;
    }


    /**
     * @param $src
     * @param array $options
     *
     * @return bool|mixed
     */
    public function makeThumbnail($src, $options = [])
    {
        if (empty($src)) {
            return false;
        }
        if (!class_exists('modPhpThumb')) {
            /** @noinspection PhpIncludeInspection */
            require MODX_CORE_PATH . 'model/phpthumb/modphpthumb.class.php';
        }

        /** @noinspection PhpParamsInspection */
        $phpThumb = new modPhpThumb($this->modx);
        $phpThumb->initialize();
        $phpThumb->setSourceFilename($src);
        foreach ($options as $k => $v) {
            $phpThumb->setParameter($k, $v);
        }
        if ($phpThumb->GenerateThumbnail()) {
            if ($phpThumb->RenderOutput()) {

                return $phpThumb->outputImageData;
            }
        }
        $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not generate thumbnail for "' . $src . '". ' . print_r($phpThumb->debugmessages, 1));

        return false;
    }


    /**
     * @param $section
     * @param string $type
     *
     * @return mixed
     */
    public function getProperties($section, $type = 'topic')
    {
        $properties = isset($this->properties[$section])
            ? $this->properties[$section]
            : $this->properties['default'];

        $properties[$type] = !empty($properties[$type])
            ? array_merge($this->properties['default'][$type], $properties[$type])
            : $this->properties['default'][$type];

        return $properties[$type];
    }


    /**
     * @param string $string
     *
     * @return array|mixed|null|string|string[]
     */
    public function sanitizeString($string = '')
    {
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $string[$key] = $this->sanitizeString($value);
            }

            return $string;
        }

        $string = htmlentities(trim($string), ENT_QUOTES, 'UTF-8');
        $string = preg_replace('#^@.*\b#', '', $string);
        $string = str_replace(
            ['[', ']', '`', '{', '}'],
            ['&#91;', '&#93;', '&#96;', '&#123;', '&#125;'],
            $string
        );

        return $string;
    }


    /**
     *
     */
    protected function updateModel()
    {
        if ($this->modx->loadClass('modUser')) {
            // Remove Tickets connection
            unset($this->modx->map['modUser']['composites']['AuthorProfile']);
        }
        if ($this->modx->loadClass('modUserProfile')) {
            $this->modx->map['modUserProfile']['fields']['feedback'] =
            $this->modx->map['modUserProfile']['fields']['usename'] =
            $this->modx->map['modUserProfile']['fields']['work'] = false;
            $this->modx->map['modUserProfile']['fieldMeta']['feedback'] =
            $this->modx->map['modUserProfile']['fieldMeta']['usename'] =
            $this->modx->map['modUserProfile']['fieldMeta']['work'] = [
                'dbtype' => 'tinyint',
                'precision' => 1,
                'phptype' => 'bool',
                'null' => true,
                'default' => 0,
            ];
            $this->modx->map['modUserProfile']['indexes']['work'] = [
                'alias' => 'work',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => ['work' => ['length' => '', 'collation' => 'A', 'null' => false]],
            ];
        }
    }

}