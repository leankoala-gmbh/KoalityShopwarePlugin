<?php

namespace Koality\ShopwarePlugin\Controller;

use Doctrine\DBAL\Connection;
use Koality\ShopwarePlugin\Collector\ActiveProductsCollector;
use Koality\ShopwarePlugin\Collector\MinOrdersCollector;
use Koality\ShopwarePlugin\Collector\OpenCartsCollector;
use Koality\ShopwarePlugin\Exception\ForbiddenException;
use Koality\ShopwarePlugin\Formatter\KoalityFormatter;
use Koality\ShopwarePlugin\KoalityShopwarePlugin;
use RuntimeException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class HealthApiController
 *
 * @package Koality\ShopwarePlugin\Controller
 *
 * @author Nils Langner <nils.langner@leankoala.com>
 * created 2020-12-28
 *
 * @RouteScope(scopes={"storefront"})
 */
class HealthApiController extends AbstractController
{
    /**
     * Get the health status of the online shop.
     *
     * @param Request $request
     * @param Context $context
     *
     * @return JsonResponse
     *
     * @RouteScope(scopes={"storefront"})
     * @Route("_koality/sales/metrics/{apiKey}", name="koality.sales.metrics", methods={"GET"}, defaults={"csrf_protected"=false, "XmlHttpRequest"=true})
     */
    public function healthSalesApi(Request $request, Context $context): JsonResponse
    {
        $currentApiKey = $request->get('apiKey');

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
     * @RouteScope(scopes={"storefront"})
     * @Route("_koality/version", name="koality.version", methods={"GET"}, defaults={"csrf_protected"=false, "XmlHttpRequest"=true})
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
     */
    private function collectResults(array $pluginConfig, Context $context): KoalityFormatter
    {
        $formatter = new KoalityFormatter();

        $collectors = [
            new OpenCartsCollector($pluginConfig, $this->get(Connection::class)),
            new ActiveProductsCollector($pluginConfig, $this->get(Connection::class)),
            new MinOrdersCollector($pluginConfig, $context, $this->get('order.repository'))
        ];

        foreach ($collectors as $collector) {
            $formatter->addResult($collector->getResult());
        }

        return $formatter;
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
        /** @var SystemConfigService $configService */
        $configService = $this->get(SystemConfigService::class);

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
