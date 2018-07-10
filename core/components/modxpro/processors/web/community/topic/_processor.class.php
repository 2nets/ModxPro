<?php

/**
 * Trait TopicProcessor
 *
 * @property modX $modx
 * @property comTopic $object
 */
trait TopicProcessor
{
    /** @var App $App */
    public $App;
    /** @var array $fields Allowed and required fields of topic */
    public $fields = [
        'pagetitle' => 'string',
        'content' => 'text',
        'parent' => 'int',
    ];
    public $published = null;
    public $first_publication = false;
    public $max_length = 1000;
    public $email = '@FILE chunks/email/community/topic.tpl';


    /**
     * @return bool|null|string
     */
    public function beforeSet()
    {
        $this->App = $this->modx->getService('App');

        $pid = (int)$this->getProperty('parent');
        /** @var comSection $section */
        if (!$section = $this->modx->getObject('comSection', $pid)) {
            return $this->modx->lexicon('access_denied');
        } elseif ($this->object->parent != $pid && !$this->modx->user->isMember('Administrator')) {
            if (!$section->checkPolicy('save')) {
                return $this->modx->lexicon('topic_err_permission');
            }
            // Check rating
            $properties = $this->App->getProperties($section->alias);
            /** @var comAuthor $author */
            $author = $this->modx->getObject('comAuthor', $this->modx->user->id);
            if ($properties['required'] > $author->rating) {
                return $this->modx->lexicon('topic_err_rating', ['required' => $properties['required']]);
            }
        }

        if (isset($this->properties['published'])) {
            $this->published = (bool)$this->properties['published'];
            $this->first_publication = empty($this->object->publishedby);
        }

        $processor = require_once dirname(__FILE__) . '/getsection.class.php';
        $processor = new $processor($this->modx);
        if (isset($processor->fields[$section->alias])) {
            $this->fields = array_merge($this->fields, $processor->fields[$section->alias]);
        }
        foreach ($this->fields as $field => $type) {
            switch ($type) {
                case 'int':
                    $value = (int)$this->getProperty($field);
                    if (empty($value)) {
                        $this->addFieldError($field, $this->modx->lexicon('topic_err_empty_field'));
                    }
                    break;
                case 'text':
                    $value = trim($this->getProperty($field));
                    if (empty($value)) {
                        $this->addFieldError($field, $this->modx->lexicon('topic_err_empty_field'));
                    }
                    break;
                case 'bool':
                    $value = (bool)$this->getProperty($field);
                    break;
                default:
                    $value = trim($this->App->sanitizeString($this->getProperty($field)));
                    if (empty($value)) {
                        $this->addFieldError($field, $this->modx->lexicon('topic_err_empty_field'));
                    }
            }
            $properties[$field] = $value;
        }
        $this->properties = $properties;

        $length = mb_strlen(strip_tags($properties['content'], '<pre><code>'), 'UTF-8');
        if (!preg_match('#<cut\b.*?>#', $properties['content']) && $length > $this->max_length) {
            return $this->modx->lexicon('topic_err_cut', [
                'length' => $length,
                'max' => $this->max_length,
            ]);
        }

        return !$this->hasErrors();
    }


    /**
     *
     */
    public function afterSave()
    {
        parent::afterSave();

        if ($this->published && $this->first_publication && $section = $this->object->getOne('Section')) {
            $data = [
                'topic' => $this->object->toArray(),
                'user' => $this->object->getOne('UserProfile')->toArray(),
                'section' => $section->toArray(),
            ];

            $subscribers = $this->modx->getIterator('comSubscriber', [
                'id' => $this->object->parent,
                'class' => 'comSection',
                'createdby:!=' => $this->object->createdby,
            ]);
            /** @var comSubscriber $subscriber */
            foreach ($subscribers as $subscriber) {
                $this->App->sendEmail(
                    $subscriber->createdby,
                    $this->modx->lexicon('subject_new_topic', ['section' => $section->pagetitle]),
                    $this->App->pdoTools->getChunk($this->email, $data)
                );
            }
        }
    }
}