<?php

namespace CurieRO\Innoship\Api;

use CurieRO\Innoship\Request\CreateOrder;
use CurieRO\Innoship\Request\UpdateOrderStatus;
use CurieRO\Innoship\Response\Contract;
use CurieRO\Innoship\Traits\HasHttpClient;
class Order
{
    use HasHttpClient;
    public function create(CreateOrder $request): Contract
    {
        return $this->getClient()->post("Order", $request->data());
    }
    public function validate(CreateOrder $request): Contract
    {
        return $this->getClient()->post("Order/validate", $request->data());
    }
    /**
     * @param int $courierId See https://docs.innoship.io/innoship/tables
     * @param string $awb AWB number for update status
     * @param int $statusId See Status id Table. Can only send id 3 (Handover) or any final status
     * @param DateTime $actionDateTime DateTime of the event.
     * @return Contract
     */
    public function updateStatus($courierId, $awb, $statusId, $actionDateTime): Contract
    {
        return $this->getClient()->post("Order/UpdateStatus", ['CourierId' => $courierId, 'CourierShipmentId' => $awb, 'StatusId' => $statusId, 'ActionDateTime' => $actionDateTime]);
    }
    public function delete($courierId, $awb): Contract
    {
        return $this->getClient()->delete("Order/{$courierId}/awb/{$awb}");
    }
}
