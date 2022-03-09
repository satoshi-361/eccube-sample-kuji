<?php

namespace Plugin\SortProduct4Plus\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\SortProduct4Plus\Entity\SortProduct;
use Symfony\Bridge\Doctrine\RegistryInterface;


class SortProductRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SortProduct::class);
    }


    /*
     * SQL方式
     */

    // sort_noのASC順に全レコードを取得する
    private function getAllIdBySortNoASC()
    {
        // 同じsort_noが1個より多くあるレコード数
        $sql = 'SELECT sp.id
                FROM plg_sort_product sp
                ORDER BY sp.sort_no ASC
                ';

        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }

    // sort_noを1から振り直す
    // 振り直す順番は、sort_noのASC順で振り直す
    public function renewSortNo()
    {
        // sort_noのDESC順に全レコードを取得する
        $idArray = $this->getAllIdBySortNoASC();

        // sort_noを1から振り直すSQL群を作成する
        $sql = '';
        $sortNo = 1;
        foreach ($idArray as $idData) {
            $id = isset($idData['id']) ? $idData['id'] : null;
            if ($id !== null) {
                $sql .= 'UPDATE plg_sort_product sp SET sort_no = ' . $sortNo . ' WHERE sp.id = ' . $id . ';';
                $sortNo++;
            }
        }

        if ($sql != '') {
            // 振り直す対象がある場合: 振り直す
            $result = $this->getEntityManager()->getConnection()->executeUpdate($sql);

        } else {
            // 振り直す対象がない場合
            $result = 0;
        }

        return $result;
    }

    // [!!! 処理後に必ず [sort_no重複調査] を実施する !!!]
    // [dtb_product]テーブルにある商品で、[dtb_sort_product]テーブルに設定されていないレコードを探して登録する
    // ソート情報(sort_no)はとりあえずとして商品IDをつけておく
    public function insertSortProductNoExist()
    {
        $sql = 'INSERT INTO plg_sort_product
                (
                    id, 
                    sort_no, 
                    discriminator_type
                )
                SELECT
                  p.id,
                  p.id,
                  \'sortproduct\'
                FROM dtb_product AS p
                WHERE NOT EXISTS (
                    SELECT p.id
                    FROM plg_sort_product AS sp
                    WHERE p.id = sp.id
                )
                ';

        return $this->getEntityManager()->getConnection()->executeUpdate($sql);
    }

    // [dtb_product]テーブルにない商品で、[dtb_sort_product]テーブルに設定されているレコードを探して削除する
    // 削除されてしまった商品を想定
    public function deleteSortProductNoExist()
    {
        // [dtb_product]テーブルにない商品で、[dtb_sort_product]テーブルに設定されているid一覧の取得
        $idArray = $this->getIdNoExistProduct();


        // 削除対象のID一覧を作成する
        $newIdArray = array();
        foreach ($idArray as $idData) {
            $id = isset($idData['id']) ? $idData['id'] : null;
            if ($id !== null) {
                $newIdArray[] = $id;
            }
        }

        if (count($newIdArray) > 0) {
            // 削除対象がある場合

            // 削除するSQLを作成する
            $sql = 'DELETE FROM plg_sort_product
                WHERE id IN (:idString)
                ';

            // カンマ区切り数字配列がパラメータ渡しできないので、置換する
            $sql = str_replace(':idString', implode(',', $newIdArray), $sql);

            $result = $this->getEntityManager()->getConnection()->executeUpdate($sql);

        } else {
            // 削除対象がない場合
            $result = 0;
        }

        return $result;
    }
    // [dtb_product]テーブルにない商品で、[dtb_sort_product]テーブルに設定されているid一覧の取得
    private function getIdNoExistProduct()
    {
        // 同じsort_noが1個より多くあるレコード数
        $sql = 'SELECT sp.id
                FROM plg_sort_product sp
                LEFT OUTER JOIN dtb_product p
                ON p.id = sp.id
                WHERE p.id IS NULL
                ';

        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }


    // [plg_sort_product]テーブルで[sort_no]がnullのレコードを探して、値を設定する
    public function setSortNoWithNull()
    {
        $sql = 'UPDATE plg_sort_product SET sort_no = id WHERE sort_no is null';

        return $this->getEntityManager()->getConnection()->executeUpdate($sql);
    }


    // テーブル内で重複しているソート情報(sort_no)一覧を取得する
    public function countDupliSortNo()
    {
        // 同じsort_noが1個より多くあるレコード数
        $sql = 'SELECT sp.sort_no
                FROM plg_sort_product sp
                GROUP BY sp.sort_no
                HAVING count(sp.id) > 1';

        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }

    // sort_noがnullのレコード数を返す
    public function countNullSortNo()
    {
        // sort_noがnullのレコード数
        $sql = 'SELECT count(sp.id) as null_count
                FROM plg_sort_product sp
                WHERE sp.sort_no is null
                ';

        $result = $this->getEntityManager()->getConnection()->fetchAll($sql);

        return (isset($result[0]['null_count'])) ? $result[0]['null_count'] : null;
    }

    // [!!! sort_noが重複していないことが前提 !!!]
    // sort_no指定でproduct_idを取得する
    public function getProductIdBySortNo($sort_no)
    {
        $sql = 'SELECT sp.id as product_id
                FROM plg_sort_product sp
                WHERE sp.sort_no = :sort_no
                ';

        $params = array(
            'sort_no' => $sort_no,
        );

        $result = $this->getEntityManager()->getConnection()->fetchAll($sql, $params);

        if (count($result) > 1) {
            // sort_noが重複している場合
            // システムエラーが発生しました。大変お手数ですが、サイト管理者までご連絡ください。
            throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('[SortProduct] sort_no重複エラー sort_no:' . $sort_no);
        } else {
            // sort_noが重複していない場合
            $result = (isset($result[0]['product_id'])) ? $result[0]['product_id'] : null;
        }

        return $result;
    }

    // product_id指定でsort_noを取得する
    public function getSortNoByProductId($product_id)
    {
        $sql = 'SELECT sp.sort_no
                FROM plg_sort_product sp
                WHERE sp.id = :product_id
                ';

        $params = array(
            'product_id' => $product_id,
        );

        $result = $this->getEntityManager()->getConnection()->fetchAll($sql, $params);

        $result = (isset($result[0]['sort_no'])) ? $result[0]['sort_no'] : null;

        return $result;
    }


    // sort_noを小さい数値へ移動する処理 (順位を下げる場合に使用する)
    public function toSmallSortNo($fromProductId, $fromSortNo, $toSortNo)
    {
        // 移動対象以外をずらす処理 (移動対象以外のsort_noを大きくする)
        // 例: id:5 sort_no: 5 -> 3 にする場合
        //     UPDATE plg_sort_product SET sort_no = sort_no + 1 WHERE sort_no >= 3 AND sort_no < 5;
        $sql = 'UPDATE plg_sort_product
                SET sort_no = sort_no + 1
                WHERE sort_no >= :toSortNo AND sort_no < :fromSortNo
                ';

        $params = array(
            'toSortNo'   => $toSortNo,
            'fromSortNo' => $fromSortNo,
        );

        $result = $this->getEntityManager()->getConnection()->executeUpdate($sql, $params);


        // 移動対象のsort_noを再定義する
        // 例: id:5 sort_no: 5 -> 3 にする場合
        //     UPDATE plg_sort_product SET sort_no = 3 where id = 5;
        $sql = 'UPDATE plg_sort_product
                SET sort_no = :toSortNo
                WHERE id = :fromProductId
                ';

        $params = array(
            'toSortNo'   => $toSortNo,
            'fromProductId' => $fromProductId,
        );

        $result = $this->getEntityManager()->getConnection()->executeUpdate($sql, $params);

        return $result;
    }

    // sort_noを大きい数値へ移動する処理 (順位を上げる場合に使用する)
    // [!!! sort_noが整地されていることが前提 !!!]
    public function toBigSortNo($fromProductId, $fromSortNo, $toSortNo)
    {
        // 移動対象以外をずらす処理 (移動対象以外のsort_noを小さくする)
        // 例: id:3 sort_no: 3 -> 5 にする場合
        //     UPDATE plg_sort_product SET sort_no = sort_no - 1 WHERE sort_no > 3 AND sort_no <= 5;
        $sql = 'UPDATE plg_sort_product
                SET sort_no = sort_no - 1
                WHERE sort_no > :fromSortNo AND sort_no <= :toSortNo
                ';

        $params = array(
            'toSortNo'   => $toSortNo,
            'fromSortNo' => $fromSortNo,
        );

        $result = $this->getEntityManager()->getConnection()->executeUpdate($sql, $params);


        // 移動対象のsort_noを再定義する
        // 例: id:3 sort_no: 3 -> 5 にする場合
        //     UPDATE plg_sort_product SET sort_no = 5 where id = 3;
        $sql = 'UPDATE plg_sort_product
                SET sort_no = :toSortNo
                WHERE id = :fromProductId
                ';

        $params = array(
            'toSortNo'   => $toSortNo,
            'fromProductId' => $fromProductId,
        );

        $result = $this->getEntityManager()->getConnection()->executeUpdate($sql, $params);

        return $result;
    }

    /*
     * SQL方式 ここまで
     */


    // 並び替え画面向けの商品一覧(フロントの商品一覧とほぼ同じもの)を取得
    // ProductRepository::getQueryBuilderBySearchData()とほぼ同じだが、[公開/非公開/廃止]を無視するところが異なる
    // ・商品の[公開/非公開/廃止]は無視
    // ・カテゴリー指定がある場合はカテゴリーで絞り込む
    // ・ソート順は [おすすめ順]
    public function getQbSortProduct($searchData)
    {
        $qb = $this->createQueryBuilder('sp');
        $qb->select('p as Product, sp.id as product_id, sp.sort_no');

        // dtb_product
        $qb->innerJoin('Eccube\Entity\Product', 'p', 'WITH', 'p.id = sp.id');

        // 商品の[公開/非公開/廃止]は無視
        // $qb->andWhere('p.Status = 1');


        // category
        // カテゴリー指定がある場合はカテゴリーで絞り込む
        // 数字の場合 (エンティティでない場合) はエンティティにする
        if (!empty($searchData['category_id']) && $searchData['category_id']) {
            $categoryId = $searchData['category_id'];
            if (is_numeric($categoryId) === true) {
                // 数字の場合 (エンティティでない場合)
                $Category = $this->container->get(\Eccube\Repository\CategoryRepository::class)->findOneBy(array('id' => $categoryId));
                $searchData['category_id'] = $Category;
            }
        }
        if (!empty($searchData['category_id']) && $searchData['category_id']) {
            $Categories = $searchData['category_id']->getSelfAndDescendants();
            if ($Categories) {
                $qb
                    ->innerJoin('p.ProductCategories', 'pct')
                    ->innerJoin('pct.Category', 'c')
                    ->andWhere($qb->expr()->in('pct.Category', ':Categories'))
                    ->setParameter('Categories', $Categories);
            }
        }

        // ソート順は [おすすめ順]
        $qb->orderBy('sp.sort_no', 'DESC');

        return $qb;
    }

}
