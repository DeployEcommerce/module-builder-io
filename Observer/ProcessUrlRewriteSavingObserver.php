<?php
/*
 * @Author:    Nathan Chick (nathan.chick@deploy.co.uk)
 * @Copyright: 2024 Deploy Ecommerce (https://www.deploy.co.uk/)
 * @Package:   DeployEcommerce_BuilderIO
 */
declare(strict_types=1);

namespace DeployEcommerce\BuilderIO\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Event\ObserverInterface;
use DeployEcommerce\BuilderIO\Model\PageUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class ProcessUrlRewriteSavingObserver
 *
 * This class is an observer for the cms_page_save_after event.
 *
 */
class ProcessUrlRewriteSavingObserver implements ObserverInterface
{
    /**
     * ProcessUrlRewriteSavingObserver constructor.
     *
     * @param PageUrlRewriteGenerator $pageUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     */
    public function __construct(
        private PageUrlRewriteGenerator $pageUrlRewriteGenerator,
        private UrlPersistInterface $urlPersist
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(EventObserver $observer)
    {
        $page = $observer->getEvent()->getObject();

        if ($page->dataHasChangedFor('url')
            || $page->dataHasChangedFor('store_ids')
        ) {
            $urls = $this->pageUrlRewriteGenerator->generate($page);

            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $page->getId(),
                UrlRewrite::ENTITY_TYPE => PageUrlRewriteGenerator::ENTITY_TYPE,
            ]);

            $this->urlPersist->replace($urls);
        }
    }
}
