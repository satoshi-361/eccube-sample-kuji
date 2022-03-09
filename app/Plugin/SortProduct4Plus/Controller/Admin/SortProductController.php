<?php


namespace Plugin\SortProduct4Plus\Controller\Admin;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Product;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\Master\PageMaxRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\ProductCategoryRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Util\EntityUtil;
use Knp\Component\Pager\Paginator;
use Plugin\SortProduct4Plus\Service\SortService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class SortProductController extends AbstractController
{

    /**
     * @var SortService
     */
    private $SortService;

    /**
     * SortProductController constructor.
     * @param SortService $SortService
     */
    public function __construct(SortService $SortService)
    {
        // ソート処理系のサービス
        $this->SortService = $SortService;
    }

    // 並び替えをrankで制御する
    // rankは大きい数字ほど優先順位が高い
    /**
     * @param Request $request
     * @param null $categoryId
     * @param Paginator $paginator
     * @return mixed
     * @Route("/%eccube_admin_route%/plugin/SortProduct4Plus", name="plugin_SortProduct")
     * @Route("/%eccube_admin_route%/plugin/SortProduct4Plus/config", name="sort_product4_admin_config")
     * @Route("/%eccube_admin_route%/plugin/SortProduct4Plus/{categoryId}", name="plugin_SortProduct_byCategory")
     * @Template("@SortProduct4Plus/admin/index.twig")
     */
    public function index(Request $request, $categoryId = null, Paginator $paginator)
    {
        // デバッグモードの取得
        // ・有効(true)の場合: 並び替え一覧にsort_noを表示する
        $onDebug = $this->container->getParameter('kernel.debug');

        // ソート処理系のサービス
        $SortService = $this->SortService;


        // ---------- ----------
        // 順番指定方式 の場合の 順序変更処理
        //

        // 順番指定方式の場合は POSTでくるので取得する
        $fromProductId = $this->getParamFromRequest($request, 'from_id');
        $toRank        = $this->getParamFromRequest($request, 'to_id');

        // 順番指定方式 の場合の 順序変更処理
        if ($fromProductId != null && $toRank != null) {
            // 順番指定方式 で指定してきた場合

            // 順位変更を実施
            $toSortNo = null;  // 順番指定方式の場合はnull
            $SortService->changeRank($fromProductId, $toSortNo, $toRank, $categoryId);
        }


        // ---------- ----------
        // 順序テーブル(plg_sort_product) の整地
        //
        // [!!! [順序指定方式]処理後に実施すること (画面に表示済みのsort_noと差異が出ると[順序指定方式]処理がおかしくなる可能性があるため) !!!]

        // 順序テーブル(plg_sort_product)を整地する
        $SortService->refineSortProductAdmin();


        // ---------- ----------
        // twig表示のための処理
        //

        // 並び替え画面向けの商品一覧(フロントの商品一覧とほぼ同じもの)を取得
        $qb = $SortService->getQbSortProduct($categoryId);
        // paginator
        // 　デフォルト値の取得
        $pageMaxis = $this->container->get(PageMaxRepository::class)->findAll();  // 表示件数指定リストのリスト作成
        $page_count_default = $this->eccubeConfig['eccube_default_page_count'];  // デフォルトの表示件数
        // 　指定値の取得 (取得できなければ、デフォルト値を使用する)
        $page_no = $request->get('page_no') ? $request->get('page_no') : 1;
        $page_count = $request->get('page_count') ? $request->get('page_count') : $page_count_default;
        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $page_count
        );

        // 最大ランク (順番指定方式で最大値を表示するために使用)
        $maxRank = $pagination->getTotalItemCount();


        return array(
            'this_page' => 'sort_product4_admin_config',
            'this_page_by' => 'plugin_SortProduct_byCategory',
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,  // 表示件数指定のリスト
            'page_no' => $page_no,  // ページ番号
            'page_count' => $page_count,  // デフォルトの表示件数
            'category_id' => $categoryId,
            'onDebug' => $onDebug,
            'maxRank' => $maxRank,
            );
    }
    // requestから指定パラメータの取得
    // ・文字で[null]と来た場合は本当のnullにする
    // ・数字判定を行う
    private function getParamFromRequest($request, $paramName)
    {
        $result = $request->get($paramName) ? $request->get($paramName) : null;

        // 文字で[null]と来た場合は本当のnullにする
        if ($result == 'null') {
            $result = null;
        }

        // バリデート　数字判定を行う
        if ($result === null || is_numeric($result) == false || $result <= 0) {
            $result = null;
        }

        return $result;
    }


    /**
     * ajax方式によるランク移動
     *
     * @param Request $request
     * @return Response
     * @Route("/%eccube_admin_route%/plugin/SortProduct4Plus/rank/move", name="plg_SortProduct_product_rank_move")
     */
    public function moveRank(Request $request, Paginator $paginator)
    {
        if ($request->isXmlHttpRequest() && $this->isTokenValid()) {

            // ---------- ----------
            // 順序入れ替え 処理

            // $requestデータから順番入れ替えに必要なデータを抽出する
            $result = $this->getSortDataByRequet($request);

            // 移動元ID (商品ID)
            $fromProductId = isset($result['from_id']) ? $result['from_id'] : null;
            // 移動先ID (sort_no)
            $toSortNo      = isset($result['to_id']) ? $result['to_id'] : null;

            // 順位変更
            $toRank     = null;  // ajax方式の場合はnull
            $categoryId = null;  // ajax方式の場合はnull
            $this->SortService->changeRank($fromProductId, $toSortNo, $toRank, $categoryId);



            // ---------- ----------
            // 戻り値 処理

            // フロントへ返す順序入れ替え後のsort_no一覧の生成
            // 　フロントで表示している最新の商品一覧の取得 (ajax方式ではページまたぎの移動はない)
            $categoryId = isset($result['category_id']) ? $result['category_id'] : null;
            $page_no    = isset($result['page_no']) ? $result['page_no'] : null;
            $page_count = isset($result['page_count']) ? $result['page_count'] : null;
            $qb = $this->SortService->getQbSortProduct($categoryId);
            $pagination = $paginator->paginate(
                $qb,
                $page_no,
                $page_count
            );
            // 　フロントへ返す順序入れ替え後のsort_no一覧の生成
            $sortNoArray = array();
            foreach($pagination as $productPlus){
                $id = isset($productPlus['product_id']) ? $productPlus['product_id'] : null;
                $sortNo = isset($productPlus['sort_no']) ? $productPlus['sort_no'] : null;
                if ($id !== null) {
                    // { product_id => sort_no, ... }
                    $sortNoArray[$id] = $sortNo;
                }
            }

            return new Response(json_encode($sortNoArray));
        }

        return new Response('Successful');
    }
    // $requestデータから順番入れ替えに必要なデータを抽出する
    private function getSortDataByRequet($request)
    {
        // POSTの全データをそのまま使用する
        return $request->request->all();
    }

}
