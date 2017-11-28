<?php

namespace Peters\DeliveryDate\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Data\Form\FormKey;
use Magento\Catalog\Model\ProductRepository;

use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\Message\ManagerInterface ;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ProductAlert\Model\Stock;

class BeforeAddToCart implements ObserverInterface {

    protected $_productRepository;
    protected $_cart;
    protected $formKey;
    protected $_messageManager;

    /**
     */
    public function __construct(ProductRepository $productRepository, Cart $cart, FormKey $formKey, ManagerInterface $messageManager)
    {
        $this->_productRepository = $productRepository;
        $this->_cart = $cart;
        $this->formKey = $formKey;
        $this->_messageManager = $messageManager;
    }

    /**
     */
    public function execute(Observer $observer) {
        $items = $this->_cart->getQuote()->getAllItems();
        $product = $observer->getEvent()->getData('product');
        foreach($items as $item){
            if($item->getProductId() == $product->getProductId()){
                if ($product->getQty() < $item->getQty()){
                    $this->_messageManager->addNoticeMessage('Only one item can be bought at a time');
                    return false;
                }
                else{
                    $this->_cart->addProduct($product);
                    $this->_cart->save();
                }
            }
        }
    }
}