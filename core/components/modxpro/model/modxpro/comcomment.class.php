<?php

/**
 * @property int id
 * @property int createdby
 * @property int topic
 * @property int parent
 * @property string createdon
 * @property string raw
 * @property string content
 * @property bool deleted
 */
class comComment extends xPDOSimpleObject
{
    /** @var array $section */
    protected $properties = [];


    /**
     * @return array
     */
    public function getProperties()
    {
        if (empty($this->properties)) {
            /** @var comTopic $topic */
            if ($topic = $this->getOne('Topic')) {
                /** @var comSection $section */
                if ($section = $topic->getOne('Section')) {
                    /** @var App $App */
                    $App = $this->xpdo->getService('App');
                    $this->properties = $App->getProperties($section->alias, 'comment');
                }
            }
        }

        return $this->properties;
    }


    /**
     * @return bool
     */
    public function canEdit()
    {
        if (($this->xpdo instanceof modX) && $this->xpdo->user->isMember('Administrator')) {
            return true;
        }

        return strtotime($this->createdon) + $this->getProperties()['edit'] > time();
    }


    /**
     * @return bool
     */
    public function canVote()
    {
        if (!($this->xpdo instanceof modX) || $this->createdby == $this->xpdo->user->id || $this->deleted) {
            return false;
        }

        return $this->xpdo->user->isAuthenticated() &&
            (strtotime($this->createdon) + $this->getProperties()['voting']) > time();
    }


    public function stars($save = false)
    {
        $stars = $this->xpdo->getCount('comStar', ['id' => $this->id, 'class' => self::class]);
        if ($save) {
            parent::set('stars', $stars);
            parent::save();
        }

        return $stars;
    }


    /**
     * @param bool $save
     *
     * @return int
     */
    public function rating($save = false)
    {
        $rating = 0;

        $c = $this->xpdo->newQuery('comVote', ['id' => $this->id, 'class' => self::class]);
        //$c->groupby('topic');
        $c->select('SUM(value)');
        if ($c->prepare() && $c->stmt->execute()) {
            $rating = $c->stmt->fetchColumn();
            if ($rating !== false && $save) {
                parent::set('rating', $rating);
                parent::save();
            }
        }

        return $rating;
    }

}