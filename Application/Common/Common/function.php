<?php 


  /**
   * 获取配置项
   */
  function CC($key){
    $m_setting = M('Setting');

    $row = $m_setting
    	 ->field('value')
    	 ->where(['key'=>$key])
    	 ->find();
    if ($row) {
    	return $row['value'];
    }else{
    	return null;
    }
  	
  }
?>