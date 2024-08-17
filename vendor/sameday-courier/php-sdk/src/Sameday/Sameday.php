<?php

namespace CurieRO\Sameday;

use Exception;
use CurieRO\Sameday\Objects\AwbStatusHistory\ParcelObject;
use CurieRO\Sameday\Requests\SamedayDeleteAwbRequest;
use CurieRO\Sameday\Requests\SamedayGetAwbPdfRequest;
use CurieRO\Sameday\Requests\SamedayGetAwbStatusHistoryRequest;
use CurieRO\Sameday\Requests\SamedayGetCitiesRequest;
use CurieRO\Sameday\Requests\SamedayGetCountiesRequest;
use CurieRO\Sameday\Requests\SamedayGetLockersRequest;
use CurieRO\Sameday\Requests\SamedayGetParcelStatusHistoryRequest;
use CurieRO\Sameday\Requests\SamedayGetPickupPointsRequest;
use CurieRO\Sameday\Requests\SamedayGetStatusSyncRequest;
use CurieRO\Sameday\Requests\SamedayPostAwbRequest;
use CurieRO\Sameday\Requests\SamedayPostAwbEstimationRequest;
use CurieRO\Sameday\Requests\SamedayPostParcelRequest;
use CurieRO\Sameday\Requests\SamedayPutAwbCODAmountRequest;
use CurieRO\Sameday\Requests\SamedayPutParcelSizeRequest;
use CurieRO\Sameday\Requests\SamedayGetServicesRequest;
use CurieRO\Sameday\Responses\SamedayDeleteAwbResponse;
use CurieRO\Sameday\Responses\SamedayGetAwbPdfResponse;
use CurieRO\Sameday\Responses\SamedayGetAwbStatusHistoryResponse;
use CurieRO\Sameday\Responses\SamedayGetCitiesResponse;
use CurieRO\Sameday\Responses\SamedayGetCountiesResponse;
use CurieRO\Sameday\Responses\SamedayGetLockersResponse;
use CurieRO\Sameday\Responses\SamedayGetParcelStatusHistoryResponse;
use CurieRO\Sameday\Responses\SamedayGetPickupPointsResponse;
use CurieRO\Sameday\Responses\SamedayGetStatusSyncResponse;
use CurieRO\Sameday\Responses\SamedayPostAwbEstimationResponse;
use CurieRO\Sameday\Responses\SamedayPostAwbResponse;
use CurieRO\Sameday\Responses\SamedayPostParcelResponse;
use CurieRO\Sameday\Responses\SamedayPutAwbCODAmountResponse;
use CurieRO\Sameday\Responses\SamedayPutParcelSizeResponse;
use CurieRO\Sameday\Responses\SamedayGetServicesResponse;
/**
 * Class that encapsulates endpoints available in sameday api.
 *
 * @package Sameday
 */
