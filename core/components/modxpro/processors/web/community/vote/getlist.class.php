<?php

require_once dirname(dirname(dirname(__FILE__))) . '/getlist.class.php';

class VotesGetListProcessor extends AppGetListProcessor
{
    public $objectType = 'comVote';
    public $classKey = 'comVote';
    public $defaultSortField = 'comVote.createdon';
    public $defaultSortDirection = 'desc';

    public $tpl = '@FILE chunks/topics/votes.tpl';
    protected $_max_limit = 0;


    public function initialize()
    {
        if (!$this->modx->user->isAuthenticated($this->modx->context->key)) {
            return $this->modx->lexicon('access_denied');
        }

        $initialize = parent::initialize();
        $this->setProperty('limit', 0);

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
            'id' => (int)$this->getProperty('id'),
            'class' => $this->getProperty('type') == 'topic'
                ? 'comTopic'
                : 'comComment',
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
        $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey, '', ['value', 'createdby']));
        $c->select('UserProfile.photo, User.username, UserProfile.email, UserProfile.fullname, UserProfile.usename');

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
        if ($count === false) {
            $count = count($array);
        }

        $output = [
            'success' => true,
            'total' => $count,
            'results' => $array,
        ];
        $output['results'] = $this->App->pdoTools->getChunk($this->getProperty('tpl', $this->tpl), $output);

        return $output;
    }

}

return 'VotesGetListProcessor';