<?php

namespace app\admin\controller\brand;

use app\admin\model\special\Grade as GradeModel;
use service\FormBuilder as Form;
use service\JsonService as Json;
use service\UtilService as Util;
use think\Controller;
use think\Request;
use think\Url;

class Brand extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->assign('grade', \app\admin\model\brand\Brand::getAll());
        return $this->fetch();
    }

    public function get_grade_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['cid', ''],
            ['name', ''],
        ]);
        return Json::successlayui(\app\admin\model\brand\Brand::getAllList($where));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create($id = 0)
    {
        if ($id) $grade = \app\admin\model\brand\Brand::get($id);
        $form = Form::create(Url::build('save', ['id' => $id]), [
            Form::input('name', '品牌名称', isset($grade) ? $grade->name : ''),
            Form::upload('img', '品牌图片','upload'),
            Form::number('sort', '排序', isset($grade) ? $grade->sort : 0),
        ]);
        $form->setMethod('post')->setTitle($id ? '修改分类' : '添加分类')->setSuccessScript('parent.$(".J_iframe:visible")[0].contentWindow.location.reload();');
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     *Excle导出
     */
    public function excle(){
        $data = db("brand")->select();
//        print_r($data);
//        die();
        $PHPExcel = new \PHPExcel();
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle("demo"); //给当前活动sheet设置名称
        $PHPSheet->setCellValue("A1","ID")->setCellValue("B1","品牌名称")->setCellValue("C1","排序");//表格数据
        for ($i=2;$i<=count($data)+1;$i++){
            $PHPSheet->setCellValue("A".$i,$data[$i-2]['id'])->setCellValue("B".$i,$data[$i-2]['name'])->setCellValue("C".$i,$data[$i-2]['sort']);//表格数据
        }
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment;filename="123.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        $objWriter->save('php://output'); //文件通过浏览器下载
    }

    /**
     *文件上传
     */
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('img');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move('./uploads');
            if($info){
                $img = './uploads/'.$info->getSaveName();
                $image = \think\Image::open($img);
                // 给原图左上角添加水印并保存water_image.png
                $image->text('online edu',ROOT_PATH."public/ARIALNB.TTF",50,'#ffffff')->save($img);
                session('img',$img);
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
    }


    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save($id = 0)
    {
        $post = Util::postMore([
            ['name', ''],
            ['sort', 0],
            ['img','']
        ]);
        if (!$post['name']) return Json::fail('请输入年级名称');
        if ($id) {
            \app\admin\model\brand\Brand::update($post, ['id' => $id]);
            return Json::successful('修改成功');
        } else {
            $post['add_time'] = time();
            $post['img'] = session('img');
             if (\app\admin\model\brand\Brand::set($post))
                return Json::successful('添加成功');
            else
                return Json::fail('添加失败');
        }
    }

    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_value($field = '', $id = '', $value = '')
    {
        $field == '' || $id == '' || $value == '' && Json::fail('缺少参数');
        if (\app\admin\model\brand\Brand::where(['id' => $id])->update([$field => $value]))
            return Json::successful('保存成功');
        else
            return Json::fail('保存失败');
    }
    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id = 0)
    {
        if (!$id) return Json::fail('缺少参数');
        if (\app\admin\model\brand\Brand::del($id))
            return Json::successful('删除成功');
        else
            return Json::fail('删除失败');
    }
}
