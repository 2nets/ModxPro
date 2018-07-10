<?php

/**
 * Trait CommentProcessor
 *
 * @property modX $modx
 * @property comComment $object
 * @property App $App
 * @property string $tpl
 * @property string $classKey
 */
trait CommentProcessor
{
    /**
     * @return array|string
     */
    public function cleanup()
    {
        $c = $this->modx->newQuery($this->classKey, $this->object->get('id'));
        $c->leftJoin('modUser', 'User');
        $c->leftJoin('modUserProfile', 'UserProfile');
        $c->leftJoin('comStar', 'Star', 'Star.id = comComment.id AND Star.class = "comComment" AND Star.createdby = ' . $this->modx->user->id);
        $c->select('Star.id as star');
        $c->leftJoin('comVote', 'Vote', 'Vote.id = comComment.id AND Vote.class = "comComment" AND Vote.createdby = ' . $this->modx->user->id);
        $c->select('Vote.value as vote');
        $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey));
        $c->select('User.username');
        $c->select('UserProfile.fullname, UserProfile.photo, UserProfile.email, UserProfile.usename');
        $data = $c->prepare() && $c->stmt->execute()
            ? $c->stmt->fetch(PDO::FETCH_ASSOC)
            : [];
        $data['can_edit'] = $this->object->canEdit();
        $data['can_vote'] = $this->object->canVote();

        /** @noinspection PhpUndefinedMethodInspection */
        return $this->success('', [
            'id' => $data['id'],
            'parent' => $data['parent'],
            'count' => $this->modx->getCount('comComment', ['topic' => $this->object->topic]),
            'html' => trim($this->App->pdoTools->getChunk($this->tpl, ['item' => $data])),
        ]);
    }

}