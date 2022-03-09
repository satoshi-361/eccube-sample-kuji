<?php
namespace Plugin\SortProduct4Plus\Entity;

use Eccube\Common\EccubeConfig;
use Eccube\Doctrine\Query\OrderByClause;
use Eccube\Doctrine\Query\OrderByCustomizer;
use Eccube\Entity\Master\ProductListOrderBy;
use Eccube\Repository\QueryKey;

/**
 * Class SortProductOrderBy
 * @package Plugin\SortProduct4Plus\Entity
 * @see Plugin\SortProduct4Plus\Resource\config\services.yml
 */
class SortProductOrderBy extends OrderByCustomizer
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;
    /**
     * ProductRankCustomizer constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    protected function createStatements($params, $queryKey)
    {
        if (!isset($params['orderby']) || !$params['orderby'] instanceof ProductListOrderBy) {
            return [];
        }
        /** @var ProductListOrderBy $OrderBy */
        $OrderBy = $params['orderby'];
        if ($OrderBy->getName() != $this->eccubeConfig->get('sort_product.list_order_by.name')) {
            return [];
        }
        return [
            // prefer SortProductJoin
            0 => new OrderByClause('sp.sort_no', 'DESC')
        ];
    }

    public function getQueryKey()
    {
        return QueryKey::PRODUCT_SEARCH;
    }
}