<?php

class CommunityStarCommentProcessor extends modObjectProcessor
{
    public $classKey = 'comStar';


    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        return $this->modx->user->isAuthenticated($this->modx->context->key)
            ? true
            : $this->modx->lexicon('access_denied');
    }


    /**
     * @return array|mixed|string
     */
    public function process()
    {
        $key = [
            'id' => (int)$this->getProperty('id'),
            'class' => 'comComment',
            'createdby' => $this->modx->user->id,
        ];

        /** @var comComment $object */
        $object = $this->modx->getObject('comComment', ['id' => $key['id'], 'deleted' => false]);
        if (!$object) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        /** @var comStar $star */
        if (!$star = $this->modx->getObject($this->classKey, $key)) {
            $star = $this->modx->newObject($this->classKey, $key);
            $star->fromArray($key, '', true, true);
            $star->set('createdon', date('Y-m-d H:i:s'));
            $star->set('owner', $object->createdby);
            $star->save();
        } else {
            $star->remove();
        }
        $object->stars(true);

        return $this->success('', $object->get(['stars']));
    }

}

return 'CommunityStarCommentProcessor';