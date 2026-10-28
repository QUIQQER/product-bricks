<?php

namespace QUI\ProductBricks\Utils;

use QUI;
use QUI\ERP\Products\Handler\Products;
use QUI\ERP\Products\Product\Product;
use QUI\ERP\Products\Search\FrontendSearch;

/**
 * Apply frontend visibility rules before limiting and loading brick products.
 */
final class ProductSearch
{
    /**
     * @param array<string, mixed> $searchParams
     * @return array<int, Product>
     */
    public static function getProducts(
        array $searchParams,
        bool $showVariantChildren,
        int $limit,
        string $order
    ): array {
        $Search = new FrontendSearch();

        // A brick may hide children, but cannot override the global search setting.
        if (!$showVariantChildren) {
            $Search->ignoreVariantChildren();
        }

        $orderParts = preg_split('/\s+/', trim($order), 2);
        $sortOn = $orderParts[0] ?? '';

        if ($sortOn === 'name') {
            $sortOn = 'title';
        } elseif ($sortOn === 'manuell') {
            $sortOn = 'priority';
        }

        $searchParams['fields'] = [];
        $searchParams['ignoreFindVariantParentsByChildValues'] = true;
        $searchParams['limit'] = $limit;
        $searchParams['sortOn'] = $sortOn;
        $searchParams['sortBy'] = strtoupper($orderParts[1] ?? 'ASC');

        $ids = $Search->search($searchParams);
        $products = [];

        if (!is_array($ids)) {
            return [];
        }

        foreach ($ids as $id) {
            try {
                $products[] = Products::getProduct((int)$id);
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::addDebug($Exception->getMessage());
            }
        }

        return $products;
    }
}
