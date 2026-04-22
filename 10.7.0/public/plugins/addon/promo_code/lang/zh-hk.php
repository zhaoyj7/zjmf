<?php

return [
    'id_error' => 'ID錯誤',
    'param_error' => '參數錯誤',
    'success_message' => '請求成功',
    'fail_message' => '請求失敗',
    'create_success' => '創建成功',
    'create_fail' => '創建失敗',
    'delete_success' => '刪除成功',
    'delete_fail' => '刪除失敗',
    'update_success' => '修改成功',
    'update_fail' => '修改失敗',
    'cannot_repeat_opreate' => '不可重複操作',
    'promo_code_require' => '請填寫優惠碼',
    'promo_code_error' => '優惠碼只能為9位且包含大小寫字母數字',
    'promo_code_unique' => '優惠碼已存在',
    'promo_code_start_time_require' => '請填寫生效時間',
    'promo_code_start_time_date' => '生效時間錯誤',
    'promo_code_end_time_date' => '截止時間錯誤',
    'promo_code_end_time_gt' => '截止時間需要大於生效時間',
    'promo_code_max_times_require' => '請填寫最大使用次數',
    'promo_code_max_times_error' => '最大使用次數需為大於等於0的整數',
    'promo_code_notes_max' => '備註長度最大為1000字符',
    'promo_code_is_not_exist' => '優惠碼不存在',
    'promo_code_type_percent_value_error' => '折扣比例只能為大於0且小於等於100的數',
    'promo_code_type_fixed_amount_value_error' => '減免金額只能為大於0的數',
    'promo_code_type_replace_price_value_error' => '覆蓋金額只能為0以上的數',
    'promo_code_product_is_not_exist' => '商品不存在',
    'promo_code_type_percent_description' => '優惠碼{promo_code}應用至{target}, {value}% 百分比 折扣',
    'promo_code_type_fixed_amount_description' => '優惠碼{promo_code}應用至{target}, {value} 固定金額減免 折扣',
    'promo_code_type_replace_price_description' => '優惠碼{promo_code}應用至{target}, {value} 覆蓋價格 折扣',
    'promo_code_type_free_description' => '優惠碼{promo_code}應用至{target}, 免費 折扣',
    'promo_code_type_fixed_amount_not_support' => '優惠類型固定金額減免不支援開啟升降級，升降級循環，續費，續費循環',
    'promo_code_type_replace_price_not_support' => '優惠類型覆蓋價格不支援開啟升降級，升降級循環，續費，續費循環',
    'promo_code_type_free_not_support' => '優惠類型免費不支援開啟升降級循環，續費循環',
    'addon_promo_code_client_level_is_not_install' => '未安裝使用者等級插件，不支援選擇使用者未擁有指定使用者等級條件',
    'addon_promo_code_client_level_is_not_exist' => '使用者等級不存在',
    'addon_promo_code_flow_packet' => '流量包',
    'addon_promo_code_host' => '產品Host Id:{host_id}',

    'log_admin_create_promo_code' => '{admin}新增優惠碼:{promo_code}',
    'log_admin_update_promo_code' => '{admin}修改優惠碼{promo_code}:{description}',
    'log_admin_delete_promo_code' => '{admin}刪除優惠碼:{promo_code}',
    'log_admin_enable_promo_code' => '{admin}啟用優惠碼:{promo_code}',
    'log_admin_disable_promo_code' => '{admin}禁用優惠碼:{promo_code}',
    'promo_code_client_use_promo_code' => '{client}使用優惠碼:{promo_code},應用於訂單:{order_id}',


    # 優惠碼可用判斷
    'addon_promo_code_not_found' => '未找到優惠碼',
    'addon_promo_code_has_expired' => '該優惠碼已失效',
    'addon_promo_code_product_cannot_use' => '該優惠碼無法應用到該產品',
    'addon_promo_code_the_condition_cannot_use' => '尚未達到優惠碼使用條件',
    'addon_promo_code_upgrade_cannot_use' => '該優惠碼無法在升降級時使用',
    'addon_promo_code_renew_cannot_use' => '該優惠碼無法在續費產品時使用',
    'addon_promo_code_flow_packet_cannot_use' => '該優惠碼無法用於流量包訂單',
    'addon_promo_code_only_new_client' => '該優惠碼僅可用於無產品新用戶',
    'addon_promo_code_only_old_client' => '該優惠碼僅可用於賬戶內存在激活產品的用戶',
    'addon_promo_code_only_not_have_client_level_client' => '尚未達到優惠碼使用條件',
    'addon_promo_code_higher_cannot_use' => '應用優惠碼後價格高於原價格，無法應用',
    'addon_promo_code_on_demand_to_recurring_prepayment_cannot_use' => '此優惠碼無法在按需轉包年包月時使用',

    # 導航
    'nav_plugin_addon_promo_code' => '優惠碼',

    'auth_product_promo_code' => '優惠碼',
    'auth_product_promo_code_view' => '查看頁面',
    'auth_product_promo_code_create_promo_code' => '新增優惠碼',
    'auth_product_promo_code_delete_promo_code' => '刪除優惠碼',
    'auth_product_promo_code_deactivate_enable_promo_code' => '停/啟用優惠碼',
    'auth_product_promo_code_update_promo_code' => '編輯優惠碼',

    # 流量包相關
    'promo_code_flow_packet' => '流量包使用',
    'promo_code_flow_packet_desc' => '開啟後該優惠碼可用於流量包訂單',

    # 不與使用者等級同享
    'promo_code_exclude_with_client_level' => '不與使用者等級同享',
    'promo_code_exclude_with_client_level_desc' => '開啟後使用優惠碼時不與使用者等級折扣疊加',

];
