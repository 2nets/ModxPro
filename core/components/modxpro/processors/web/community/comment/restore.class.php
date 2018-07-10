<?php

require_once dirname(__FILE__) . '/_processor.class.php';

class CommentRestoreProcessor extends modObjectUpdateProcessor
{
    use CommentProcessor;

    public $classKey = 'comComment';
    /** @var comComment $object */
    public $object;
    /** @var App $App */
    public $App;
    public $tpl = '@FILE chunks/comments/_comment.tpl';


    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        if (!$this->modx->user->isMember('Administrator')) {
            return $this->modx->lexicon('access_denied');
        }
        $this->App = $this->modx->getService('App');

        return parent::initialize();
    }


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $this->properties = [
            'deleted' => false,
            'deletedon' => null,
            'deletedby' => null,
        ];

        return true;
    }


    /**
     * @return bool
     */
    public function afterSave()
    {
        /** @var comTopic $topic */
        if ($topic = $this->object->getOne('Topic')) {
            $topic->updateLast();
        }

        return true;
    }

}

return 'CommentRestoreProcessor';