<?php

class CommentPreviewProcessor extends modObjectGetProcessor
{

    public $classKey = 'comComment';
    /** @var comComment $object */
    public $object;
    public $tpl = '@FILE chunks/comments/_preview.tpl';


    public function initialize()
    {
        return $this->modx->user->isAuthenticated($this->modx->context->key);
    }


    public function process()
    {
        /** @var App $App */
        $App = $this->modx->getService('App');

        $content = trim($this->getProperty('content'));
        if (empty($content)) {
            return $this->failure($this->modx->lexicon('comment_err_no_content'));
        }

        if ($id = (int)$this->getProperty('id')) {
            $c = $this->modx->newQuery($this->classKey, $id);
            $c->leftJoin('modUser', 'User');
            $c->leftJoin('modUserProfile', 'UserProfile');
            $c->leftJoin('comStar', 'Star', 'Star.id = comComment.id AND Star.class = "comComment" AND Star.createdby = ' . $this->modx->user->id);
            $c->select('Star.id as star');
            $c->leftJoin('comVote', 'Vote', 'Vote.id = comComment.id AND Vote.class = "comComment" AND Vote.createdby = ' . $this->modx->user->id);
            $c->select('Vote.value as vote');
            $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey));
            $c->select('User.username');
            $c->select('UserProfile.fullname, UserProfile.photo, UserProfile.email, UserProfile.usename');
            if ($c->prepare() && $c->stmt->execute()) {
                $data = $c->stmt->fetch(PDO::FETCH_ASSOC);
            }
        } else {
            $data = [
                'id' => 0,
                'createdon' => time(),
                'fullname' => $this->modx->user->Profile->fullname,
                'photo' => $this->modx->user->Profile->photo,
                'email' => $this->modx->user->Profile->email,
                'usename' => $this->modx->user->Profile->usename,
                'rating' => 0,
                'stars' => 0,
                'vote' => null,
                'star' => null,
            ];
        }
        $data['content'] = $content;

        return $this->success('', [
            'html' => $App->pdoTools->getChunk($this->tpl, [
                'item' => $data,
            ]),
        ]);
    }

}

return 'CommentPreviewProcessor';