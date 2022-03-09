<?php
namespace Plugin\SortProduct4Plus;

use Eccube\Plugin\AbstractPluginManager;
use Eccube\Entity\Master\ProductListOrderBy;
use Eccube\Repository\Master\ProductListOrderByRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;


class PluginManager extends AbstractPluginManager
{

    // インストール時に、指定の処理を実行します
    public function install(array $meta = null, ContainerInterface $container)
    {
    }

    // アンインストール時にマイグレーションの「down」メソッドを実行します
    public function uninstall(array $meta = null, ContainerInterface $container)
    {
    }

    // プラグイン有効時に、マイグレーションの「up」メソッドを実行します
    public function enable(array $meta = null, ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        // 既存テーブルへのデータ追加するもの

        // [mtb_product_list_order_by]テーブルに[おすすめ順]を追加する
        // [おすすめ順]のrankは最上位とする
        // すでに追加済みの場合は登録しない
        $targetRepository = $container->get(ProductListOrderByRepository::class);
        $targetName = 'おすすめ順';
        $targetRecord = $targetRepository->findOneBy(array('name' => $targetName));
        if(!$targetRecord) {  // [おすすめ順]が未追加の場合に追加する
            //$debugMessage[]='['.$targetName.'] を追加します';
            $records_ProductListOrderBy = $targetRepository->findAll();

            // [おすすめ順]のrankを最上位[0]に入れ込むため、既存のrankを+1ずつ増やして 後ろへずらす
            // IDの最大値もゲットする
            $maxId = 0;
            /** @var ProductListOrderBy $record_ProductListOrderBy */
            foreach ($records_ProductListOrderBy as $record_ProductListOrderBy) {
                // 最大IDの調査
                $id = $record_ProductListOrderBy->getId();
                if ($id > $maxId) {
                    $maxId = $id;
                }
                // rankを+1 増やしてずらす
                $rank = $record_ProductListOrderBy->getSortNo();
                $record_ProductListOrderBy->setSortNo($rank + 1);
                $entityManager->persist($record_ProductListOrderBy);
            }

            // テーブルに[おすすめ順]を追加する
            $entity = new ProductListOrderBy;
            $entity->setID($maxId + 1);
            $entity->setName($targetName);
            $entity->setSortNo(0);
            $entityManager->persist($entity);
            // 保存
            $entityManager->flush();
        } else {
        }
    }


    /**
     * プラグイン無効時に、指定の処理 ( ファイルの削除など ) を実行します
     *
     * @param null $meta
     * @param ContainerInterface $container
     */
    public function disable(array $meta = null, ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        // テーブルからデータ削除（テーブルの削除は無しのもの）
        // [mtb_product_list_order_by]テーブルから[おすすめ順]を削除する
        // [おすすめ順]のrankは最上位に設定されていなくても対応可能にする
        //    -> [おすすめ順]よりrankが下のものはrankを-1する
        // すでに削除済みの場合は削除しない
        $targetRepository = $container->get(ProductListOrderByRepository::class);
        $targetName = 'おすすめ順';
        $targetRecord = $targetRepository->findOneBy(array('name'=>$targetName));
        if($targetRecord) {  // [おすすめ順]が登録されている場合のみ削除する（すでに削除済みの場合は削除しない）
            // 必要なデータの収集
            $targetRank = $targetRecord->getSortNo();  // [おすすめ順]のrankの調査
            $records_ProductListOrderBy = $targetRepository->findAll();  // 全レコード取得
            // rankの修正
            /** @var ProductListOrderBy $record_ProductListOrderBy */
            foreach ($records_ProductListOrderBy as $record_ProductListOrderBy) {
                $rank = $record_ProductListOrderBy->getSortNo();
                if( $rank > $targetRank ){
                    $record_ProductListOrderBy->setSortNo($rank - 1);
                    $entityManager->persist($record_ProductListOrderBy);
                }
            }
            $entityManager->remove($targetRecord);  // [おすすめ順]のデータ削除
            $entityManager->flush();
        } else {
        }
    }

    // プラグインアップデート時に、指定の処理を実行します
    public function update(array $meta = null, ContainerInterface $container)
    {
    }


}
