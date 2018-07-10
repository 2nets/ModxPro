<?php

/**
 * @property int id
 * @property string class
 * @property int owner
 */
class comStar extends xPDOObject
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

        if ($new && $save) {
            /** @var comTopic|comComment $obj */
            if ($this->class == 'comComment') {
                $obj = $this->getOne('Comment');
            } else {
                $obj = $this->getOne('Topic');
            }
            if ($obj) {
                $obj->stars(true);
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
            /** @var comTopic|comComment $obj */
            if ($this->class == 'comComment') {
                $obj = $this->getOne('Comment');
            } else {
                $obj = $this->getOne('Topic');
            }
            if ($obj) {
                $obj->stars(true);
            }
        }

        return $remove;
    }
}