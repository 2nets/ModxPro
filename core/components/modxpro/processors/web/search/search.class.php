<?php

use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Helper;
use Foolz\SphinxQL\Drivers\SimpleConnection;
use Foolz\SphinxQL\Drivers\Mysqli\ResultSet;

require_once dirname(dirname(__FILE__)) . '/getlist.class.php';

class SearchGetListProcessor extends AppGetListProcessor
{
    public $objectType = 'comTopic';
    public $classKey = 'comTopic';

    public $_max_limit = 100;
    public $getPages = true;
    public $tpl = '@FILE chunks/search/results.tpl';

    protected $conn;
    protected $data = [];


    /**
     * @return bool
     */
    public function initialize()
    {
        $socket = dirname(MODX_BASE_PATH) . '/sphinx/var/run/mysql.sock';
        if (!file_exists($socket)) {
            return false;
        }
        $this->conn = new SimpleConnection();
        $this->conn->setParams(['socket' => $socket]);

        return parent::initialize();
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $ids = [];
        if ($string = $this->getProperty('query')) {
            /** @noinspection PhpParamsInspection */
            $query = (new SphinxQL($this->conn))->select('id', 'comment', 'weight() AS weight')
                ->from($this->modx->context->key == 'web' ? 'topics_ru' : 'topics_en')
                ->limit($this->getProperty('start'), $this->getProperty('limit'))
                ->option('field_weights', [
                    'pagetitle' => 100,
                    'content' => 50,
                    'comment' => 10,
                ])
                ->groupBy('id')
                ->option('max_matches', 100000)
                ->match(['pagetitle', 'content', 'comment'], $string);
            /** @var ResultSet $result */
            $result = $query->execute();
            while ($row = $result->fetchAssoc()) {
                $ids[] = $row['id'];
                $this->data[$row['id']] = $row;
            }
        }
        if (!$ids) {
            $ids[] = -1;
        }

        $c->where([$this->classKey . '.id:IN' => $ids]);
        $c->sortby('FIELD(' . $this->classKey . '.id, ' . implode(',', $ids) . ')');

        return $c;
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey, '', ['content'], true));
        $c->leftJoin('comSection', 'Section');
        $c->leftJoin('modUser', 'User');
        $c->leftJoin('modUserProfile', 'UserProfile');
        $c->select('Section.pagetitle as section_title, Section.uri as section_uri');
        $c->select('User.username');
        $c->select('UserProfile.photo, UserProfile.email, UserProfile.fullname, UserProfile.usename');

        return $c;
    }


    /**
     * Get the data of the query
     *
     * @return array
     */
    public function getData()
    {
        $data = [];
        $c = $this->modx->newQuery($this->classKey);
        $c = $this->prepareQueryBeforeCount($c);

        $meta = (new SphinxQL($this->conn))->enqueue(Helper::create($this->conn)->showMeta())->execute();
        foreach ($meta->fetchAllAssoc() as $item) {
            if ($item['Variable_name'] == 'total_found') {
                $data['total'] = $item['Value'];
                break;
            }
        }

        $c = $this->prepareQueryAfterCount($c);
        $data['results'] = [];
        $tstart = microtime(true);
        if ($c->prepare() && $c->stmt->execute()) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;
            $data['results'] = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR,
                "[App] GetList error: " . print_r($c->stmt->errorInfo(), true) . $c->toSQL());
        }

        return $data;
    }


    /**
     * @param array $array
     *
     * @return array
     */
    public function prepareArray(array $array)
    {
        $array['content'] = strip_tags($array['content']);
        $array['weight'] = $this->data[$array['id']]['weight'];
        /** @noinspection PhpParamsInspection */
        $snippet = (new SphinxQL($this->conn))
            ->enqueue(
                Helper::create($this->conn)->callSnippets([
                    $this->modx->stripTags(html_entity_decode($array['pagetitle'])),
                    $this->modx->stripTags(html_entity_decode($array['content'] . $this->data[$array['id']]['comment'])),
                ],
                    $this->modx->context->key == 'web'
                        ? 'topics_ru'
                        : 'topics_en',
                    $this->getProperty('query'), ['use_boundaries' => true]
                )
            )
            ->execute();
        if ($data = $snippet->fetchAllNum()) {
            $array['pagetitle'] = $this->modx->stripTags($data[0][0], '<b>');
            $array['introtext'] = $this->modx->stripTags($data[1][0], '<b>');
        }

        return parent::prepareArray($array);
    }

}

return 'SearchGetListProcessor';