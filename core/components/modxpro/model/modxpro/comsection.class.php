<?php

class comSection extends modResource
{

    public $showInContextMenu = false;


    /**
     * @param xPDO $modx
     *
     * @return string
     */
    public static function getControllerPath(xPDO &$modx)
    {
        return MODX_CORE_PATH . 'components/modxpro/controllers/section/';
    }


    /**
     * @return array
     */
    public function getContextMenuText()
    {
        return [
            'text_create' => $this->xpdo->lexicon('section_type'),
            'text_create_here' => $this->xpdo->lexicon('section_type'),
        ];
    }


    /**
     * @return null|string
     */
    public function getResourceTypeName()
    {
        return $this->xpdo->lexicon('section');
    }

}