class Sameday
{
    /**
     * @var SamedayClientInterface
     */
    protected $client;
    /**
     * Sameday constructor.
     *
     * @param SamedayClientInterface $client
     */
    public function __construct(SamedayClientInterface $client)
    {
        $this->client = $client;
    }
    /**
     * @return SamedayClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }
    /**
     * @param SamedayGetServicesRequest $request
     *
     * @return SamedayGetServicesResponse
     *
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayServerException
     */
    public function getServices(SamedayGetServicesRequest $request)
    {
        return new SamedayGetServicesResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayGetPickupPointsRequest $request
     *
     * @return SamedayGetPickupPointsResponse
     *
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayServerException
     */
    public function getPickupPoints(SamedayGetPickupPointsRequest $request)
    {
        return new SamedayGetPickupPointsResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayPutParcelSizeRequest $request
     *
     * @return SamedayPutParcelSizeResponse
     *
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayBadRequestException
     * @throws Exceptions\SamedayServerException
     */
    public function putParcelSize(SamedayPutParcelSizeRequest $request)
    {
        return new SamedayPutParcelSizeResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayGetParcelStatusHistoryRequest $request
     *
     * @return SamedayGetParcelStatusHistoryResponse
     *
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayNotFoundException
     * @throws Exceptions\SamedayOtherException
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayServerException
     * @throws Exception
     */
    public function getParcelStatusHistory(SamedayGetParcelStatusHistoryRequest $request)
    {
        return new SamedayGetParcelStatusHistoryResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayDeleteAwbRequest $request
     *
     * @return SamedayDeleteAwbResponse
     *
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayNotFoundException
     * @throws Exceptions\SamedayOtherException
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayServerException
     */
    public function deleteAwb(SamedayDeleteAwbRequest $request)
    {
        return new SamedayDeleteAwbResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayPostAwbRequest $request
     *
     * @return SamedayPostAwbResponse
     *
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayBadRequestException
     * @throws Exceptions\SamedayNotFoundException
     * @throws Exceptions\SamedayOtherException
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayServerException
     */
    public function postAwb(SamedayPostAwbRequest $request)
    {
        return new SamedayPostAwbResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayPostAwbEstimationRequest $request
     *
     * @return SamedayPostAwbEstimationResponse
     *
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayBadRequestException
     * @throws Exceptions\SamedayNotFoundException
     * @throws Exceptions\SamedayOtherException
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayServerException
     */
    public function postAwbEstimation(SamedayPostAwbEstimationRequest $request)
    {
        return new SamedayPostAwbEstimationResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayGetCountiesRequest $request
     *
     * @return SamedayGetCountiesResponse
     *
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayBadRequestException
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayServerException
     */
    public function getCounties(SamedayGetCountiesRequest $request)
    {
        return new SamedayGetCountiesResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayGetCitiesRequest $request
     *
     * @return SamedayGetCitiesResponse
     *
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayBadRequestException
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayServerException
     */
    public function getCities(SamedayGetCitiesRequest $request)
    {
        return new SamedayGetCitiesResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayGetStatusSyncRequest $request
     *
     * @return SamedayGetStatusSyncResponse
     *
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayBadRequestException
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayServerException
     * @throws Exception
     */
    public function getStatusSync(SamedayGetStatusSyncRequest $request)
    {
        return new SamedayGetStatusSyncResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayPostParcelRequest $request
     *
     * @return SamedayPostParcelResponse
     *
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayBadRequestException
     * @throws Exceptions\SamedayNotFoundException
     * @throws Exceptions\SamedayOtherException
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayServerException
     * @throws Exception
     */
    public function postParcel(SamedayPostParcelRequest $request)
    {
        $parcelsRequest = new SamedayGetAwbStatusHistoryRequest($request->getAwbNumber());
        // Get old parcels.
        $parcelsResponse = $this->getAwbStatusHistory($parcelsRequest);
        $oldParcels = array_map(function (ParcelObject $parcel) {
            return $parcel->getParcelAwbNumber();
        }, $parcelsResponse->getParcels());
        // Create new parcel.
        $response = $this->client->sendRequest($request->buildRequest());
        // Get new parcels.
        $parcelsResponse = $this->getAwbStatusHistory($parcelsRequest);
        $newParcels = array_map(function (ParcelObject $parcel) {
            return $parcel->getParcelAwbNumber();
        }, $parcelsResponse->getParcels());
        $newParcel = array_values(array_diff($newParcels, $oldParcels));
        return new SamedayPostParcelResponse($request, $response, $newParcel[0]);
    }
    /**
     * @param SamedayGetAwbPdfRequest $request
     *
     * @return SamedayGetAwbPdfResponse
     *
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayBadRequestException
     * @throws Exceptions\SamedayNotFoundException
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayServerException
     */
    public function getAwbPdf(SamedayGetAwbPdfRequest $request)
    {
        return new SamedayGetAwbPdfResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayGetAwbStatusHistoryRequest $request
     *
     * @return SamedayGetAwbStatusHistoryResponse
     *
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayBadRequestException
     * @throws Exceptions\SamedayNotFoundException
     * @throws Exceptions\SamedayOtherException
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayServerException
     * @throws Exception
     */
    public function getAwbStatusHistory(SamedayGetAwbStatusHistoryRequest $request)
    {
        return new SamedayGetAwbStatusHistoryResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayGetLockersRequest $request
     *
     * @return SamedayGetLockersResponse
     *
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayServerException
     * @throws Exceptions\SamedayBadRequestException
     */
    public function getLockers(SamedayGetLockersRequest $request)
    {
        return new SamedayGetLockersResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
    /**
     * @param SamedayPutAwbCODAmountRequest $request
     *
     * @return SamedayPutAwbCODAmountResponse
     *
     * @throws Exceptions\SamedaySDKException
     * @throws Exceptions\SamedayAuthenticationException
     * @throws Exceptions\SamedayAuthorizationException
     * @throws Exceptions\SamedayServerException
     * @throws Exceptions\SamedayBadRequestException
     */
    public function putAwbCODAmount(SamedayPutAwbCODAmountRequest $request)
    {
        return new SamedayPutAwbCODAmountResponse($request, $this->client->sendRequest($request->buildRequest()));
    }
}
