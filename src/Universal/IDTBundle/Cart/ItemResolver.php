<?php
namespace Universal\IDTBundle\Cart;

use Sylius\Component\Cart\Model\CartItemInterface;
use Sylius\Component\Cart\Resolver\ItemResolverInterface;
use Sylius\Component\Cart\Resolver\ItemResolvingException;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Universal\IDTBundle\Entity\Product;

/**
 * Class ItemResolver
 * @package Universal\IDTBundle\Cart
 */
class ItemResolver implements ItemResolverInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param CartItemInterface $item
     * @param Request $request
     * @return CartItemInterface
     */
    public function resolve(CartItemInterface $item, $request)
    {
        $productId = $request->query->get('productId');

        /** @var Product $product */
        // If no product id given, or product not found, we throw exception with nice message.
        if (!$productId || !$product = $this->getProductRepository()->find($productId)) {
            throw new ItemResolvingException('Requested product was not found');
        }

        // Assign the product to the item and define the unit price.
        $item->setProduct($product);
        $item->setUnitPrice($product->getDenominations()[0]);

        // Everything went fine, return the item.
        return $item;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getProductRepository()
    {
        return $this->entityManager->getRepository('UniversalIDTBundle:Product');
    }
}