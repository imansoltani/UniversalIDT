<?php
namespace Universal\IDTBundle\Services;

use Doctrine\ORM\EntityManager;
use Universal\IDTBundle\Entity\Product;

/**
 * Class OrderServices
 * @package Universal\IDTBundle\Service
 */
class OrderServices
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    private function getVat($countryISO)
    {
        $vatEntity = $this->em->getRepository('UniversalIDTBundle:Vat')->findOneBy(array('countryISO'=>$countryISO));
        return $vatEntity ? $vatEntity->getValue() : null;
    }

    public function calcVat(Product $product, $denomination)
    {
        $price_with_vat = $denomination * (($product->getDenominations()[0] - $product->getFreeAmountDenomination1()) / $product->getDenominations()[0]);
        $price_without_vat = $price_with_vat / (1+ $this->getVat($product->getCountryISO()) /100);
        return $price_with_vat - $price_without_vat;
    }

    public function calcDiscount(Product $product, $denomination)
    {
        return ($product->getFreeAmountDenomination1() / $product->getDenominations()[0]) * $denomination;
    }
}