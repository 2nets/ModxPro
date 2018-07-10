<?php

class CommentGetProcessor extends modObjectGetProcessor
{

    public $classKey = 'comComment';
    /** @var comComment $object */
    public $object;


    public function initialize()
    {
        if ($initialize = parent::initialize()) {
            if ($this->object->createdby != $this->modx->user->id && !$this->modx->user->isMember('Administrator')) {
                return $this->modx->lexicon('access_denied');
            }
        }

        return $initialize;
    }


    /**
     * @return array|string
     */
    public function cleanup()
    {
        return $this->success('', [
            'id' => $this->object->id,
            'parent' => $this->object->parent,
            'createdon' => $this->object->createdon,
            'content' => $this->object->raw,
        ]);
    }

}

return 'CommentGetProcessor';