<?php

/**
 * @property int id
 * @property string pagetitle
 * @property string content
 * @property int parent
 * @property int createdby
 * @property string createdon
 * @property bool published
 * @property int views
 * @property int comments
 * @property int stars
 * @property float rating
 * @property int publishedby
 * @property int publishedon
 */
class comTopic extends xPDOSimpleObject
{
    /** @var array $section */
    protected $properties = [];


    /**
     * @return array
     */
    public function getProperties()
    {
        if (empty($this->properties)) {
            /** @var comSection $section */
            if ($section = $this->getOne('Section')) {
                /** @var App $App */
                $App = $this->xpdo->getService('App');
                $this->properties = $App->getProperties($section->alias, 'topic');
            }
        }

        return $this->properties;
    }


    /**
     * @return bool
     */
    public function canEdit() {
        if (!($this->xpdo instanceof modX)) {
            return false;
        }

        return $this->xpdo->user->isMember('Administrator') || $this->createdby == $this->xpdo->user->id;
    }


    /**
     * @return bool
     */
    public function canVote()
    {
        if (!($this->xpdo instanceof modX) || $this->createdby == $this->xpdo->user->id || !$this->published) {
            return false;
        }

        return $this->xpdo->user->isAuthenticated() &&
            (strtotime($this->publishedon) + $this->getProperties()['voting']) > time();
    }


    /**
     * @param null $content
     * @param bool $jevix
     *
     * @return mixed|null|string|string[]
     */
    function getIntro($content = null, $jevix = true)
    {
        if (empty($content)) {
            $content = parent::get('content');
        }
        $content = preg_replace('#<cut(.*?)>#i', '<cut/>', $content);
        $introtext = !preg_match('#<cut\/>#', $content)
            ? $content
            : explode('<cut/>', $content)[0];
        /** @var App $App */
        if ($jevix && $App = $this->xpdo->getService('App')) {
            $introtext = $App->pdoTools->runSnippet('Jevix@Typography', [
                'input' => $introtext,
            ]);
        }

        return $introtext;
    }


    /**
     *
     */
    public function addView()
    {
        if (!($this->xpdo instanceof modX)) {
            return;
        }
        if ($this->xpdo->user->id) {
            $key = [
                'topic' => $this->id,
                'createdby' => $this->xpdo->user->id,
            ];
            if (!$view = $this->xpdo->getObject('comView', $key)) {
                /** @var comView $view */
                $view = $this->xpdo->newObject('comView');
                $view->fromArray($key, '', true, true);
                $this->set('views', $this->views + 1);
                $this->save();
            }
            $view->set('createdon', date('Y-m-d H:i:s'));
            $view->save();
        } else {
            $views = !empty($_COOKIE['community-views'])
                ? explode('-', $_COOKIE['community-views'])
                : [];
            if (!in_array($this->id, $views)) {
                $views[] = $this->id;
                $this->set('views', $this->views + 1);
                $this->save();
            }
            setcookie(
                'community-views', implode('-', $views), time() + (365 * 86400), '/',
                preg_replace('#^en\.#', '', $_SERVER['HTTP_HOST'])
            );
        }
    }


    /**
     * @param bool $save
     *
     * @return int
     */
    public function views($save = false)
    {
        $views = $this->xpdo->getCount('comView', ['topic' => $this->id]);
        if ($save) {
            parent::set('views', $views);
            parent::save();
        }

        return $views;
    }


    /**
     * @param bool $save
     *
     * @return int
     */
    public function stars($save = false)
    {
        $stars = $this->xpdo->getCount('comStar', ['id' => $this->id, 'class' => self::class]);
        if ($save) {
            parent::set('stars', $stars);
            parent::save();
        }

        return $stars;
    }


    /**
     * @param bool $save
     *
     * @return int
     */
    public function comments($save = false)
    {
        $comments = $this->xpdo->getCount('comComment', ['topic' => $this->id]);
        if ($save) {
            parent::set('comments', $comments);
            parent::save();
        }

        return $comments;
    }


    /**
     * @return int
     */
    public function updateLast()
    {
        $id = 0;

        $c = $this->xpdo->newQuery('comComment', ['topic' => $this->id, 'deleted' => false]);
        $c->sortby('id', 'desc');
        $c->limit(1);
        /** @var comComment $last */
        if ($last = $this->xpdo->getObject('comComment', $c)) {
            $this->set('last_comment', $last->id);
            $id = $last->id;
        } else {
            $this->set('last_comment', 0);
        }
        $this->save();

        return $id;
    }


    /**
     * @param bool $save
     *
     * @return int
     */
    public function rating($save = false)
    {
        $rating = 0;

        $c = $this->xpdo->newQuery('comVote', ['id' => $this->id, 'class' => self::class]);
        $c->select('SUM(value)');
        if ($c->prepare() && $c->stmt->execute()) {
            $rating = $c->stmt->fetchColumn();
            if ($rating !== false && $save) {
                parent::set('rating', $rating);
                parent::save();
            }
        }

        return $rating;
    }


    /**
     * @param string $k
     * @param null $v
     * @param string $vType
     *
     * @return bool
     */
    public function set($k, $v = null, $vType = '')
    {
        if (is_string($k) && is_numeric($v) && preg_match('#on$#', $k)) {
            $v = !empty($v)
                ? date('Y-m-d H:i:s', $v)
                : null;
        }

        return parent::set($k, $v, $vType);
    }


    /**
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null)
    {
        if ($this->isDirty('parent')) {
            if ($this->isNew()) {
                parent::save($cacheFlag);
            }
            if ($section = $this->getOne('Section')) {
                parent::set('uri', rtrim($section->get('uri'), '/') . '/' . parent::get('id'));
            }
        }

        return parent::save($cacheFlag);
    }


    /**
     * @param $id
     */
    public function subscribe($id)
    {
        $key = [
            'id' => $this->id,
            'class' => 'comTopic',
            'createdby' => $id,
        ];
        if (!$this->xpdo->getCount('comSubscriber', $key)) {
            $obj = $this->xpdo->newObject('comSubscriber');
            $obj->fromArray($key, '', true, true);
            $obj->save();
        }
    }

}