<?php declare(strict_types=1);

namespace Memo\VatlayerPlugin\Controllers;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

use Shopware\Core\Defaults;

/**
 * @RouteScope(scopes={"api"})
 */
class VatlayerController extends AbstractController
{
    /**
     * @var \Memo\VatlayerPlugin\Services\VatlayerService
     */
    private $vatlayerService;

    public function __construct(\Memo\VatlayerPlugin\Services\VatlayerService $vatlayerService)
    {
        $this->vatlayerService = $vatlayerService;
    }

    /**
     * @Route("api/v{version}/memo/vatlayer/{id}", name="storefront.memo.vatlayer.vatid-check", methods={"GET"}, defaults={"auth_required"=false})
     */
    public function vatIdCheck(Request $request, Context $context): JsonResponse
    {
        $id = $request->get("id");

        $response = [];
        $message = [];
        try {
            $response = $this->vatlayerService->validate($id);

            // This is a test response
//            $response = [
//                "valid" => true,
//                "database" => "ok",
//                "format_valid" => true,
//                "query" => "NL001402656B40",
//                "country_code" => "NL",
//                "vat_number" => "001402656B40",
//                "company_name" => "MEMO ICT",
//                "company_address" => "\nKALKWARK 00014\n7881LW EMMER-COMPASCUUM\n",
//            ];

            // Dit nog omzetten naar vertaalbare snippets, but how?
            if($response['database'] !== "ok") {
                $message = ["type" => "warning", "message" => "We were unable to validate your VAT ID at this time"];
            } elseif($response['format_valid'] == false) {
                $message = ["type" => "danger", "message" => "Invalid VAT ID format"];
            } elseif($response['valid'] == false) {
                $message = ["type" => "danger", "message" => "Your VAT ID is invalid"];
            } else {
                $message = ["type" => "success", "message" => sprintf("Validated as \"%s\"", $response['company_name'])];
            }
        } catch(\Exception $exception) {
            $message = ["type" => "warning", "message" => $exception->getMessage()];
        }

        $response['message'] = $message;

        return new JsonResponse($response);
    }
}
