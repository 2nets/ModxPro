<?php

require_once dirname(dirname(dirname(__FILE__))) . '/getlist.class.php';

class CommentGetCommentsProcessor extends AppGetListProcessor
{
    public $objectType = 'comComment';
    public $classKey = 'comComment';
    public $defaultSortField = 'createdon';
    public $defaultSortDirection = 'asc';

    public $_max_limit = 0;
    public $getPages = false;
    public $tpl = '@FILE chunks/comments/comments.tpl';
    /** @var comTopic $topic */
    protected $topic;
    /** @var array $props */
    protected $props;


    /**
     * @return bool
     */
    public function initialize()
    {
        $initialize = parent::initialize();

        if (!$this->topic = $this->modx->getObject('comTopic', (int)$this->getProperty('topic'))) {
            return $this->modx->lexicon('access_denied');
        }
        /** @var comSection $section */
        if ($section = $this->topic->getOne('Section')) {
            $this->props = $this->App->getProperties($section->alias, 'comment');
        } else {
            return $this->modx->lexicon('access_denied');
        }

        return $initialize;
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->where([
            'topic' => $this->topic->id,
        ]);

        return $c;
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $c->leftJoin('modUser', 'User');
        $c->leftJoin('modUserProfile', 'UserProfile');
        if ($this->modx->user->id) {
            $c->leftJoin('comStar', 'Star', 'Star.id = comComment.id AND Star.class = "comComment" AND Star.createdby = ' . $this->modx->user->id);
            $c->select('Star.id as star');
            $c->leftJoin('comVote', 'Vote', 'Vote.id = comComment.id AND Vote.class = "comComment" AND Vote.createdby = ' . $this->modx->user->id);
            $c->select('Vote.value as vote');
        }
        $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey));
        $c->select('User.username');
        $c->select('UserProfile.fullname, UserProfile.photo, UserProfile.email, UserProfile.usename');


        return $c;
    }


    /**
     * @param array $array
     * @param bool $count
     *
     * @return array
     */
    public function outputArray(array $array, $count = false)
    {
        $count = count($array);
        /** @var comView $view */
        $view = $this->modx->getObject('comView', [
            'topic' => $this->topic->id,
            'createdby' => $this->modx->user->id,
        ]);
        $array = [
            'comments' => $this->buildTree($array),
            'topic' => $this->topic->get(['id', 'createdby', 'comments']),
            'seen' => $view
                ? $view->get('createdon')
                : false,
            'new' => $view
                ? $this->modx->getCount($this->classKey, ['createdon:>' => $view->createdon, 'topic' => $this->topic->id])
                : 0,
        ];

        return parent::outputArray($array, $count);
    }


    /**
     * @param array $tmp
     * @param string $id
     * @param string $parent
     * @param array $roots
     *
     * @return array
     */
    public function buildTree($tmp = [], $id = 'id', $parent = 'parent', array $roots = [])
    {
        $rows = $tree = [];
        foreach ($tmp as $v) {
            $rows[$v[$id]] = $v;
        }

        foreach ($rows as $id => &$row) {
            if (empty($row[$parent]) || (!isset($rows[$row[$parent]]) && in_array($id, $roots))) {
                $tree[$id] = &$row;
            } else {
                $rows[$row[$parent]]['children'][$id] = &$row;
            }
        }

        return $tree;
    }


    /**
     * @param array $array
     *
     * @return array
     */
    public function prepareArray(array $array)
    {
        $time = time();
        $array['can_vote'] = !$array['deleted'] &&
            $this->modx->user->isAuthenticated($this->modx->context->key) &&
            $array['createdby'] != $this->modx->user->id &&
            (strtotime($array['createdon']) + $this->props['voting']) > $time;

        $array['can_edit'] = !$array['deleted'] && (
                $this->modx->user->isMember('Administrator') || (
                    empty($array['children']) && $this->modx->user->id == $array['createdby'] &&
                    (strtotime($array['createdon']) + $this->props['edit']) > $time
                )
            );

        return $array;
    }

}

return 'CommentGetCommentsProcessor';