<?php

require_once dirname(__FILE__) . '/_processor.class.php';

class CommentUpdateProcessor extends modObjectUpdateProcessor
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
        if ($initialize = parent::initialize()) {
            if ($this->object->createdby != $this->modx->user->id && !$this->modx->user->isMember('Administrator')) {
                return $this->modx->lexicon('access_denied');
            }
        }
        $this->App = $this->modx->getService('App');

        return $initialize;
    }


    /**
     * @return bool|null|string
     */
    public function beforeSet()
    {
        if (!$this->object->canEdit()) {
            return $this->modx->lexicon('comment_err_edit_time');
        }

        $content = trim($this->getProperty('content'));
        if (empty($content)) {
            return $this->modx->lexicon('comment_err_no_content');
        }

        $properties = [
            'content' => $content
        ];
        if ($this->modx->user->isMember('Administrator') && isset($this->properties['parent'])) {
            $properties['parent'] = (int)$this->getProperty('parent');
        }
        $this->properties = $properties;

        return true;
    }


    public function beforeSave()
    {
        $content = $this->object->get('content');
        $this->object->fromArray([
            'raw' => $content,
            'content' => $this->App->pdoTools->runSnippet('Jevix@Typography', [
                'input' => $content,
            ]),
            'editedon' => time(),
            'editedby' => $this->modx->user->id,
        ]);

        return true;
    }

}

return 'CommentUpdateProcessor';