<?php 
	
	namespace Back\Controller;
	use Think\Controller;
	use Think\Upload;
	/**
	* 分类控制器
	*/
	class CategoryController extends Controller
	{
		/**
		 * 分类数据展示
		 * @return [type] [description]
		 */
		public function listAction()
		{
			$m_category = D('Category');
			$category_list = $m_category->getTree();
			// dump($category_list);
			// die;
			$this->assign('category_list',$category_list);
			$this->display();
		}		
		/**
		 * 新增分类动作
		 */
		public function addAction()
		{	
			if (IS_POST) {
				 $m_category = D('Category');
				 // 图片上传处理
					$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
					//设置文件上传类型
					$upload = new Upload();
					$upload->maxSize = 3145728 ;// 设置附件上传大小
					 // 设置文件上传目录
					$upload->rootPath = './Uploads/'; //上传文件根目录
					$upload->savePath =  MODULE_NAME.'/'.CONTROLLER_NAME.'/'; //上传文件相对于根目录的路径
					 // 上传文件 
					$info = $upload->uploadOne($_FILES['image']);
					if($info){
						// 上传ok，添加到post数据中
						$_POST['image'] = $info['rootpaht'].$info['savepath'].$info['savename'];
					}else{
						// 上传失败，将错误信息存储到error属性中；
						$m_category->error = $upload->getError();
					}
				 $data = $m_category->create();
				 if ($data) {
				 	$category_id = $m_category->add();
				 	if ($category_id) {
					 	//删除缓存
					 	S(['type'=>'File']);
					 	S('category_list_0',null);
					 	// 前台缓存
					 	S('category_nested_0',null)	;
					 	$this->redirect('Back/Category/list',[],0);
				 	}
				 }else{
				 	$this->error('添加失败<br>原因:'.$m_category->getError(),U('Back/Category/add'));
				 }
			}else{
				$m_category = D('Category');
				$category_list = $m_category->getTree();
				$this->assign('category_list',$category_list);
				$this->display();
			}
		}
		/**
		 * 分类删除动作
		 * @return [type] [description]
		 */
		public function deleteAction()
		{
			$del_list = I('post.selected');
			$m_category = M('Category');
			foreach ($del_list as $del_id) {
			 
				$del_res = $m_category->delete($del_id);
				if ($del_res !== false) {
					$data['parent_id'] = 1;
					$where['parent_id'] = $del_id;
					$m_category->where($where)->save($data);
					S(['type'=>'File']);
					S('category_list_0',null);
				}
			}
			$this->redirect('Back/Category/list',[],0);
		}

		public function updateAction()
		{
			if (IS_POST) {
				$m_category = M('Category');
				$data = $m_category->create();
				if ($data) {
					$res = $m_category->save();
					if ($res !== false) {
						// 更新缓存
						S(['type'=>'File']);
						S('category_list_0',null);
						$this->redirect('Back/Category/list',[],0);
					}
				}
			}else{
				$m_category = D('Category');
				$category = $m_category
						->where(['category_id'=>I('get.id')])
						->find();
				$category_list = $m_category->getTree();
					// dump($category);
					// die;
				$this->assign('category_list',$category_list);
				$this->assign('category',$category);
				$this->display();

			}
		}
	}
 ?>