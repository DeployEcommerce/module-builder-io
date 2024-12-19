<?php
/*
 * @Author:    Nathan Chick (nathan.chick@deploy.co.uk)
 * @Copyright: 2024 Deploy Ecommerce (https://www.deploy.co.uk/)
 * @Package:   DeployEcommerce_BuilderIO
 */
declare(strict_types=1);

namespace DeployEcommerce\BuilderIO\Model;

use DeployEcommerce\BuilderIO\Api\Data\ContentPageInterface;
use DeployEcommerce\BuilderIO\Api\Data\ContentPageInterfaceFactory;
use DeployEcommerce\BuilderIO\Api\ContentPageRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class ContentPageRepository implements ContentPageRepositoryInterface
{

    /**
     * ContentPageRepository constructor.
     *
     * @param ResourceModel\ContentPageResource $resource
     * @param ContentPageInterfaceFactory $contentPageFactory
     */
    public function __construct(
        private ResourceModel\ContentPageResource $resource,
        private ContentPageInterfaceFactory $contentPageFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function save(ContentPageInterface $contentPage): ContentPageInterface
    {
        try {
            $this->resource->save($contentPage);
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(
                __('Could not save the webhook: %1', $exception->getMessage()),
                $exception
            );
        } catch (\Throwable $exception) {
            // todo fix the exception handling
//            throw new CouldNotSaveException(
//                __('Could not save the webhook: %1', __('Something went wrong while saving the ContentPage.')),
//                $exception
//            );
        }

        return $contentPage;
    }

    /**
     * @inheritDoc
     */
    public function getById($contentPageId, $field = ContentPageInterface::ID): ContentPageInterface
    {
        $contentPage = $this->contentPageFactory->create();
        $contentPage->load($contentPageId, $field);
        if (!$contentPage->getId()) {
            throw new NoSuchEntityException(__('The ContentPage with the "%1" ID doesn\'t exist.', $contentPageId));
        }

        return $contentPage;
    }

    /**
     * @inheritDoc
     */
    public function findByBuilderioPageId($builderioPageId): ContentPageInterface
    {
        return $this->getById($builderioPageId, ContentPageInterface::BUILDERIO_PAGE_ID);
    }

    /**
     * @inheritDoc
     */
    public function delete(ContentPageInterface $contentPage): void
    {
        try {
            $this->resource->delete($contentPage);
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(
                __('Could not delete the webhook: %1', $exception->getMessage()),
                $exception
            );
        }
    }
}
