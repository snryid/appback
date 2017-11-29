<?php
namespace app\admin\controller;
use QL\QueryList;
class Note extends Common
{

    public function noteList(){
        //获取前缀
        $prefix=config('database.prefix');
        $modelName=$prefix.'model';
        $noteRes=db('note')->field('n.id,n.note_name,n.model_id,n.output_encoding,n.input_encoding,n.addtime,n.lasttime,m.model_name')->alias('n')->join("$modelName m",'n.model_id=m.id')->paginate(4);
        $this->assign([
            'noteRes'=>$noteRes,
            ]);
        return view('list');
    }
	

    public function addListRules(){
        if(request()->isPost()){
            $_data=input('post.');
            $data['note_name']=$_data['note_name'];
            $data['model_id']=$_data['model_id'];
            $data['output_encoding']=$_data['output_encoding'];
            $data['input_encoding']=$_data['input_encoding'];
            $data['list_rules']=array(
                'list_url'=>$_data['list_url'],
                'start_page'=>$_data['start_page'],
                'end_page'=>$_data['end_page'],
                'step'=>$_data['step'],
                'range'=>$_data['range'],
                'list_rules'=>array(
                    'url'=>$_data['url'],
                    'litpic'=>$_data['litpic'],
                    ),
                );
            //列表页面采集规则
            $data['list_rules']=json_encode($data['list_rules']);
            $data['addtime']=time();
            $id=db('note')->insertGetId($data);//新添加数据并且获取到id主键
            if($id){
                $notes=db('note')->field('model_id')->find($id);
                $modelId=$notes['model_id'];
                $this->redirect('additemrules', ['model_id' => $modelId,'note_id'=>$id]);
            }else{
                $this->error('添加节点失败！');
            }
            // dump($data);die;
            // dump(input('post.')); die;
        }
        $modelRes=db('model')->select();
        $this->assign([
            'modelRes'=>$modelRes,
            ]);
        return view();
    }
    
    public function additemrules(){
        $noteId=input('note_id');
        if(request()->isPost()){
            $data=input('post.');
            $rules=array();
            if($data){
                foreach ($data as $k => $v) {
                        $rules[$k][0]=$v[0];
                        $rules[$k][1]=$v[1];
                        $rules[$k][2]=$v[2];
                        array_values($rules[$k]);
                    
                }
            }
            $rules=json_encode($rules);
            $save=db('note')->where(array('id'=>$noteId))->update(['item_rules'=>$rules]);
            if($save){
                $this->success('添加节点成功！','noteList');
            }else{
                $this->error('添加节点失败');
            }
            return;
        }
        //自定义模型字段
        $modelId=input('model_id');
        $modelFieldRes=db('model_fields')->field('field_cname,field_ename')->where(array('model_id'=>$modelId))->select();
        $this->assign([
            'modelFieldRes'=>$modelFieldRes,
            ]);
        return view();
    }

