<?php


namespace Plugin\SortProduct4Plus\Service;

use Doctrine\ORM\EntityManager;
use Plugin\SortProduct4Plus\Entity\SortProduct;
use Plugin\SortProduct4Plus\Repository\SortProductRepository;

/**
 * ソート処理系のサービス
 */
class SortService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var SortProductRepository
     */
    private $SortProductRepository;

    /**
     * @var \Eccube\Common\EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * SortService constructor.
     * @param EntityManager $entityManager
     * @param SortProductRepository $SortProductRepository
     */
    public function __construct(EntityManager $entityManager, SortProductRepository $SortProductRepository,
                                \Eccube\Common\EccubeConfig $eccubeConfig,
                                \Symfony\Component\DependencyInjection\ContainerInterface $container
    )
    {
        $this->entityManager = $entityManager;
        $this->SortProductRepository = $SortProductRepository;
        $this->eccubeConfig = $eccubeConfig;
        $this->container = $container;
    }


    // 順序設定画面向け
    // 順序テーブル(plg_sort_product)を整地する
    public function refineSortProductAdmin()
    {
        // 順序テーブル(plg_sort_product) に未登録商品があった場合は追加する
        $this->setNewProduct();


        // 順序テーブル(plg_sort_product) に存在しない商品(削除された商品)があった場合は削除する
        $this->deleteProduct();


        // 順序テーブル(plg_sort_product) で ランク情報がnullのレコードがあった場合は値を設定する
        // [使用中止] $this->setNewRank();
        // ＝＞ 何もしない (nullのレコードがあった場合は、あとの処理でランクを振り直す)


        // 商品ID重複 対応
        // ＝＞ 何もしない (商品IDは [plg_sort_product] のidのため重複しない)


        // ランクが重複している or ランクがnull の レコードがあった場合、ランクを振り直す
        $this->renewRank();


        return true;
    }

    // 順序テーブル(plg_sort_product) に未登録商品があった場合は追加する
    private function setNewProduct()
    {
        // [dtb_product]テーブルにある商品で、[dtb_sort_product]テーブルに設定されていないレコードを探して登録する
        // ソート情報(sort_no)はとりあえずとして商品IDをつけておく
        return $this->SortProductRepository->insertSortProductNoExist();
    }

    // 順序テーブル(plg_sort_product) に存在しない商品(削除された商品)があった場合は削除する
    private function deleteProduct()
    {
        // [dtb_product]テーブルにない商品商品で、[dtb_sort_product]テーブルに設定されているレコードを探して削除する
        // 削除されてしまった商品を想定
        return $this->SortProductRepository->deleteSortProductNoExist();
    }

    // 順序テーブル(plg_sort_product) で ランク情報 がnullのレコードがあった場合は値を設定する
    private function setNewRank()
    {
        // [plg_sort_product]テーブルで[sort_no]がnullのレコードを探して、値を設定する
        // 設定する値はとりあえずとして商品IDをつけておく
        return $this->SortProductRepository->setSortNoWithNull();
    }

    // ランクが重複している or ランクがnull の レコードがあった場合、ランクを振り直す
    private function renewRank()
    {
        if ($this->isDupliRank() || $this->isNullRank()) {
            // ランクが重複しているレコードがある場合: ランクを振り直す
            $this->renewRankCore();
        }
        return true;
    }
    // ランクを振り直す
    private function renewRankCore()
    {
        // sort_noを1から振り直す
        // 振り直す順番は、sort_noのASC順で振り直す
        $result = $this->SortProductRepository->renewSortNo();

        return $result;
    }
    // ランクが重複しているレコードが[ある:true / ない:false]
    private function isDupliRank()
    {
        // 重複しているソート情報(sort_no)一覧を取得する
        $sortNoArray = $this->SortProductRepository->countDupliSortNo();

        if (count($sortNoArray) > 0) {
            // ランクが重複しているレコードがある場合: true
            return true;
        }
        return false;
    }
    // ランクがnullのレコードが[ある:true / ない:false]
    private function isNullRank()
    {
        // ランクがnullのレコード数を返す
        $nullCount = $this->SortProductRepository->countNullSortNo();

        if ($nullCount > 0) {
            // ランクがnullのレコードがある場合: true
            return true;
        }
        return false;
    }


    // 並び替え画面向けの商品一覧(フロントの商品一覧とほぼ同じもの)を取得
    // ProductRepository::getQueryBuilderBySearchData()とほぼ同じだが、[公開/非公開/廃止]を無視するところが異なる
    // ・商品の[公開/非公開/廃止]は無視
    // ・カテゴリー指定がある場合はカテゴリーで絞り込む
    // ・ソート順は [おすすめ順]
    public function getQbSortProduct($categoryId)
    {
        // 初期化 検索項目
        $searchData= array();

        // category_idの指定  Categoryエンティティを渡す
        if (!empty($categoryId)) {
            $Category = $this->container->get(\Eccube\Repository\CategoryRepository::class)->findOneBy(array('id' => $categoryId));
            $searchData['category_id'] = $Category;
        }

        // 並び替え画面向けの商品一覧(フロントの商品一覧とほぼ同じもの)を取得
        $qb = $this->container->get(\Plugin\SortProduct4Plus\Repository\SortProductRepository::class)->getQbSortProduct($searchData);

        return $qb;
    }

    /*
     * [順位 <=> sort_no]処理 ここから
     *
     * [!!! 順位はsort_noと逆順になるため注意 !!!]
     * ・sort_noの値が大きいものほど順位が上(順位は [1]が一番順位が上になる)
     *
     */

    // ProductPlusを順位指定で1件取得する
    private function getProductPlusByRank($categoryId, $rank)
    {
        // 並び替え画面向けの商品一覧(フロントの商品一覧とほぼ同じもの)を取得
        $qb = $this->getQbSortProduct($categoryId);

        // 順位指定で1件取得する (DB依存をなくすためpaginatorを利用する)
        $page_no    = $rank;
        $page_count = 1;
        $paginator = $this->container->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $page_count
        );
        foreach ($pagination as $SortProduct) {
            return $SortProduct;
        }
        return null;
    }
    // product_idを順位指定で1件取得する
    private function getProductIdByRank($categoryId, $rank)
    {
        // 順位指定で1件取得する
        $result = $this->getProductPlusByRank($categoryId, $rank);

        return (isset($result['product_id'])) ? $result['product_id'] : null;
    }


    /*
     * [順位 <=> sort_no]処理 ここまで
     */

    /**
     * 順位変更 処理 (sort_noは数字が大きいほど順位が上)
     *
     * @param $fromProductId
     * @param $toSortNo       : ajax方式: 指定する、 順番指定方式: null
     * @param $toRank         : ajax方式: null   、 順番指定方式: 指定する
     * @param $categoryId     : ajax方式: null   、 順番指定方式: 指定する
     * @return bool
     */
    public function changeRank($fromProductId, $toSortNo, $toRank, $categoryId)
    {
        log_info('[SortProduct] 順位変更処理 [開始]  fromProductId:[' . $fromProductId . '] toSortNo:[' . $toSortNo . '] toRank:[' . $toRank . '] categoryId:[' . $categoryId . ']');


        // ---------- ----------
        // 整地前の処理
        //

        // 移動先ProductIdの取得 (整地前に確保する)
        if ($toSortNo !== null) {
            // ajax方式の場合
            $toProductId = $this->SortProductRepository->getProductIdBySortNo($toSortNo);

        } elseif ($toRank !== null) {
            // 順番指定方式の場合
            $toProductId = $this->getProductIdByRank($categoryId, $toRank);

        } else {
            // おかしい場合
            $toProductId = null;
        }
        if ($toProductId == null) {
            return true;
        }


        // ---------- ----------
        // 整地
        //

        // 順序テーブル(plg_sort_product)を整地する
        $this->refineSortProductAdmin();


        // ---------- ----------
        // 整地後の処理
        //

        // 異動先sort_no の取得 (整地後に取得する)
        $toSortNo   = $this->SortProductRepository->getSortNoByProductId($toProductId);

        // 異動先sort_no の取得 (整地後に取得する)
        $fromSortNo = $this->SortProductRepository->getSortNoByProductId($fromProductId);

        // 順位変更 処理 (sort_noは数字が大きいほど順位が上)
        if ($fromSortNo > $toSortNo) {
            // 順位を下げる場合 (sort_noを小さい数値へ移動する処理)
            $this->SortProductRepository->toSmallSortNo($fromProductId, $fromSortNo, $toSortNo);

        } elseif ($fromSortNo < $toSortNo) {
            // 順位を上げる場合 (sort_noを大きい数値へ移動する処理)
            $this->SortProductRepository->toBigSortNo($fromProductId, $fromSortNo, $toSortNo);

        } else {
            // 順位移動がない場合: なにもしない
        }


        return true;
    }
}
