<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/encryption.php
 * 简述: 专门用于加密与解密处理
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: encryption.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
final class wcore_encryption
{
	/**
	 * @var string 加密用的密钥匙
	 */
	private $_key = '';

	/**
	 * @var bool 是否以base64展示与存储加密内容
	 */
	private $_rb64 = true;

	/**
	 * @var string 当前使用的加密类型
	 */
	private $_type = '3des';

	/**
	 * @var array 加密类型，采用哪种加密码方式
	 */
	private $_types = array(
		'rc2'      => MCRYPT_RC2,
		'des'      => MCRYPT_DES,
		'3des'     => MCRYPT_3DES,
		'crypt'    => MCRYPT_CRYPT,
		'blowfish' => MCRYPT_BLOWFISH,
		'rijndael' => MCRYPT_RIJNDAEL_256,
	);

	/**
	 * wcore_encryption constructor.
	 *
	 * @param string $key  加密钥匙
	 * @param string $type 加密类型
	 * @param bool   $rb64 是否以base64展示与存储加密内容
	 */
	public function __construct($key, $type = '3des', $rb64 = true)
	{
		$this->change_key($key);    //更改加密钥匙
		$this->change_type($type);  //更改加密类型
		$this->change_rb64($rb64);  //更改是否以base64展示与存储加密内容
	}

	/**
	 * 更改加密钥匙
	 *
	 * @param string $key 加密钥匙
	 */
	public function change_key($key)
	{
		$this->_key = $key;
	}

	/**
	 * 更改是否以base64展示与存储加密内容
	 *
	 * @param bool $rb64 是否以base64展示与存储加密内容
	 */
	public function change_rb64($rb64 = true)
	{
		$this->_rb64 = (bool)$rb64;
	}

	/**
	 * 更改加密类型
	 *
	 * @param string $type 加密类型
	 */
	public function change_type($type)
	{
		$this->_type = isset($this->_types[$type]) ? $this->_types[$type] : exit("Not have {$type} encryption type");
	}

	/**
	 * 加密数据
	 *
	 * @param string $value 待加密内容
	 * @return string 已加密内容
	 */
	public function encrypt($value)
	{
		$value = mcrypt_encrypt($this->_type, $this->_key, trim($value), MCRYPT_MODE_ECB);
		if ($this->_rb64)
		{
			$value = base64_encode($value);
		}

		return $value;
	}

	/**
	 * 解密数据
	 *
	 * @param string $value 待解密内容
	 * @return string 已解密内容
	 */
	public function decrypt($value)
	{
		if ($this->_rb64)
		{
			$value = base64_decode($value);
		}

		return trim(mcrypt_decrypt($this->_type, $this->_key, $value, MCRYPT_MODE_ECB));
	}
}
?>