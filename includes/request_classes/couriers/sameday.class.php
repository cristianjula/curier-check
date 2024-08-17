<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class APISamedayClass
{
    protected $sameday;

    protected $sameday_client;

    private $api_url;

    public function __construct()
    {
        $this->sameday_client = new CurieRO\Sameday\SamedayClient(
            get_option('sameday_username', ''),
            get_option('sameday_password', ''),
            null,
            null,
            null,
            apply_filters('curiero_sameday_http_client', null),
            new SamedayWordpressPersistentDataHandler(),
        );

        $this->sameday = new CurieRO\Sameday\Sameday($this->sameday_client);

        $this->api_url = curiero_get_api_url('/v1/shipping/sameday/');
    }

    public function getLatestStatus(string $awb): ?string
    {
        try {
            $response = $this->sameday->getAwbStatusHistory(
                new CurieRO\Sameday\Requests\SamedayGetAwbStatusHistoryRequest($awb),
            );

            return $response->getHistory()[0]->getState();
        } catch (Exception $e) {
            return $response = null;
        }
    }

    public function getAdditionalServices(): CurieRO\Illuminate\Support\Collection
    {
        if (
            ($services = get_transient('curiero_sameday_services'))
            && $services->isNotEmpty()
        ) {
            return $services;
        }

        try {
            $servicesRequest = $this->sameday->getServices(new CurieRO\Sameday\Requests\SamedayGetServicesRequest());
            $services = collect($servicesRequest->getServices());
            $services->transform(function (object $service): array {
                return [
                    'id' => $service->getId(),
                    'name' => $service->getName(),
                    'delivery' => [
                        'id' => $service->getDeliveryType()->getId(),
                        'name' => $service->getDeliveryType()->getName(),
                    ],
                    'optional_taxes' => collect($service->getOptionalTaxes())->transform(function (object $tax): array {
                        return [
                            'id' => $tax->getId(),
                            'tax' => $tax->getTax(),
                            'package_type' => $tax->getPackageType()->getType(),
                            'name' => $tax->getName(),
                            'code' => $tax->getCode(),
                        ];
                    })->toArray(),
                ];
            });

            $services = $services->whereNotIn('name', ['Colet la schimb', 'Retur Documente'])->values();

            if ($services->isNotEmpty()) {
                set_transient('curiero_sameday_services', $services, DAY_IN_SECONDS);
            }
        } catch (Exception $e) {
            $services = collect();
        }

        return $services;
    }

    public function calculate(array $parameters): ?string
    {
        $tarif = $this->sameday->postAwbEstimation(new CurieRO\Sameday\Requests\SamedayPostAwbEstimationRequest(
            get_option('sameday_pickup_point'),
            null,
            new CurieRO\Sameday\Objects\Types\PackageType(get_option('sameday_package_type', 0)),
            [
                new CurieRO\Sameday\Objects\ParcelDimensionsObject(
                    $parameters['weight'],
                    $parameters['width'],
                    $parameters['length'],
                    $parameters['height'],
                ),
            ],
            $parameters['service_id'],
            new CurieRO\Sameday\Objects\Types\AwbPaymentType(
                CurieRO\Sameday\Objects\Types\AwbPaymentType::CLIENT,
            ),
            new CurieRO\Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject(
                $parameters['city'],
                $parameters['state'],
                trim($parameters['address']) ?: 'Principala 1',
                null,
                null,
                null,
                null,
            ),
            $parameters['declared_value'],
            $parameters['cod_value'],
        ));

        return $tarif->getCost();
    }
}

if (!class_exists('SamedayWordpressPersistentDataHandler')) {
    class SamedayWordpressPersistentDataHandler implements CurieRO\Sameday\PersistentData\SamedayPersistentDataInterface
    {
        /**
         * Get a value from a persistent data store.
         *
         * @param string $key
         * @return mixed
         */
        public function get($key)
        {
            return get_transient("curiero_sameday_persistent_{$key}");
        }

        /**
         * Set a value in the persistent data store.
         *
         * @param string $key
         * @param mixed $value
         */
        public function set($key, $value): void
        {
            set_transient("curiero_sameday_persistent_{$key}", $value, 7 * DAY_IN_SECONDS);
        }
    }
}
