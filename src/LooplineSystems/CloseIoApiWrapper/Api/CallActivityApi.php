<?php

declare(strict_types=1);

/**
 * Close.io Api Wrapper - LLS Internet GmbH - Loopline Systems
 *
 * @link      https://github.com/loopline-systems/closeio-api-wrapper for the canonical source repository
 * @copyright Copyright (c) 2014 LLS Internet GmbH - Loopline Systems (http://www.loopline-systems.com)
 * @license   https://github.com/loopline-systems/closeio-api-wrapper/blob/master/LICENSE (MIT Licence)
 */

namespace LooplineSystems\CloseIoApiWrapper\Api;

use LooplineSystems\CloseIoApiWrapper\CloseIoResponse;
use LooplineSystems\CloseIoApiWrapper\Library\Api\AbstractApi;
use LooplineSystems\CloseIoApiWrapper\Library\Exception\BadApiRequestException;
use LooplineSystems\CloseIoApiWrapper\Library\Exception\ResourceNotFoundException;
use LooplineSystems\CloseIoApiWrapper\Model\CallActivity;

class CallActivityApi extends AbstractApi
{
    /**
     * The maximum number of items that are requested by default
     */
    private const MAX_ITEMS_PER_REQUEST = 100;

    const NAME = 'CallActivityApi';

    /**
     * {@inheritdoc}
     */
    protected function initUrls()
    {
        $this->urls = [
            'get-calls' => '/activity/call/',
            'get-call' => '/activity/call/[:id]/',
            'add-call' => '/activity/call/',
            'delete-call' => '/activity/call/[:id]/',
        ];
    }

    /**
     * Gets up to the specified number of activities that match the given
     * criteria.
     *
     * @param int      $offset  The offset from which start getting the items
     * @param int      $limit   The maximum number of items to get
     * @param array    $filters A set of criteria to filter the items by
     * @param string[] $fields  The subset of fields to get (defaults to all)
     *
     * @return CallActivity[]
     */
    public function list(int $offset = 0, int $limit = self::MAX_ITEMS_PER_REQUEST, array $filters = [], array $fields = []): array
    {
        /** @var CallActivity[] $activities */
        $activities = [];
        $result = $this->triggerGet(
            $this->prepareRequest('get-calls', null, [], array_merge($filters, [
                '_skip' => $offset,
                '_limit' => $limit,
                '_fields' => $fields,
            ]))
        );

        if (200 === $result->getReturnCode()) {
            $responseData = $result->getData();

            foreach ($responseData[CloseIoResponse::GET_RESPONSE_DATA_KEY] as $activity) {
                $activities[] = new CallActivity($activity);
            }
        }

        return $activities;
    }

    /**
     * Gets the information about the call activity that matches the given ID.
     *
     * @param string   $id     The ID of the activity
     * @param string[] $fields The subset of fields to get (defaults to all)
     *
     * @return CallActivity
     *
     * @throws ResourceNotFoundException If a call activity with the given ID
     *                                   doesn't exists
     */
    public function get(string $id, array $fields = []): CallActivity
    {
        $apiRequest = $this->prepareRequest('get-call', null, ['id' => $id], ['_fields' => $fields]);

        return new CallActivity($this->triggerGet($apiRequest)->getData());
    }

    /**
     * Creates a new call activity using the given information.
     *
     * @param CallActivity $activity The information of the call activity
     *                               to create
     *
     * @return CallActivity
     *
     * @throws BadApiRequestException
     */
    public function create(CallActivity $activity): CallActivity
    {
        $apiRequest = $this->prepareRequest('add-call', json_encode($activity));

        return new CallActivity($this->triggerPost($apiRequest)->getData());
    }

    /**
     * Deletes the given call activity.
     *
     * @param CallActivity $activity The call activity to delete
     *
     * @throws ResourceNotFoundException If a call activity with the given ID
     *                                   doesn't exists
     */
    public function delete(CallActivity $activity): void
    {
        $id = $activity->getId();

        $activity->setId(null);

        $this->triggerDelete($this->prepareRequest('delete-call', null, ['id' => $id]));
    }
}