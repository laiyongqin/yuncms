<?php
// +----------------------------------------------------------------------
// | YunCMS
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://www.yunalading.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: chenqianhao <68527761@qq.com>
// +----------------------------------------------------------------------


namespace app\home\controller;
use app\admin\model\ContentModel;
use app\admin\model\ModelAttrModel;
use app\home\controller\HomeBaseController;
use app\home\model\CategoryModel;


/**
 * Class Lists
 * @package app\admin\controller
 */

class Lists extends HomeBaseController {
    /**
     * @return \think\response\View
     */
    public function index() {
        //栏目列表
        $category = get_cate_list(['2','3','4','5']);
        $this->assign('category',$category);
        //友情链接列表
        $link = get_link_list();
        $this->assign('link',$link);
        $template = '';
        if(isset($this->param['category_id']) && $this->param['category_id']>0){
            $categoryModel = new CategoryModel();
            $cid = intval($this->param['category_id']);
            $category_one = $categoryModel->get($cid);
            $this->assign('category_one',$category_one);
            $template = $category_one['list_template'];
            //模型属性
            $model_attr = ModelAttrModel::all(array("model_id" => $category_one['model_id']));
            $attrs = array();
            if (!empty($model_attr)) {
                foreach ($model_attr as $mm) {
                    $mm['values'] = explode(',',$mm['pro_value']);
                    $attrs[$mm['pro_key']] = $mm;
                }
            }
            $this->assign('attrs', $attrs);
            unset($this->param['category_id']);
            //dd($this->param);
            //根据栏目获取所有文章
            $articleModel = new ContentModel();
            //unset($where);
            //有属性值选择提交过来
            if(isset($this->param) && !empty($this->param)){
                $where['a.category_id'] = $cid;
                foreach($this->param as $k=>$pp){
                    //$where['ap.value'] = $pp;
                }
                $list = $articleModel->alias('a')->where($where)->group('a.content_id')->field('a.content_id,a.category_id,a.cover,c.model_id,p.model_properties_id,p.pro_name,ap.value')->join('__CATEGORY__ c','a.category_id = c.category_id')->join('__MODEL_PROPERTIES__ p','c.model_id = p.model_id')->join('__ARTICLE_PROPERTIES__ ap','a.content_id = ap.article_id')->paginate();
            }else{
                $where['category_id'] = $cid;
                $list = $articleModel->where($where)->field('content_id,category_id,category_id,cover')->paginate();
            }
            $page = $list->render();
            $this->assign('list', $list);
            $this->assign('page', $page);
            dd($list);
        }else{
            //所有栏目
            $this->error('请先选择栏目分类!');
            $cid = 0;
            $articleModel = new ContentModel();
            $list = $articleModel->paginate();
            $page = $list->render();
            $this->assign('list', $list);
            $this->assign('page', $page);
        }
        //渲染模板输出
        return $this->show($template);
    }


}