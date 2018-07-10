<?php

require_once dirname(__FILE__) . '/_processor.class.php';

class TopicCreateProcessor extends modObjectCreateProcessor
{
    use TopicProcessor;

    public $classKey = 'comTopic';
    /** @var comTopic $object */
    public $object;


    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        if ($initialize = parent::initialize()) {
            if (!$this->modx->user->isAuthenticated($this->modx->context->key)) {
                return $this->modx->lexicon('access_denied');
            }
        }

        return $initialize;
    }


    /**
     * @return bool
     */
    public function beforeSave()
    {
        $this->object->fromArray([
            'createdon' => date('Y-m-d H:i:s'),
            'createdby' => $this->modx->user->id,
            'context' => $this->modx->context->key,
        ]);
        if ($this->published) {
            $this->object->fromArray([
                'published' => true,
                'publishedon' => date('Y-m-d H:i:s'),
                'publishedby' => $this->modx->user->id,
            ]);
        }
        $this->object->set('introtext', $this->object->getIntro());

        return true;
    }


    /**
     * @return bool
     */
    public function afterSave()
    {
        $this->object->subscribe($this->object->createdby);

        return true;
    }


    /**
     * @return array
     */
    public function cleanup()
    {
        return $this->success($this->modx->lexicon('topic_success'), [
            'redirect' => '/' . (!$this->published
                    ? 'topic/' . $this->object->get('id')
                    : $this->object->get('uri')
                ),
        ]);
    }

}

return 'TopicCreateProcessor';