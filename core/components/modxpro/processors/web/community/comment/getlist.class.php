<?php

require_once dirname(dirname(dirname(__FILE__))) . '/getlist.class.php';

class CommentGetListProcessor extends AppGetListProcessor
{
    public $objectType = 'comComment';
    public $classKey = 'comComment';
    public $defaultSortField = 'createdon';
    public $defaultSortDirection = 'desc';

    public $getPages = true;
    public $tpl = '@FILE chunks/comments/list.tpl';


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $where = [
            $this->classKey . '.deleted' => false,
        ];

        if ($user = (int)$this->getProperty('user')) {
            $where[$this->classKey . '.createdby'] = $user;
        } elseif ($favorites = (int)$this->getProperty('favorites')) {
            $q = $this->modx->newQuery('comStar', ['createdby' => $favorites, 'class' => 'comComment']);
            $tstart = microtime(true);
            if ($q->prepare() && $q->stmt->execute()) {
                $this->modx->queryTime += microtime(true) - $tstart;
                $this->modx->executedQueries++;
                $where[$this->classKey . '.id:IN'] = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        } else {
            $where[$this->classKey . '.context'] = $this->modx->context->key;
        }

        if ($tmp = $this->getProperty('where', [])) {
            $where = array_merge($where, $tmp);
        }

        if ($where) {
            $c->where($where);
        }
        /*
        if ($query = $this->getProperty('query')) {
            $query = trim($query);
            $c->where([
                $this->classKey . '.text:LIKE' => "%{$query}%",
            ]);
        }*/

        return $c;
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $c->leftJoin('comThread', 'Thread');
        $c->leftJoin('comTopic', 'Topic', 'Thread.topic = Topic.id');
        $c->leftJoin('comSection', 'Section', 'Section.id = Topic.parent');
        $c->leftJoin('modUser', 'User');
        $c->leftJoin('modUserProfile', 'UserProfile');
        if ($this->modx->user->id) {
            $c->leftJoin('comStar', 'Star', 'Star.id = comComment.id AND Star.class = "comComment" AND Star.createdby = ' . $this->modx->user->id);
            $c->select('Star.id as star');
            $c->leftJoin('comVote', 'Vote', 'Vote.id = comComment.id AND Vote.class = "comComment" AND Vote.createdby = ' . $this->modx->user->id);
            $c->select('Vote.value as vote');
        }
        $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey));
        $c->select('Thread.topic, Thread.comments');
        $c->select('User.username');
        $c->select('UserProfile.fullname, UserProfile.photo, UserProfile.email, UserProfile.usename');
        $c->select('Topic.pagetitle as topic_title, Section.uri, Section.pagetitle as section_title');

        return $c;
    }


    /**
     * @param array $array
     *
     * @return array
     */
    public function prepareArray(array $array)
    {
        $properties = $this->App->getProperties($array['uri']);
        $array['can_vote'] = $this->modx->user->isAuthenticated($this->modx->context->key) &&
            (strtotime($array['createdon']) + $properties['voting']) > time();

        return $array;
    }

}

return 'CommentGetListProcessor';