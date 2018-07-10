<?php

class TopicPreviewProcessor extends modProcessor
{

    public $tpl = '@FILE chunks/topics/_preview.tpl';


    public function initialize()
    {
        return $this->modx->user->isAuthenticated($this->modx->context->key);
    }


    public function process()
    {
        /** @var App $App */
        $App = $this->modx->getService('App');

        $data = array_map('trim', $this->getProperties());
        if (empty($data['content'])) {
            return $this->failure($this->modx->lexicon('topic_err_no_content'));
        }

        return $this->success('', [
            'html' => $App->pdoTools->getChunk($this->tpl, $data),
        ]);
    }

}

return 'TopicPreviewProcessor';