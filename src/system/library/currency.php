<?php
/**
 * 网站货币处理类库
 */
class Currency extends modules_mem
{
	/**
	 * @var string 货币代号
	 */
	private $code;

	/**
	 * @var Config 配置对象
	 */
	protected $config;

	/**
	 * @var Request 请求对象
	 */
	protected $request;

	/**
	 * @var Language 语言对象
	 */
	protected $language;

	/**
	 * @var array 网站拥有货币
	 */
	public $currencies = array();

	public function __construct($registry)
	{
		parent::__construct();
		$this->config     = $registry->get('config');
		$this->request    = $registry->get('request');
		$this->language   = $registry->get('language');
		$this->currencies = $this->hash_sql('SELECT * FROM ' . DB_PREFIX . 'currency WHERE status = 1', 'code');

		if (isset($this->request->get['currency']) && isset($this->currencies[$this->request->get['currency']]))
		{
			$this->set($this->request->get['currency']);
		}
		elseif (isset($this->request->cookie['currency']) && isset($this->currencies[$this->request->cookie['currency']]))
		{
			$this->set($this->request->cookie['currency']);
		}
		else
		{
			$this->set($this->config->get('config_currency'));
		}
	}

	/**
	 * 根据货币代号判断系统是否拥有此种货币
	 *
	 * @param string $currency 货币代号
	 * @return bool
	 */
	public function has($currency)
	{
		return isset($this->currencies[$currency]);
	}

	/**
	 * 设置网站当前货币代号
	 *
	 * @param string $currency 货币
	 */
	public function set($currency)
	{
		$this->code = $currency;
		wcore_utils::set_cookie('currency', $currency, 365);
	}

	/**
	 * 格式化货币显示
	 *
	 * @param  float $number   金额
	 * @param string $currency 货币
	 * @param string $value    汇率
	 * @param bool   $format   是否格式化
	 * @return string
	 */
	public function format($number, $currency = '', $value = '', $format = true)
	{
		if ($currency && $this->has($currency))
		{
			$symbol_left   = $this->currencies[$currency]['symbol_left'];
			$symbol_right  = $this->currencies[$currency]['symbol_right'];
			$decimal_place = $this->currencies[$currency]['decimal_place'];
		}
		else
		{
			$symbol_left   = $this->currencies[$this->code]['symbol_left'];
			$symbol_right  = $this->currencies[$this->code]['symbol_right'];
			$decimal_place = $this->currencies[$this->code]['decimal_place'];
			$currency      = $this->code;
		}

		if (empty($value))
		{
			$value = $this->currencies[$currency]['value'];
		}
		$value = $value ? (float)$number * $value : $number;

		$string = '';
		if (($symbol_left) && ($format))
		{
			$string .= $symbol_left;
		}

		$decimal_point  = $format ? $this->language->get('decimal_point') : '.';
		$thousand_point = $format ? $this->language->get('thousand_point') : '';
		$string .= number_format(round($value, (int)$decimal_place), (int)$decimal_place, $decimal_point, $thousand_point);

		if (($symbol_right) && ($format))
		{
			$string .= $symbol_right;
		}

		return $string;
	}

	/**
	 * 货币转换
	 *
	 * @param float  $value 金额
	 * @param string $from  当前什么货币代号 (USD)
	 * @param string $to    要转换成什么货币代号(CNY)
	 * @return mixed
	 */
	public function convert($value, $from, $to)
	{
		$to   = !isset($this->currencies[$to]) ? 0 : $this->currencies[$to]['value'];
		$from = !isset($this->currencies[$from]) ? 0 : $this->currencies[$from]['value'];

		return $value * ($to / $from);
	}

	/**
	 * 获取当前货币代号
	 *
	 * @return string 货币代号
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * 获取货币编号
	 *
	 * @param string $currency 货币代号
	 * @return int
	 */
	public function getId($currency = '')
	{
		if (!$currency)
		{
			return $this->currencies[$this->code]['currency_id'];
		}
		elseif ($currency && isset($this->currencies[$currency]))
		{
			return $this->currencies[$currency]['currency_id'];
		}

		return 0;
	}

	/**
	 * 获取货币左侧符号
	 *
	 * @param string $currency 货币代号
	 * @return string
	 */
	public function getSymbolLeft($currency = '')
	{
		if (!$currency)
		{
			return $this->currencies[$this->code]['symbol_left'];
		}
		elseif ($currency && isset($this->currencies[$currency]))
		{
			return $this->currencies[$currency]['symbol_left'];
		}

		return '';
	}

	/**
	 * 获取货币右侧符号
	 *
	 * @param string $currency 货币代号
	 * @return string
	 */
	public function getSymbolRight($currency = '')
	{
		if (!$currency)
		{
			return $this->currencies[$this->code]['symbol_right'];
		}
		elseif ($currency && isset($this->currencies[$currency]))
		{
			return $this->currencies[$currency]['symbol_right'];
		}

		return '';
	}

	/**
	 * 获取货币精度到几位小数
	 *
	 * @param string $currency 货币代号
	 * @return int
	 */
	public function getDecimalPlace($currency = '')
	{
		if (!$currency)
		{
			return $this->currencies[$this->code]['decimal_place'];
		}
		elseif ($currency && isset($this->currencies[$currency]))
		{
			return $this->currencies[$currency]['decimal_place'];
		}

		return 0;
	}

	/**
	 * 获取货币汇率
	 *
	 * @param string $currency 货币代号
	 * @return int
	 */
	public function getValue($currency = '')
	{
		if (!$currency)
		{
			return $this->currencies[$this->code]['value'];
		}
		elseif ($currency && isset($this->currencies[$currency]))
		{
			return $this->currencies[$currency]['value'];
		}

		return 0;
	}
}
?>