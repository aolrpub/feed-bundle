<?php

namespace Aolr\FeedBundle\Service;

use Aolr\ArticleBundle\Service\CacheManager;
use Aolr\JournalBundle\Entity\Journal;
use Aolr\UserBundle\Entity\User;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Cursor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class FeedManager
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $eventPath;

    public function __construct(Client $client, Security $security, RequestStack $requestStack)
    {
        $this->client = $client;
        $this->user = $security->getUser();
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param string $database
     * @param string $table
     *
     * @return void
     */
    public function setCollection(string $database, string $table)
    {
        $this->collection = $this->client->selectCollection($database, $table);
    }

    public function setEventPath(string $eventPath)
    {
        $this->eventPath = $eventPath;
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    /**
     * m : module, j => journal, m => manuscript, i => invoice
     * 'dt' => ['m' => 'j|m|i', 'id' => 1, 'hk' => 'xx'] etc..
     * @param $et
     * @param array $data
     *
     * @return bool
     */
    public function save($et, array $data =[]): bool
    {
        try {
            $res = $this->collection->insertOne([
                'un' => $this->user ? $this->user->getName() : '',
                'em' => $this->user ? $this->user->getEmail() : '',
                'et' => $et,
                'host' => $this->request->getHost(),
                'ip' => $this->request->getClientIp(),
                'time' => time(),
                'dt' => $data
            ]);

            return $res->getInsertedCount() == 1;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function query($where=[], $page=1, $limit=10, $sort=['time' => -1]): ?Cursor
    {
        if (!isset($where['host'])) {
            $where['host'] = $this->request->getHost();
        }
        return $this->collection->find($where, [
            'skip' => ($page - 1) * $limit,
            'limit' => $limit,
            'sort' => $sort
        ]);
    }

    public function getObjects($where=[], $page=1, $limit=10, $sort=['time' => -1]): array
    {
        try {
            $events = [];
            $cursor = $this->query($where, $page, $limit, $sort);
            $constants = call_user_func($this->eventPath . 'Event::getReflectConstants');
            foreach ($cursor as $document) {
                if (!empty($constants[$document['et']])) {
                    $className = $this->eventPath . $constants[$document['et']];
                    if (!class_exists($className)) {
                        continue;
                    }

                    $events[] = new $className($document);
                }
            }
            return $events;
        } catch (\Exception $e) {
            @error_log($e->getMessage());
        }

        return [];
    }
}
