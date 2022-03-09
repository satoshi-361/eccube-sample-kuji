<?php

namespace Customize\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Eccube\Entity\BaseInfo;

class HelloEventSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [Events::postLoad];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof BaseInfo) {
            $shopName = 'くじEC CUBEデモ';
            $entity->setShopName($shopName);
        }
    }
}