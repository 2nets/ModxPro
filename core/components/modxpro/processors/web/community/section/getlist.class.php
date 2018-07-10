<?php

require_once dirname(dirname(dirname(__FILE__))) . '/getlist.class.php';

class SectionGetListProcessor extends AppGetListProcessor
{
    public $objectType = 'comSection';
    public $classKey = 'comSection';
    public $defaultSortField = 'comSection.publishedon';
    public $defaultSortDirection = 'desc';

    public $tpl = '@FILE chunks/sections/list.tpl';


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $where = [
            $this->classKey . '.class_key' => 'comSection',
            $this->classKey . '.published' => true,
            $this->classKey . '.deleted' => false,
            $this->classKey . '.context_key' => $this->modx->context->key,
        ];

        $c->groupby('comSection.id');

        if ($tmp = $this->getProperty('where', [])) {
            $where = array_merge($where, $tmp);
        }

        if ($where) {
            $c->where($where);
        }

        return $c;
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $c->leftJoin('comTopic', 'Topics');

        $c->select('comSection.id, comSection.pagetitle, comSection.uri, comSection.description');
        $c->select('COUNT(Topics.id) as topics, SUM(Topics.comments) as comments, SUM(Topics.views) as views');

        return $c;
    }
}

return 'SectionGetListProcessor';