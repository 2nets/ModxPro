<?php

require_once dirname(dirname(dirname(__FILE__))) . '/getlist.class.php';

class TopicGetListProcessor extends AppGetListProcessor
{
    public $objectType = 'comTopic';
    public $classKey = 'comTopic';
    public $defaultSortField = 'createdon';
    public $defaultSortDirection = 'desc';

    public $getPages = true;
    public $tpl = '@FILE chunks/topics/list.tpl';


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        if ($this->getProperty('showSection')) {
            $c->leftJoin('comSection', 'Section');
            $c->select('Section.pagetitle as section_title, Section.uri as section_uri');
        }
        $where = [
            $this->classKey . '.published' => true,
        ];
        if ($user = (int)$this->getProperty('user')) {
            $where[$this->classKey . '.createdby'] = $user;
        } elseif ($favorites = (int)$this->getProperty('favorites')) {
            $q = $this->modx->newQuery('comStar', ['createdby' => $favorites, 'class' => 'comTopic']);
            $tstart = microtime(true);
            if ($q->prepare() && $q->stmt->execute()) {
                $this->modx->queryTime += microtime(true) - $tstart;
                $this->modx->executedQueries++;
                $where[$this->classKey . '.id:IN'] = $q->stmt->fetchAll(PDO::FETCH_COLUMN) ?: -1;
            }
        } else {
            $where['context'] = $this->modx->context->key;
        }

        if ($tmp = $this->getProperty('where', [])) {
            $where = array_merge($where, $tmp);
        }

        if ($where) {
            $c->where($where);
        }
        /*
        if ($query = $this->getProperty('query')) {
            if (is_numeric($query)) {
                $c->where([
                    $this->classKey . '.createdby' => (int)$query,
                ]);
            } else {
                $query = trim($query);
                $c->where([
                    $this->classKey . '.text:LIKE' => "%{$query}%",
                ]);
            }
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
        $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey, '', ['content'], true));
        if (!$this->getProperty('fastMode')) {
            $c->leftJoin('modUser', 'User');
            $c->leftJoin('modUserProfile', 'UserProfile');
            if ($this->modx->user->id) {
                $c->leftJoin('comThread', 'Thread');
                $c->select('Thread.id as thread');
                $c->leftJoin('comStar', 'Star', 'Star.id = comTopic.id AND Star.class = "comTopic" AND Star.createdby = ' . $this->modx->user->id);
                $c->select('Star.id as star');
                $c->leftJoin('comVote', 'Vote', 'Vote.id = comTopic.id AND Vote.class = "comTopic" AND Vote.createdby = ' . $this->modx->user->id);
                $c->select('Vote.value as vote');
            }
            $c->select('User.username');
            $c->select('UserProfile.photo, UserProfile.email, UserProfile.fullname, UserProfile.usename');
        }

        return $c;
    }


    /**
     * @param array $array
     *
     * @return array
     */
    public function prepareArray(array $array)
    {
        if (!$this->getProperty('fastMode') && $this->modx->user->id) {
            /** @var comView $view */
            $view = $this->App->pdoTools->getArray(
                'comView',
                ['topic_id' => $array['id'], 'user_id' => $this->modx->user->id],
                ['select' => 'timestamp']
            );
            if ($view && !empty($array['thread'])) {
                $array['new_comments'] = $this->modx->getCount('comComment', [
                    'thread' => $array['thread'],
                    'createdon:>' => $view['timestamp'],
                    'createdby:!=' => $this->modx->user->id,
                ]);
            }
        } else {
            $array['new'] = 0;
        }

        $properties = $this->App->getProperties($array['section_uri']);
        $array['can_vote'] = $this->modx->user->isAuthenticated($this->modx->context->key) &&
            (strtotime($array['createdon']) + $properties['voting']) > time();

        return $array;
    }

}

return 'TopicGetListProcessor';