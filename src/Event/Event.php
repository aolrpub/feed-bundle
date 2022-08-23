<?php

namespace Aolr\FeedBundle\Event;

use Doctrine\ORM\EntityManagerInterface;
use MongoDB\Model\BSONDocument;

class Event
{
    /**
     * @var BSONDocument
     */
    protected $document;

    /**
     * @var array
     */
    private static $constants;

    public function __construct(BSONDocument $document)
    {
        $this->document = $document;
    }

    /**
     * @return array
     */
    public static function getReflectConstants(): array
    {
        if (static::$constants) {
            return static::$constants;
        }

        $reflect = new \ReflectionClass(static::class);
        $constants = $reflect->getConstants();
        foreach ($constants as $key => $value) {
            if (strpos($key, 'EVENT_') === false) {
                unset($constants[$key]);
                continue;
            }
            $constants[$value] = str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', str_replace('EVENT_', '', $key)))));
            unset($constants[$key]);
        }
        static::$constants = $constants;
        return $constants;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getTime(): string
    {
        $today = new \DateTime();

        $dt = (new \DateTime())->setTimestamp($this->document['time']);

        $interval = $today->diff($dt);
        $times = explode(' ', $interval->format('%Y %m %d %h %i %s'));
        $times = array_map(function ($time) {
            return intval($time);
        }, $times);

        if ($times[0]) {
//            $result = $times[0].' year'.(1 == $times[0] ? '' : 's').' ago';
//            if ($times[1]) {
//                $result = $times[1].' month'.(1 == $times[1] ? '' : 's').' ago';
//            }
            $result = $dt->format('Y-m-d H:i:s');
        } elseif ($times[1] > 1) {
            $result = $times[1].' months ago';
        } elseif (1 == $times[1]) {
            $result = '1 month ago';
        } elseif ($times[2] > 14) {
            $result = ceil($times[2] / 7).' weeks ago';
        } elseif ($times[2] > 1) {
            $result = $times[2].' days ago';
        } elseif (1 == $times[2]) {
            $result = 'yesterday';
        } elseif ($times[3] > 1) {
            $result = $times[3].' hours ago';
        } elseif (1 == $times[3]) {
            $result = $times[3].' hour ago';
        } elseif ($times[4] > 1) {
            $result = $times[4].' minutes ago';
        } elseif (1 == $times[4]) {
            $result = '1 minute ago';
        } elseif ($times[5] > 10) {
            $result = $times[5].' seconds ago';
        } else {
            $result = 'just now';
        }

        return $result;
    }

    public function __call($name, $args)
    {
        return $this->document[$name] ?? '';
    }
}
