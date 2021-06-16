<?php declare(strict_types=1);

namespace Memo\Vatlayer\Controllers;

use Memo\Vatlayer\Services\VatlayerService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Storefront\Controller\StorefrontController as SwStorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class StorefrontController extends SwStorefrontController
{
    /** @var VatlayerService */
    private $vatlayerService;

    public function __construct(VatlayerService $vatlayerService)
    {
        $this->vatlayerService = $vatlayerService;
    }

    /**
     * @Route("widget/memo/vatlayer/check-id",
     *         name="frontend.memo.vatlayer.validate", methods={"POST"},
     *         defaults={"XmlHttpRequest"=true})
     */
    public function validate(RequestDataBag $request, Context $context): JsonResponse
    {
        $vatId = $request->get("vatId");

        $response = [];

        try {
            $response = $this->vatlayerService->validate($vatId);
            $response['isValid'] = false;

            if ($response['database'] !== "ok") {
                $message = [
                    "type" => "warning",
                    "message" => $this->trans('memo-vatlayer.error.database')
                ];
            } elseif ($response['format_valid'] == false) {
                $message = [
                    "type" => "danger",
                    "message" => $this->trans('memo-vatlayer.error.format')
                ];
            } elseif ($response['valid'] == false) {
                $message = [
                    "type" => "danger",
                    "message" => $this->trans('memo-vatlayer.error.invalid')
                ];
            } else {
                $response['isValid'] = true;
                $message = [
                    "type" => "success",
                    "message" => $this->trans('memo-vatlayer.valid', [
                        '%company%' => $response['company_name']
                    ])
                ];
            }
        } catch (\Exception $exception) {
            switch($exception->getCode()) {
                default:
                    $message = ["type" => "warning", "message" => $exception->getMessage()];
                    break;
                case 106:
                    $message = ['type' => "warning", "message" => $this->trans('memo-vatlayer.api-error.106')];
                    break;
            }
        }

        $response['message'] = $message;

        return new JsonResponse($response);
    }
}
