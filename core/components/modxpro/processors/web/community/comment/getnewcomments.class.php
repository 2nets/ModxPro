<?php

require_once dirname(__FILE__) . '/getcomments.class.php';

class CommentGetNewCommentsProcessor extends CommentGetCommentsProcessor
{
    public $defaultSortField = 'id';
    public $tpl = '@FILE chunks/comments/_comment.tpl';
    /** @var comView $view */
    protected $view;


    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        if (!$this->modx->user->isAuthenticated($this->modx->context->key)) {
            return $this->modx->lexicon('access_denied');
        }
        $this->setProperty('limit', 0);

        return parent::initialize();
    }


    /**
     * @param array $array
     * @param bool $count
     *
     * @return array
     */
    public function outputArray(array $array, $count = false)
    {
        /** @var comView $view */
        $view = $this->modx->getObject('comView', ['createdby' => $this->modx->user->id, 'topic' => $this->topic->id]);
        $topic = $this->topic->get(['id', 'createdby', 'comments']);
        $array = $this->buildTree($array);

        $html = '';
        foreach ($array as $item) {
            $html .= $this->App->pdoTools->getChunk($this->tpl, [
                'item' => $item,
                'level' => 0,
                'seen' => $view
                    ? $view->createdon
                    : false,
                'topic' => $topic,
            ]);
        }

        $array = [
            'success' => true,
            'message' => '',
            'html' => $html,
            'topic' => $topic
        ];
        $this->topic->addView();

        return $array;
    }

}

return 'CommentGetNewCommentsProcessor';