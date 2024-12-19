<?php
/*
 * @Author:    Nathan Chick (nathan.chick@deploy.co.uk)
 * @Copyright: 2024 Deploy Ecommerce (https://www.deploy.co.uk/)
 * @Package:   DeployEcommerce_BuilderIO
 */

declare(strict_types=1);

namespace DeployEcommerce\BuilderIO\Controller\Adminhtml\Config;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use DeployEcommerce\BuilderIO\Service\BuilderIO\AdminApi;
use DeployEcommerce\BuilderIO\System\Config;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Ajax
 *
 * This class is a controller for the Builder.io configuration AJAX requests.
 *
 */
class WebhooksAjax extends Action
{

    /**
     * WebhooksAjax constructor.
     *
     * @param Context $context
     * @param AdminApi $adminApi
     * @param Json $json
     * @param Config $config
     */
    public function __construct(
        Context $context,
        private AdminApi $adminApi,
        private Json $json,
        private Config $config
    ) {
        parent::__construct($context);
    }

    public const ADMIN_RESOURCE = 'DeployEcommerce_BuilderIO::listing';

    /**
     * Execute the AJAX request.
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws GuzzleException
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $store = (int) $this->getRequest()->getParam('store');

        if (!$store) {
            $resultPage->setStatusHeader(400);
            return $resultPage;
        }

        $response = $this->adminApi->postContent(
            "{\n\t\"query\": \"query{settings id models{id kind}}\",\n\t\"variables\": {}\n}",
            $store
        );

        if ($response === null) {
            $resultPage->setStatusHeader(500);
            return $resultPage;
        }

        if ($response->getStatusCode() !== 200) {
            $resultPage->setStatusHeader(500);
            return $resultPage;
        }

        $data = $this->json->unserialize($response->getBody()->getContents());

        if (!isset($data['data']['models'])) {
            $resultPage->setStatusHeader(500);
            return $resultPage;
        }

        $counter = 0;
        foreach ($data['data']['models'] as $model) {
            $updateResponse = $this->adminApi->postContent(
                sprintf(
                    "{\n\t\"query\": \"mutation{updateModel(body:{id:\\\"%s\\\" data:{editingUrlLogic:\\\"%s\\\", webhooks:[{disablePayload:false url:\\\"%s\\\" customHeaders:[{name:\\\"x-builderio-secret-key\\\" value:\\\"%s\\\"}]}]}}){id}}\",\n\t\"variables\": {}\n}",
                    $model['id'],
                    "return '" . $this->config->getStoreUrl($store) . 'builderio/preview/page' . "';",
                    $this->config->getStoreUrl($store) . 'builderio/webhooks/'.$model['kind'],
                    $this->config->getWebhookSecretKey($store)
                ),
                $store
            );


            if ($updateResponse->getStatusCode() === 200) {
                $counter++;
            }
        }

        $resultPage->setJsonData("{\"status\": \"success\", \"count\": $counter}");

        return $resultPage;
    }
}
