<?php

namespace FunkySquid\NotifyMe\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;

class Post extends Action
{
    protected $transportBuilder;
    protected $storeManager;

    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();

        if (!$post || !isset($post['email']) || !isset($post['product_id'])) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('/');
        }

        try {
            $email = $post['email'];
            $productId = $post['product_id'];

            $store = $this->storeManager->getStore();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('notify_me_email_template') // The identifier of the email template
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()])
                ->setTemplateVars(['product_id' => $productId, 'store' => $store])
                ->setFrom('general')
                ->addTo($email)
                ->getTransport();

            $transport->sendMessage();

            $this->messageManager->addSuccessMessage(__('We will notify you when the product is back in stock.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while processing your request.'));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('/');
    }
}
