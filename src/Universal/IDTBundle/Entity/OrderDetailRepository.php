<?php

namespace Universal\IDTBundle\Entity;

use Doctrine\ORM\EntityRepository;

class OrderDetailRepository extends EntityRepository
{
    /**
     * @param string $orderRef
     * @return OrderDetail|null
     */
    public function findOneByOrderReference($orderRef)
    {
        /**
         * $orderRef is composed by ymdHi + id
         * ymdHi (i.e. the 1st of January 2013 20h59: 1301012059)
         * id starts from the 11th character
         */
        $id = substr($orderRef, 10);

        /** @var OrderDetail $orderDetail */
        $orderDetail = $this->find($id);
        if (null === $orderDetail || strval($orderDetail->getOrderReference()) !== strval($orderDetail))
            return null;

        return $orderDetail;
    }
}