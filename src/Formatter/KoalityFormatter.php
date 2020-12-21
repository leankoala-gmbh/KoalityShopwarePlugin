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
                'message' => $result->getMessage(),
                'key' => $result->getKey()
            ];

            if($result->getLimit()) {
                $detail['limit'] = $result->getLimit();
            }

            if(!is_null($result->getCurrentValue())) {
                $detail['current_value'] = $result->getCurrentValue();
            }

            $details[] = $detail;

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
