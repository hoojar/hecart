<?php
/**
 * 网站日志处理
 */
class Log extends wcore_object
{
	private $filename;

	public function __construct($filename)
	{
		$this->filename = $filename;
	}

	/**
	 * 记录PHP运行日志
	 *
	 * @param string $message
	 */
	public function phplog($message)
	{
		$handle = fopen(DIR_ROOT . "/logs/{$this->filename}", 'a+');
		if ($handle)
		{
			fwrite($handle, date('Y-m-d H:i:s') . ' - ' . $message . "\n");
			fclose($handle);
		}
	}

	/**
	 * 记录支付日志
	 *
	 * @param string $osn      订单编号
	 * @param string $pay_name 支付名称
	 * @param array  $data     支付数据组
	 */
	public function payment($osn, $pay_name, $data = array())
	{
		$pay_status   = isset($data['pay_status']) ? $data['pay_status'] : '';
		$trade_status = isset($data['trade_status']) ? $data['trade_status'] : '';
		$trade_total  = isset($data['trade_total']) ? $data['trade_total'] : '';
		$order_total  = isset($data['order_total']) ? $data['order_total'] : '';

		$sql = 'INSERT INTO ' . DB_PREFIX . 'order_pay (osn, pay_name, pay_status, trade_status, trade_total, order_total, date_added)';
		$sql .= " VALUES ('{$osn}', '{$pay_name}', '{$pay_status}', '{$trade_status}', '{$trade_total}', '{$order_total}', NOW())";
		$this->mdb()->query($sql);
	}

	/**
	 * 记录自定义文件日志
	 *
	 * @param string $file    日志文件名
	 * @param string $message 日志内容
	 */
	public function write($file, $message)
	{
		$now_time = date('Y-m-d H:i:s');//当前系统时间
		$log_file = DIR_ROOT . "/logs/{$file}";//日志文件路径
		@file_put_contents($log_file, "{$now_time} - {$message}\n", FILE_APPEND);
	}
}
?>