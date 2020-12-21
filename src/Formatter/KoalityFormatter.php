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

            $checks[$result->getKey()] = $check;

            if ($result->getStatus() == Result::STATUS_FAIL) {
                $status = Result::STATUS_FAIL;
            }
        }

        $formattedResult['status'] = $status;
        $formattedResult['checks'] = $checks;

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
