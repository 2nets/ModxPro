<?php

class TopicGetSectionProcessor extends modProcessor
{

    public $tpl = '@FILE chunks/topics/_fields.tpl';
    public $fields = [
        'work' => [
            'days' => 'int',
            'money' => 'int',
        ],
        'crowdfunding' => [
            'days' => 'int',
            'money' => 'int',
        ],
    ];


    public function initialize()
    {
        return $this->modx->user->isAuthenticated($this->modx->context->key);
    }


    public function process()
    {
        /** @var App $App */
        $App = $this->modx->getService('App');
        /** @var comSection $section */
        $section = $this->modx->getObject('comSection', (int)$this->getProperty('id'));
        if (!$section) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $data = [
            'description' => $section->description,
            'properties' => $App->getProperties($section->alias),
            'fields' => '',
        ];
        if (!empty($this->fields[$section->alias])) {
            $properties = $this->getProperties();
            $properties['section'] = $section->get(['id', 'alias', 'pagetitle']);
            $properties['fields'] = $this->fields[$section->alias];
            $data['fields'] = $App->pdoTools->getChunk($this->tpl, $properties);
        }

        return $this->success('', $data);
    }

}

return 'TopicGetSectionProcessor';