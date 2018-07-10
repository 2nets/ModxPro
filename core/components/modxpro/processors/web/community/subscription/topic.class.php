<?php

class CommunitySubscriptionTopicProcessor extends modProcessor
{
    public $classKey = 'comSubscriber';


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
            'createdby' => $this->modx->user->id,
        ];

        /** @var comTopic $object */
        $object = $this->modx->getObject('comTopic', ['id' => $key['id'], 'published' => true]);
        if (!$object) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        /** @var comSubscriber $subscriber */
        if (!$subscriber = $this->modx->getObject($this->classKey, $key)) {
            $subscriber = $this->modx->newObject($this->classKey, $key);
            $subscriber->fromArray($key, '', true, true);
            $subscriber->set('createdon', date('Y-m-d H:i:s'));
            $subscriber->save();

            return $this->success($this->modx->lexicon('topic_subscribe'));
        } else {
            $subscriber->remove();

            return $this->success($this->modx->lexicon('topic_unsubscribe'));
        }
    }

}

return 'CommunitySubscriptionTopicProcessor';