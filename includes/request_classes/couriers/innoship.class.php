<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class APIInnoshipClass
{
    protected $client;

    public function __construct()
    {
        $this->client = new CurieRO\Innoship\Innoship(
            get_option('innoship_api_key', ''),
            true,
        );
    }

    public function getParcelStatus(string $awb, int $courier_id): array
    {
        try {
            if (empty($courier_id) || empty($awb)) {
                throw new Exception();
            }

            $request = $this->client->track()->byAwb($courier_id, $awb);
            if (!$request->isSuccessful()) {
                throw new Exception();
            }

            $status = $request->getContent();
            if (empty($status)) {
                throw new Exception();
            }

            $status = $status[0]['history'] ?? [];

            return end($status);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getLockers(string $county, string $locality): array
    {
        if (strlen($county) <= 2) {
            $county = curiero_get_counties_list($county);
        }

        if (
            is_null($county)
            || is_array($county)
        ) {
            return [];
        }

        $location_id = get_option('innoship_location_id', '');
        $hash = $this->generateHash($location_id, $county, $locality);

        if (
            ($innoship_locker_list = get_transient('curiero_innoship_locker_list') ?: [])
            && !empty($innoship_locker_list)
            && isset($innoship_locker_list[$hash])
        ) {
            return $innoship_locker_list[$hash];
        }

        try {
            $location = new CurieRO\Innoship\Request\GetFixedLocations();
            $location->setCountryCode('RO')
                ->setCountyName($county)
                ->setLocalityName($locality)
                ->setExternalLocationId($location_id)
                ->setFixedLocationType($location::TYPE_LOCKER);

            $request = $this->client->location()->fixedLocations($location);
            if (!$request->isSuccessful()) {
                throw new Exception();
            }

            $lockers = $request->getContent();
            if (!empty($lockers)) {
                $innoship_locker_list[$hash] = $lockers;
                $transient_exists = get_option('_transient_timeout_curiero_innoship_locker_list');

                set_transient('curiero_innoship_locker_list', $innoship_locker_list, $transient_exists ? null : DAY_IN_SECONDS);
            }

            return $lockers;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getClientLocations(): array
    {
        if (
            ($innoship_client_locations = get_transient('curiero_innoship_client_locations'))
            && !empty($innoship_client_locations)
        ) {
            return $innoship_client_locations;
        }

        try {
            $request = $this->client->location()->clientLocations();
            if (!$request->isSuccessful()) {
                throw new Exception();
            }

            $innoship_client_locations = $request->getContent();

            if (!empty($innoship_client_locations)) {
                set_transient('curiero_innoship_client_locations', $innoship_client_locations, DAY_IN_SECONDS);
            }

            return $innoship_client_locations ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getClientCouriers(): array
    {
        if (
            ($innoship_client_couriers = get_transient('curiero_innoship_client_couriers'))
            && !empty($innoship_client_couriers)
        ) {
            return $innoship_client_couriers;
        }

        $innoship_client_couriers = collect($this->getClientLocations())
            ->pluck('courierServices')
            ->flatten(1)
            ->pluck('courier', 'courierId')
            ->sortKeys()
            ->toArray();

        if (!empty($innoship_client_couriers)) {
            set_transient('curiero_innoship_client_couriers', $innoship_client_couriers, DAY_IN_SECONDS);
        }

        return $innoship_client_couriers;
    }

    public static function getClientServices(): array
    {
        return CurieRO\Innoship\Tables::services();
    }

    public static function getCourierName(int $courier_id): string
    {
        return CurieRO\Innoship\Tables::carrierNameById($courier_id) ?: '';
    }

    private function generateHash(string $location_id, string $county, string $locality): string
    {
        $algoList = hash_algos();

        if (in_array('crc32c', $algoList)) {
            $algo = 'crc32c';
        } elseif (in_array('crc32', $algoList)) {
            $algo = 'crc32';
        } else {
            $algo = 'md5';
        }

        return hash($algo, $location_id . $county . $locality);
    }
}
