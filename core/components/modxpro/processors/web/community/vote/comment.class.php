<?php

class CommunityVoteCommentProcessor extends modObjectProcessor
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
            'class' => 'comComment',
            'createdby' => $this->modx->user->id,
        ];
        $rating = $this->getProperty('vote') == 'down'
            ? -1
            : 1;
        /** @var comComment $object */
        $object = $this->modx->getObject('comComment', ['id' => $key['id'], 'deleted' => false]);
        if (!$object || $object->createdby == $this->modx->user->id) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }
        $c = $this->modx->newQuery('comTopic', ['id' => $object->topic]);
        $c->innerJoin('comSection', 'Section');
        $c->select('Section.alias');
        if ($c->prepare() && $c->stmt->execute()) {
            $properties = $this->App->getProperties($c->stmt->fetchColumn(), 'comment');
            if ((strtotime($object->createdon) + $properties['voting']) < time()) {
                return $this->failure($this->modx->lexicon('vote_err_ended'));
            }
        } else {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        /** @var comStar $vote */
        if (!$vote = $this->modx->getObject($this->classKey, $key)) {
            $vote = $this->modx->newObject($this->classKey, $key);
            $vote->fromArray($key, '', true, true);
            $vote->set('createdon', date('Y-m-d H:i:s'));
            $vote->set('value', $rating);
            $vote->set('owner', $object->createdby);
        } else {
            $vote->set('value', $rating);
        }
        $this->modx->getRequest();
        /** @var modRequest $request */
        $request = $this->modx->request;
        $vote->set('ip', $request->getClientIp()['ip']);
        $vote->save();
        $object->rating(true);

        return $this->success('', $object->get(['rating']));
    }

}

return 'CommunityVoteCommentProcessor';