    //展示采集界面
    public function showCaiji($id){
        //获取栏目
        $_cateRes=db('cate')->select();
        $cateRes=model('cate')->catetree($_cateRes);
        $notes=db('note')->field('model_id,note_name')->find($id);
        $modelId=$notes['model_id'];
        $noteName=$notes['note_name'];
        $this->assign([
            'id'=>$id,
            'cateRes'=>$cateRes,
            'modelId'=>$modelId,
            'noteName'=>$noteName,
            ]);
        return view();
    }
    //执行采集
    public function doCaiji($id){
        $notes=db('note')->find($id);
        //采集参数
        //输出编码
        $outputEncoding=$notes['output_encoding'];
        //输入编码
        $inputEncoding=$notes['input_encoding'];
        //列表采集配置
        $listRules=$notes['list_rules'];
        //json转换为数组
        $listRules=json_decode($listRules,true);
        //内容页采集规则
        $itemRules=$notes['item_rules'];
        //json转换数组
        $itemRules=json_decode($itemRules,true);
        //采集列表网址
        $listUrl=$listRules['list_url'];
        //采集开始页码
        $startPage=$listRules['start_page'];
        //采集结束页码
        $endPage=$listRules['end_page'];
        //页码步长
        $step=$listRules['step'];
        //采集范围
        $range=$listRules['range'];
        //采集规则
        $listCaijiRules=$listRules['list_rules'];
        // dump($listCaijiRules); die;
        //动态读取列表采集规则
        $listCaijiRules=array(
            'url'=>array($listCaijiRules['url'],'href'),
            'litpic'=>array($listCaijiRules['litpic'],'src'),
            );
        // dump($listRules); die;
        $_listUrl=[];//转换为实际页码的列表网址
        //处理采集列表网址
        for ($i=$startPage; $i <= $endPage; $i=$i+$step) { 
            $_listUrl[]=str_replace('(*)', $i, $listUrl);
        }
        //循环采集数据
        $_data=[];
        foreach ($_listUrl as $k => $url) {
            $_data[] = QueryList::Query($url,$listCaijiRules,$range,$outputEncoding,$inputEncoding)->data;
        }
        //数组重构,列表采集数据结果集
        $dataList=[];
        foreach ($_data as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $dataList[]=$v1;
            }
        }
        // dump($data); die;
        //内容数据采集结果集
        $_dataItem=[];
        foreach ($dataList as $k => $v) {
            $_dataItem[] = QueryList::Query($v['url'],$itemRules,'',$outputEncoding,$inputEncoding)->data;
            $_dataItem[$k][0]['url']=$v['url'];//手动添加url到数组，目的是写入临时表中
            $_dataItem[$k][0]['litpic']=$v['litpic'];//列表采集的缩略图
        }
        // dump($_dataItem); die;
        //数组重构
        $dataItem=[];
        foreach ($_dataItem as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $dataItem[]=$v1;  
            }
        }

        foreach ($dataItem as $k => $v) {
            $data['nid']=$id;
            $data['title']=$v['title'];
            //防止重复采集
            $reData=db('html')->where(array('title'=>$data['title']))->find();
            if($reData){
                continue;
            }
            $data['url']=$v['url'];
            $data['addtime']=time();
            $data['result']=json_encode($v);
            db('html')->insert($data);
        }
        //节点最后采集时间
        db('note')->where(array('id'=>$id))->update(['lasttime'=>time()]);
        echo 1;//采集完成

    }

    //数据导出操作
    public function exportdata(){
        $noteId=input('id');//当前节点id
        $cateId=input('cate_id');//要导出数据所属栏目
        $cate=db('cate')->field('model_id')->find($cateId);
        $modelId=$cate['model_id'];//动态获取模型id
        $model=db('model')->field('table_name')->find($modelId);
        $tableName=$model['table_name'];
        $data=db('html')->field('id,export,result')->where(array('export'=>0,'nid'=>$noteId))->select();//要导出的数据
        // dump($data); die;
        $arr=array('title','keywords','description','writer','source','content','litpic','url');
        $_archives=[];//主表元素数组
        $_addTable=[];//附加表元素数组
        $i=0;
        foreach ($data as $k => $v) {
            $_data=json_decode($v['result'],true);
            foreach ($_data as $k1 => $v1) {
                if(in_array($k1, $arr)){
                    $_archives[$k1]=$v1;
                    if($k1=='url'){
                        unset($_archives[$k1]);
                    }
                }else{
                     $_addTable[$k1]=$v1;
                }
            }
           $_archives['cate_id']=$cateId;
           $_archives['model_id']=$modelId;
           $addId=db('archives')->insertGetId($_archives);
           $_addTable['aid']=$addId;
           db($tableName)->insert($_addTable);
           db('html')->where(array('id'=>$v['id']))->update(['export'=>1]);
           //批量导出
           $i++;
           if(($i%6)==0){
            sleep(3);
           }
        }
        echo 1;//数据导出完成
    }

    //编辑节点信息
    public function edit($id){
        if(request()->isPost()){
            $data=input('post.');
            $base=[];
            $base['id']=$data['id'];
            $base['note_name']=$data['note_name'];
            $base['model_id']=$data['model_id'];
            $base['output_encoding']=$data['output_encoding'];
            $base['input_encoding']=$data['input_encoding'];
            $base['list_rules']['list_url']=$data['list_url'];
            $base['list_rules']['start_page']=$data['start_page'];
            $base['list_rules']['end_page']=$data['end_page'];
            $base['list_rules']['step']=$data['step'];
            $base['list_rules']['range']=$data['range'];
            $base['list_rules']['list_rules']['url']=$data['url'];
            $base['list_rules']['list_rules']['litpic']=$data['litpic'];
            $base['list_rules']=json_encode($base['list_rules']);
            $arr=array('note_name','model_id','output_encoding','input_encoding','list_url','start_page','end_page','step','range','url','litpic','id');
            foreach ($data as $k => $v) {
                if(in_array($k, $arr)){
                    unset($data[$k]);
                }
            }
            $base['item_rules']=json_encode($data);
            $save=db('note')->update($base);
            if($save!==false){
                $this->success('修改节点成功！','noteList');
            }else{
                $this->error('修改节点失败！');
            }
            return;
        }
        $notes=db('note')->find($id);
        $listRules=json_decode($notes['list_rules'],true);
        $itemRules=json_decode($notes['item_rules'],true);
        $modelId=$notes['model_id'];
        //根据模型id获取模型名称
        $models=db('model')->field('model_name')->find($modelId);
        $modelName=$models['model_name'];
        $modelRes=db('model')->select();
         //自定义模型字段
        $modelFieldRes=db('model_fields')->field('field_cname,field_ename')->where(array('model_id'=>$modelId))->select();
        $this->assign([
            'modelRes'=>$modelRes,
            'modelFieldRes'=>$modelFieldRes,
            'notes'=>$notes,
            'modelId'=>$modelId,
            'modelName'=>$modelName,
            'listRules'=>$listRules,
            'itemRules'=>$itemRules,
            'id'=>$id,//节点id
            ]);
        return view();
    }

    //删除节点
    public function del($id){
        $del=db('note')->delete($id);
        if($del){
           db('html')->where(array('nid'=>$id))->delete();
           $this->success('删除节点成功！','noteList'); 
        }else{
            $this->error('删除节点失败！');
        }
    }

    


   
}
