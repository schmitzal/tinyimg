<?php

namespace Schmitzal\Tinyimg;

class PriceCalculation
{
    private const PRICE_PER_IMAGE = [
        [
            "imageCount" => 10000,
            "pricePerImage" => 0.002
        ],
        [
            "imageCount" => 500,
            "pricePerImage" => 0.009
        ],
    ];

    /**
     * Calculates price of compression run, given no compression were made in current month
     *
     * @param  $fileCount
     *
     * @return float|int
     */
    private function calculatePrice($fileCount)
    {
        if ($fileCount <= 500) {
            return 0;
        }

        foreach ($this::PRICE_PER_IMAGE as $price) {
            if ($fileCount > $price["imageCount"]) {
                $barrier = $price;
                break;
            }
        }

        $totalPrice = 0;
        if (!empty($barrier)) {
            $totalPrice = $this->calculatePrice($barrier["imageCount"]) + ($fileCount - $barrier["imageCount"]) * $barrier["pricePerImage"];
        }

        return $totalPrice;
    }

    /**
     * Calculates price of compression run in USD.
     *
     * @param  $toCompress
     * @param  $compressedUntilNow
     *
     * @return float
     */
    public function calculateCosts($toCompress, $compressedUntilNow)
    {
        $price = $this->calculatePrice($compressedUntilNow + $toCompress) - $this->calculatePrice($compressedUntilNow);

        $price = round($price, 2);

        return $price;
    }
}
