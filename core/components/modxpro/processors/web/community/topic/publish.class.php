<?php

require_once dirname(__FILE__) . '/update.class.php';

class TopicPublishProcessor extends TopicUpdateProcessor
{
    /**
     * @return bool
     */
    public function beforeSet()
    {
        $this->properties = [
            'published' => true,
            'editedon' => date('Y-m-d H:i:s'),
            'editedby' => $this->modx->user->id,
        ];
        if (!$this->object->publishedby) {
            $this->properties['publishedon'] = date('Y-m-d H:i:s');
            $this->properties['publishedby'] = $this->modx->user->id;
        }

        return true;
    }

}

return 'TopicPublishProcessor';