<?php


namespace AG\LasVegasBundle\DoctrineListener;


use AG\LasVegasBundle\Entity\Photo;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class ThumbnailHandler
{
    private $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Photo)
            return;

        if ($file = $entity->getAbsolutePath()) {
            unlink($file);

            $this->cacheManager->resolve($entity->getWebPath(), 'my_thumbnail');
            $this->cacheManager->remove($entity->getWebPath());
        }
    }
} 