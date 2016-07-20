<?php 
	namespace Back\Controller;

	use Think\Controller;

	/**
	* 后台系统配置控制器
	*/
	class SystemController extends Controller
	{
		/**
		 * 系统配置项展示
		 * @return [type] [description]
		 */
		public function listAction()			
		{
			// 设置项分组
			$m_setting_group = M('SettingGroup');
			$setting_group_list = $m_setting_group->select();
			$this->assign('setting_group_list',$setting_group_list);
			// 设置项
			$m_setting = M('Setting');
			$setting_list = $m_setting
						   ->alias('s')
						   ->field('s.*,st.type_title')
						   ->join('left join __SETTING_TYPE__ as st on s.type_id = st.setting_type_id')
						   ->order('s.group_id,s.sort_number')
						   ->select();
			//格式化数据
			$setting_list_format = [];
			foreach ($setting_list as $setting) {

				// 处理复杂项 checkbox和select
				switch ($setting['type_title']) {
					case 'select':
						$op_list = explode(',',$setting['option_list']);
						$op_list = array_map(function($v){
							return explode('-',$v);
						}, $op_list);
						// 添加分割后的数据到setting中
						$setting['select_list'] = $op_list;
						break;
					case 'checkbox':
						$op_list = explode(',',$setting['option_list']);
						$op_list = array_map(function($v){
							return explode('-',$v);
						}, $op_list);
						// 添加分割后的数据到setting中
						$setting['check_list'] = $op_list;
						//添加已选择的数据到setting中
						$checked_list = explode(',',$setting['value']);
						$setting['checked_list'] = $checked_list;
				}
				$setting_list_format[$setting['group_id']][] = $setting;
			}
			    // dump($setting_list_format);
			    // die;
		    $this->assign('setting_list_format',$setting_list_format);
		    
			$this->display();
		}

		/**
		 * 系统配置项全部更新
		 * @return [type] [description]
		 */
		public function updateAction()
		{
			// echo 'ok';
			$m_setting = M('Setting');
			// dump(I('post.setting'));
			$data_list = I('post.setting');
			foreach ($data_list as $key => $value) {
				$data['setting_id'] = $key;
				if (is_array($value)) {
					$value = implode(',',$value);
				} 
				$data['value'] = $value;
				$m_setting->save($data);
			}
			$this->redirect('Back/System/list',[],0);
		}
		/**
		 * 利用ajax更新某一个设置项
		 * @return [type] [description]
		 */
		public function updateOneAction()
		{
			$data['setting_id'] = I('post.setting_id');
			$data['value'] = I('post.value');
			$m_setting = M('Setting');
			$res = $m_setting->save($data);
			if ($res) {
				$this->ajaxReturn(['error'=>0]);
			}else{
				$this->ajaxReturn(['error'=>1]);
			}
		}
		
	}

 ?>