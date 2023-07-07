<?php

namespace Koality\ShopwarePlugin\Controller;

use Koality\ShopwarePlugin\Collector\CollectorContainer;
use Koality\ShopwarePlugin\Exception\ForbiddenException;
use Koality\ShopwarePlugin\Formatter\KoalityFormatter;
use Koality\ShopwarePlugin\KoalityShopwarePlugin;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HealthApiController
 *
 * @package Koality\ShopwarePlugin\Controller
 *
 * @author Nils Langner <nils.langner@leankoala.com>
 * created 2020-12-28
 *
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class HealthApiController extends StorefrontController
{
    /**
     * Get the health status of the online shop.
     *
     * @param Request $request
     * @param Context $context
     *
     * @return JsonResponse
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @Route("_koality/sales/metrics/{apiKey}", name="koality.sales.metrics", methods={"GET"}, defaults={"csrf_protected"=false, "XmlHttpRequest"=true, "_routeScope"={"storefront"}})
     */
    public function healthSalesApi(Request $request, Context $context): JsonResponse
    {
        $currentApiKey = $request->get('apiKey');

        if (is_null($currentApiKey)) {
            return new JsonResponse(['status' => 'failure', 'message' => 'API key is missing. Please run the install routine again.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $pluginConfig = $this->getPluginConfig($currentApiKey);
        } catch (ForbiddenException $e) {
            return new JsonResponse(['status' => 'failure', 'message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'failure', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $formatter = $this->collectResults($pluginConfig, $context);

        $response = new JsonResponse($formatter->getFormattedResults());

        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }

    /**
     * Get the plugin version.
     *
     * @return JsonResponse
     *
     * @Route("_koality/version", name="koality.version", methods={"GET"}, defaults={"csrf_protected"=false, "XmlHttpRequest"=true, "_routeScope"={"storefront"}})
     */
    public function healthSalesApiVersion(): JsonResponse
    {
        $response = new JsonResponse(['version' => KoalityShopwarePlugin::VERSION]);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }

    /**
     * Collect all health results.
     *
     * @param array $pluginConfig
     * @param Context $context
     *
     * @return KoalityFormatter
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function collectResults(array $pluginConfig, Context $context): KoalityFormatter
    {
        $collectorContainer = $this->container->get(CollectorContainer::class);
        $collectorContainer->init($pluginConfig, $context);
        return $collectorContainer->run();
    }

    /**
     * Get the plugin configuration.
     *
     * @param string $currentApiKey
     *
     * @return string[]
     *
     * @throws ForbiddenException
     * @throws RuntimeException
     */
    private function getPluginConfig(string $currentApiKey): array
    {
        $configService = $this->getSystemConfigService();

        $pluginConfigArray = $configService->get(KoalityShopwarePlugin::PLUGIN_NAME);

        if (!is_array($pluginConfigArray) || !array_key_exists('config', $pluginConfigArray)) {
            throw new RuntimeException('The plugin is not configured yet. Please run the configuration first.');
        }

        $pluginConfig = $pluginConfigArray['config'];

        if ($currentApiKey !== $pluginConfig[KoalityShopwarePlugin::CONFIG_KEY_API_KEY]) {
            throw new ForbiddenException('The API key is not valid.');
        }

        return $pluginConfig;
    }
}
