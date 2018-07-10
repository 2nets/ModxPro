<?php

require_once dirname(__FILE__) . '/_processor.class.php';

class CommentCreateProcessor extends modObjectCreateProcessor
{
    use CommentProcessor;

    public $classKey = 'comComment';
    /** @var comComment $object */
    public $object;
    /** @var App $App */
    public $App;
    public $tpl = '@FILE chunks/comments/_comment.tpl';
    public $email = '@FILE chunks/email/community/comment.tpl';


    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        if (!$this->modx->user->isAuthenticated($this->modx->context->key)) {
            return $this->modx->lexicon('access_denied');
        }
        $this->App = $this->modx->getService('App');

        return parent::initialize();
    }


    /**
     * @return bool|null|string
     */
    public function beforeSet()
    {
        $content = trim($this->getProperty('content'));
        if (empty($content)) {
            return $this->modx->lexicon('comment_err_no_content');
        }
        $topic = (int)$this->getProperty('topic');
        if (!$topic = $this->modx->getObject('comTopic', ['id' => $topic, 'closed' => false])) {
            return $this->modx->lexicon('comment_err_topic');
        }
        $this->properties = [
            'content' => $content,
            'topic' => $topic->id,
            'parent' => (int)$this->getProperty('parent'),
        ];

        return true;
    }


    public function beforeSave()
    {
        $this->modx->getRequest();
        /** @var modRequest $request */
        $request = $this->modx->request;
        $content = $this->object->get('content');
        $this->object->fromArray([
            'raw' => $content,
            'content' => $this->App->pdoTools->runSnippet('Jevix@Typography', [
                'input' => $content,
            ]),
            'createdon' => time(),
            'createdby' => $this->modx->user->id,
            'context' => $this->modx->context->key,
            'ip' => $request->getClientIp()['ip'],
        ]);

        return true;
    }


    /**
     * @return bool
     */
    public function afterSave()
    {
        /** @var comTopic $topic */
        if ($topic = $this->object->getOne('Topic')) {
            $topic->comments(true);
            $topic->updateLast();

            $data = [
                'comment' => $this->object->toArray(),
                'user' => $this->object->getOne('UserProfile')->toArray(),
                'topic' => $topic->toArray(),
            ];

            $sent = [$this->object->createdby];
            /** @var comComment $parent */
            if ($this->object->parent && $parent = $this->object->getOne('Parent')) {
                if ($this->object->createdby != $parent->createdby) {
                    $this->App->sendEmail(
                        $parent->createdby,
                        $this->modx->lexicon('subject_new_reply', ['topic' => $topic->pagetitle]),
                        $this->App->pdoTools->getChunk($this->email, $data)
                    );
                    $sent[] = $parent->createdby;
                }
            }

            $subscribers = $this->modx->getIterator('comSubscriber', [
                'id' => $topic->id,
                'class' => 'comTopic',
                'createdby:NOT IN' => $sent,
            ]);
            /** @var comSubscriber $subscriber */
            foreach ($subscribers as $subscriber) {
                $this->App->sendEmail(
                    $subscriber->createdby,
                    $this->modx->lexicon('subject_new_comment', ['topic' => $topic->pagetitle]),
                    $this->App->pdoTools->getChunk($this->email, $data)
                );
            }
        }

        return true;
    }

}

return 'CommentCreateProcessor';