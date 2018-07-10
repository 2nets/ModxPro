<?php

require_once dirname(dirname(dirname(__FILE__))) . '/getlist.class.php';

class CommentGetLatestProcessor extends AppGetListProcessor
{
    public $objectType = 'comComment';
    public $classKey = 'comComment';
    public $defaultSortField = 'createdon';
    public $defaultSortDirection = 'desc';

    public $getCount = false;
    public $tpl = '@FILE chunks/comments/latest.tpl';


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $last = $this->App->pdoTools->getCollection('comTopic', [
            'context' => $this->modx->context->key,
        ], [
            'select' => [
                'comTopic' => 'last_comment',
            ],
            'sortby' => 'last_comment',
            'sortdir' => 'desc',
            'limit' => $this->getProperty('limit'),
            'setTotal' => false,
        ]);

        $where = [
            $this->classKey . '.deleted' => false,
            'id:IN' => [],
        ];
        foreach ($last as $v) {
            $where['id:IN'][] = $v['last_comment'];
        }
        $c->where($where);

        return $c;
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $c->leftJoin('comTopic', 'Topic');
        $c->leftJoin('modUser', 'User');
        $c->leftJoin('modUserProfile', 'UserProfile');

        $c->select('comComment.id, comComment.content, comComment.createdon, comComment.createdby');
        $c->select('User.username');
        $c->select('UserProfile.fullname, UserProfile.photo, UserProfile.email, UserProfile.usename');
        $c->select('Topic.pagetitle as pagetitle, Topic.uri as uri, Topic.comments');

        return $c;
    }

}

return 'CommentGetLatestProcessor';