<?php

namespace Koality\ShopwarePlugin\Formatter;

class KoalityFormatter
{
    /**
     * @var Result[]
     */
    private $results = [];

    public function addResult(Result $result)
    {
        $this->results[] = $result;
    }

    /**
     * return array
     */
    public function getFormattedResults()
    {
        $formattedResult = [];

        $details = [];

        $status = Result::STATUS_PASS;

        foreach ($this->results as $result) {
            $detail = [
                'status' => $result->getStatus(),
                'message' => $result->getMessage()
            ];

            if ($result->getLimit()) {
                $detail['limit'] = $result->getLimit();
            }

            if (!is_null($result->getObservedValue())) {
                $detail['observedValue'] = $result->getObservedValue();
            }

            $details[$result->getKey()] = $detail;

            if ($result->getStatus() == Result::STATUS_FAIL) {
                $status = Result::STATUS_FAIL;
            }
        }

        $formattedResult['status'] = $status;
        $formattedResult['details'] = $details;

        $formattedResult['info'] = $this->getInfoBlock();

        return $formattedResult;
    }

    private function getInfoBlock()
    {
        return [
            'creator' => 'koality.io Shopware Plugin',
            'version' => '1.0.0',
            'plugin_url' => 'https://www.koality.io/plugins/shopware'
        ];
    }
}
