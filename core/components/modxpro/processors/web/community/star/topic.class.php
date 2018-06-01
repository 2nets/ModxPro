<?php

class CommunityStarTopicProcessor extends modProcessor
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
            'class' => 'comTopic',
            'createdby' => $this->modx->user->id
        ];

        /** @var comStar $star */
        if (!$star = $this->modx->getObject($this->classKey, $key)) {
            $star = $this->modx->newObject($this->classKey, $key);
            $star->fromArray($key, '', true, true);
            $star->set('createdon', date('Y-m-d H:i:s'));
            /** @var comTopic $object */
            if ($object = $this->modx->getObject('comTopic', $key['id'])) {
                $star->set('owner', $object->createdby);
            }
            $star->save();
        } else {
            $star->remove();
        }

        if ($topic = $star->getOne('Topic')) {
            $topic->set('stars', $this->modx->getCount('comStar', ['id' => $topic->id, 'class' => 'comTopic']));
            $topic->save();
        }

        return $this->success('', $topic ? $topic->get(['stars']) : 0);
    }

}

return 'CommunityStarTopicProcessor';