<?php

require_once dirname(__FILE__) . '/update.class.php';

class TopicDraftProcessor extends TopicUpdateProcessor
{
    /**
     * @return bool
     */
    public function beforeSet()
    {
        $this->properties = [
            'published' => false,
            //'publishedon' => '0000-00-00 00:00:00',
            //'publishedby' => 0,
            'editedon' => date('Y-m-d H:i:s'),
            'editedby' => $this->modx->user->id,
        ];

        return true;
    }

}

return 'TopicDraftProcessor';