<?php

/**
 * @property int createdby
 * @property int topic
 * @property string createdon
 */
class comView extends xPDOObject
{

    /**
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null)
    {
        $new = $this->isNew();
        $save = parent::save($cacheFlag);

        /** @var comTopic $topic */
        if ($new && $save) {
            /** @var comTopic $topic */
            if ($topic = $this->getOne('Topic')) {
                $topic->views(true);
            }
        }

        return $save;
    }


    /**
     * @param array $ancestors
     *
     * @return bool
     */
    public function remove(array $ancestors = [])
    {
        if ($remove = parent::remove($ancestors)) {
            /** @var comTopic $topic */
            if ($topic = $this->getOne('Topic')) {
                $topic->views(true);
            }
        }

        return $remove;
    }

}