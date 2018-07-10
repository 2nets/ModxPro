<?php

require_once dirname(__FILE__) . '/_processor.class.php';

class TopicUpdateProcessor extends modObjectUpdateProcessor
{
    use TopicProcessor;

    public $classKey = 'comTopic';
    /** @var comTopic $object */
    public $object;


    /**
     * @return bool|string
     */
    public function initialize()
    {
        if ($initialize = parent::initialize()) {
            if ($this->modx->user->id != $this->object->createdby && !$this->modx->user->isMember('Administrator')) {
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
        if ($this->published !== null) {
            $this->object->set('published', $this->published);
            if ($this->published && !$this->first_publication) {
                $this->object->fromArray([
                    'publishedon' => date('Y-m-d H:i:s'),
                    'publishedby' => $this->modx->user->id,
                ]);
            }
        }

        $this->object->fromArray([
            'editedon' => date('Y-m-d H:i:s'),
            'editedby' => $this->modx->user->id,
        ]);
        $this->object->set('introtext', $this->object->getIntro());

        return true;
    }


    /**
     * @return array
     */
    public function cleanup()
    {
        return $this->published !== null
            ? $this->success($this->modx->lexicon('topic_success'), [
                'redirect' => '/' . (!$this->published
                        ? 'topic/' . $this->object->get('id')
                        : $this->object->get('uri')
                    ),
            ])
            : $this->success($this->modx->lexicon('topic_success'), $this->object->get($this->fields));
    }

}

return 'TopicUpdateProcessor';