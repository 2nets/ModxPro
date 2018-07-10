<?php

/**
 * @property int id
 * @property float rating
 */
class comAuthor extends xPDOObject
{

    /**
     * @param $section
     * @param string $type
     *
     * @return mixed
     */
    public function getProperties($section, $type = 'topic')
    {
        /** @var App $App */
        $App = $this->xpdo->getService('App');

        return $App->getProperties($section, $type);
    }


    /**
     * @param bool $save
     *
     * @return array
     */
    public function topics($save = false)
    {
        $res = [
            'topics' => 0,
            'rating_topics_create' => 0,
        ];

        $c = $this->xpdo->newQuery('comTopic', ['createdby' => $this->id, 'published' => true]);
        $c->innerJoin('comSection', 'Section');
        $c->groupby('Section.alias');
        $c->select('COUNT(comTopic.id) as topics, Section.alias as section');
        if ($c->prepare() && $c->stmt->execute()) {
            while ($item = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                $res['topics'] += $item['topics'];
                $res['rating_topics_create'] += $this->getProperties($item['section'], 'topic')['create'] * $item['topics'];
            }
        }
        if ($save) {
            $this->fromArray($res);
            $this->save();
        }

        return $res;
    }


    /**
     * @param bool $save
     *
     * @return array
     */
    public function comments($save = false)
    {
        $res = [
            'comments' => 0,
            'rating_comments_create' => 0,
        ];

        $c = $this->xpdo->newQuery('comComment', ['createdby' => $this->id, 'deleted' => false]);
        $c->innerJoin('comTopic', 'Topic');
        $c->innerJoin('comSection', 'Section', 'Topic.parent = Section.id');
        $c->groupby('Section.alias');
        $c->select('COUNT(comComment.id) as comments, Section.alias as section');
        if ($c->prepare() && $c->stmt->execute()) {
            while ($item = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                $res['comments'] += $item['comments'];
                $res['rating_comments_create'] += $this->getProperties($item['section'], 'comment')['create'] * $item['comments'];
            }
        }
        if ($save) {
            $this->fromArray($res);
            $this->save();
        }

        return $res;
    }


    /**
     * @param bool $save
     *
     * @return array
     */
    public function stars($save = false)
    {
        $res = [
            'rating_topics_stars' => 0,
            'rating_comments_stars' => 0,
            'topics_stars' => 0,
            'comments_stars' => 0,
        ];

        $c = $this->xpdo->newQuery('comStar', [
            'class' => 'comTopic',
            'owner' => $this->id,
            'Topic.published' => true,
        ]);
        $c->innerJoin('comTopic', 'Topic');
        $c->innerJoin('comSection', 'Section', 'Section.id = Topic.parent');
        $c->select('Section.alias as section');
        if ($c->prepare() && $c->stmt->execute()) {
            while ($section = $c->stmt->fetchColumn()) {
                $res['topics_stars'] += 1;
                $res['rating_topics_stars'] += $this->getProperties($section, 'topic')['star'];
            }
        }

        $c = $this->xpdo->newQuery('comStar', [
            'class' => 'comComment',
            'owner' => $this->id,
            'Topic.published' => true,
            'Comment.deleted' => false,
        ]);
        $c->innerJoin('comComment', 'Comment');
        $c->innerJoin('comTopic', 'Topic', 'Topic.id = Comment.topic');
        $c->innerJoin('comSection', 'Section', 'Section.id = Topic.parent');
        $c->select('Section.alias');
        if ($c->prepare() && $c->stmt->execute()) {
            while ($section = $c->stmt->fetchColumn()) {
                $res['comments_stars'] += 1;
                $res['rating_comments_stars'] += $this->getProperties($section, 'comment')['star'];
            }
        }

        if ($save) {
            $this->fromArray($res);
            $this->save();
        }

        return $res;
    }


    /**
     * @param bool $save
     *
     * @return array
     */
    public function votes($save = false)
    {
        $res = [
            'rating_topics_votes' => 0,
            'topics_votes_up' => 0,
            'topics_votes_down' => 0,
            'rating_comments_votes' => 0,
            'comments_votes_up' => 0,
            'comments_votes_down' => 0,
        ];

        $c = $this->xpdo->newQuery('comVote', [
            'class' => 'comTopic',
            'owner' => $this->id,
            'Topic.published' => true,
        ]);
        $c->innerJoin('comTopic', 'Topic');
        $c->innerJoin('comSection', 'Section', 'Section.id = Topic.parent');
        $c->select('comVote.value, Section.alias as section');
        if ($c->prepare() && $c->stmt->execute()) {
            while ($item = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($item['value'] > 0) {
                    $res['topics_votes_up'] += $item['value'];
                } else {
                    $res['topics_votes_down'] += abs($item['value']);
                }
                $res['rating_topics_votes'] += $item['value'] * $this->getProperties($item['section'], 'topic')['vote'];
            }
        }

        $c = $this->xpdo->newQuery('comVote', [
            'class' => 'comComment',
            'owner' => $this->id,
            'Topic.published' => true,
            'Comment.deleted' => false,
        ]);
        $c->innerJoin('comComment', 'Comment');
        $c->innerJoin('comTopic', 'Topic', 'Topic.id = Comment.topic');
        $c->innerJoin('comSection', 'Section', 'Section.id = Topic.parent');
        $c->select('comVote.value, Section.alias as section');
        if ($c->prepare() && $c->stmt->execute()) {
            while ($item = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($item['value'] > 0) {
                    $res['comments_votes_up'] += $item['value'];
                } else {
                    $res['comments_votes_down'] += abs($item['value']);
                }
                $res['rating_comments_votes'] += $item['value'] * $this->getProperties($item['section'], 'comment')['vote'];
            }
        }

        if ($save) {
            $this->fromArray($res);
            $this->save();
        }

        return $res;
    }


    /**
     * @param bool $save
     *
     * @return array
     */
    public function rating($save = true)
    {
        $res = $this->topics();
        $res = array_merge($res, $this->comments());
        $res = array_merge($res, $this->stars());
        $res = array_merge($res, $this->votes());

        $rating = 0;
        foreach ($res as $key => $value) {
            if (strpos($key, 'rating_') === 0) {
                $rating += $value;
            }
        }
        $res['rating'] = $rating;

        if ($save) {
            $this->fromArray($res);
            $this->save();
        }

        return $res;
    }

}