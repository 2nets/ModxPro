<?php

class CommentRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'comComment';
    /** @var comComment $object */
    public $object;


    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        if (!$this->modx->user->isMember('Administrator')) {
            return $this->modx->lexicon('access_denied');
        }

        return parent::initialize();
    }


    /**
     * @return array
     */
    public function process()
    {
        parent::process();

        /** @var comTopic $topic */
        if ($topic = $this->object->getOne('Topic')) {
            $topic->comments(true);
            $topic->updateLast();
        }
        
        return $this->success('', [
            'count' => $this->modx->getCount('comComment', ['topic' => $this->object->topic]),
        ]);
    }

}

return 'CommentRemoveProcessor';