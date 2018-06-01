<?php

class CommunityVoteTopicProcessor extends modProcessor
{
    public $classKey = 'comVote';
    /** @var App */
    public $App;


    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        $this->App = $this->modx->getService('App');

        return $this->modx->user->isAuthenticated($this->modx->context->key)
            ? true
            : $this->modx->lexicon('access_denied');
    }


    /**
     * @return array|mixed|string
     */
    public function process()
    {
        $key = [
            'id' => (int)$this->getProperty('id'),
            'class' => 'comTopic',
            'createdby' => $this->modx->user->id,
        ];
        $rating = $this->getProperty('vote') == 'down'
            ? -1
            : 1;
        /** @var comTopic $object */
        $object = $this->modx->getObject('comTopic', $key['id']);
        /** @var comSection $section */
        if (!$object || !$section = $object->getOne('Section')) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $properties = $this->App->getProperties($section->get('alias'));
        if ((strtotime($object->createdon) + $properties['voting']) < time()) {
            return $this->failure($this->modx->lexicon('vote_err_ended'));
        }

        /** @var comVote $vote */
        if (!$vote = $this->modx->getObject($this->classKey, $key)) {
            $vote = $this->modx->newObject($this->classKey, $key);
            $vote->fromArray($key, '', true, true);
            $vote->set('createdon', date('Y-m-d H:i:s'));
            $vote->set('value', $rating);
            $vote->set('owner', $object->createdby);
        } else {
            $vote->set('value', $rating);
        }
        $vote->save();

        $c = $this->modx->newQuery('comVote', ['id' => $object->id, 'class' => 'comTopic']);
        $c->select('SUM(value)');
        if ($c->prepare() && $c->stmt->execute()) {
            $object->set('rating', $c->stmt->fetchColumn());
        }
        $object->set('rating_plus', $this->modx->getCount('comVote', ['id' => $object->id, 'class' => 'comTopic', 'value:>' => 0]));
        $object->set('rating_minus', $this->modx->getCount('comVote', ['id' => $object->id, 'class' => 'comTopic', 'value:<' => 0]));
        $object->save();

        return $this->success('', $object->get(['rating', 'rating_plus', 'rating_minus']));
    }

}

return 'CommunityVoteTopicProcessor';