<?php

namespace Koality\ShopwarePlugin\Formatter;

/**
 * Class KoalityFormatter
 *
 * This class is used to create an IETF conform health JSON that can be
 * read by koality.io.
 *
 * @see https://tools.ietf.org/html/draft-inadarei-api-health-check-05
 *
 * @package Koality\ShopwarePlugin\Formatter
 *
 * @author Nils Langner <nils.langner@leankoala.com>
 * created 2020-12-28
 */
class KoalityFormatter
{
    /**
     * @var Result[]
     */
    private $results = [];

    /**
     * Add a new result.
     *
     * If the status of the result is "fail" the whole check will be marked as failed.
     *
     * @param Result $result
     */
    public function addResult(Result $result)
    {
        $this->results[] = $result;
    }

    /**
     * Return an IETF conform result array with all sub results.
     *
     * @return array
     */
    public function getFormattedResults()
    {
        $formattedResult = [];
        $checks = [];
        $status = Result::STATUS_PASS;

        foreach ($this->results as $result) {
            $check = [
                'status' => $result->getStatus(),
                'output' => $result->getMessage()
            ];

            if ($result->getLimit()) {
                $check['limit'] = $result->getLimit();
            }

            if (!is_null($result->getObservedValue())) {
                $check['observedValue'] = $result->getObservedValue();
            }


            if (!is_null($result->getObservedValueUnit())) {
                $check['observedUnit'] = $result->getObservedValueUnit();
            }

            $checks[$result->getKey()] = $check;

            if ($result->getStatus() == Result::STATUS_FAIL) {
                $status = Result::STATUS_FAIL;
            }

            $attributes = $result->getAttributes();
            if (count($attributes) > 0) {
                $formattedResult['attributes'] = $attributes;
            }
        }

        $formattedResult['status'] = $status;
        $formattedResult['output'] = $this->getOutput($status);

        $formattedResult['checks'] = $checks;

        $formattedResult['info'] = $this->getInfoBlock();

        return $formattedResult;
    }

    /**
     * Get the output string depending on the given status.
     *
     * @param string $status
     *
     * @return string
     */
    private function getOutput($status)
    {
        if ($status === Result::STATUS_PASS) {
            return 'All Shopware6 health metrics passed.';
        } else {
            return 'Some Shopware6 health metrics failed: ';
        }
    }

    /**
     * Return the info block for the JSON output
     *
     * @return string[]
     */
    private function getInfoBlock()
    {
        return [
            'creator' => 'koality.io Shopware Plugin',
            'version' => '1.0.0',
            'plugin_url' => 'https://www.koality.io/plugins/shopware'
        ];
    }
}
