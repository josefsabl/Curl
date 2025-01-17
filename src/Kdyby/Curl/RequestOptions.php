<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Curl;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @property-write int $timeout
 * @property-write string $referer
 * @property-write string $userAgent
 * @property-write bool $followRedirects
 * @property-write int $maximumRedirects
 * @property-write bool $returnTransfer
 */
abstract class RequestOptions
{
    use Nette\SmartObject;

	/**#@+ verify host for certificates */
	const VERIFYHOST_NO = 0;
	const VERIFYHOST_COMMON = 1;
	const VERIFYHOST_MATCH = 2;
	/**#@- */


	/** @var array */
	public $options = array(
		'timeout' => 15,
		'followLocation' => FALSE, // curl is not passing cookies around
		'maxRedirs' => 10,
		'returnTransfer' => TRUE,
	);



	/**
	 * @param int $timeout
	 *
	 * @return RequestOptions
	 */
	public function setTimeout($timeout)
	{
		$this->options['timeout'] = $timeout;
		return $this;
	}



	/**
	 * @param string $referer
	 *
	 * @return RequestOptions
	 */
	public function setReferer($referer)
	{
		$this->options['referer'] = (string)$referer;
		return $this;
	}



	/**
	 * @param string $ua
	 *
	 * @return RequestOptions
	 */
	public function setUserAgent($ua)
	{
		$this->options['userAgent'] = $ua;
		return $this;
	}



	/**
	 * @param boolean $yes
	 *
	 * @return RequestOptions
	 */
	public function setFollowRedirects($yes = TRUE)
	{
		$this->options['followLocation'] = (bool)$yes;
		return $this;
	}



	/**
	 * @param int $count
	 *
	 * @return RequestOptions
	 */
	public function setMaximumRedirects($count)
	{
		$this->options['maxRedirs'] = $count;
		return $this;
	}



	/**
	 * @param boolean $yes
	 *
	 * @return RequestOptions
	 */
	public function setReturnTransfer($yes = TRUE)
	{
		$this->options['returnTransfer'] = (bool)$yes;
		return $this;
	}



	/**
         * @param integer $version
         * 
         * @return RequestOptions
         */
        public function setSslVersion($version)
        {
                $this->options['sslVersion'] = (int) $version;
                return $this;
        }



	/**
         * @param string $cipherList
         * 
         * @return RequestOptions
         */
        public function setSslCipherList($cipherList) 
        {
                $this->options['ssl_cipher_list'] = (string) $cipherList;
                return $this;
        }



	/**
	 * Sets if all certificates are trusted by default
	 *
	 * @param boolean $yes
	 *
	 * @return RequestOptions
	 */
	public function setCertificationVerify($yes = TRUE)
	{
		$this->options['ssl_verifyPeer'] = (bool)$yes;
		return $this;
	}



	/**
	 * Adds path to trusted certificate and unsets directory with certificates if set
	 * WARNING: Overwrites the last given certificate
	 *
	 * CURLOPT_SSL_VERIFYHOST:
	 *    0: Don’t check the common name (CN) attribute
	 *    1: Check that the common name attribute at least exists
	 *    2: Check that the common name exists and that it matches the host name of the server
	 *
	 * @param string $cert
	 * @param int $verifyHost
	 *
	 * @throws MissingCertificateException
	 * @throws InvalidArgumentException
	 * @return RequestOptions
	 */
	public function setTrustedCertificate($cert, $verifyHost = self::VERIFYHOST_MATCH)
	{
		if (!in_array($verifyHost, range(0, 2))) {
			throw new InvalidArgumentException("Verify host must be 0, 1 or 2");
		}

		if (!file_exists($cert)) {
			throw new MissingCertificateException('Certificate "' . $cert . ' is not readable.');
		}

		unset($this->options['caPath']);
		$this->setCertificationVerify();
		$this->options['ssl_verifyHost'] = $verifyHost; // 2=secure
		$this->options['caInfo'] = $cert;

		return $this;
	}



	/**
	 * Adds path to directory which contains trusted certificate and unsets single certificate if set
	 * WARNING: Overwrites the last one
	 *
	 * CURLOPT_SSL_VERIFYHOST:
	 *    0: Don’t check the common name (CN) attribute
	 *    1: Check that the common name attribute at least exists
	 *    2: Check that the common name exists and that it matches the host name of the server
	 *
	 * @param string $dir
	 * @param int $verifyHost
	 *
	 * @throws MissingCertificateException
	 * @throws InvalidArgumentException
	 * @return RequestOptions
	 */
	public function setTrustedCertificatesDirectory($dir, $verifyHost = 2)
	{
		if (!in_array($verifyHost, range(0, 2))) {
			throw new InvalidArgumentException("Verify host must be 0, 1 or 2");
		}

		if (!is_dir($dir)) {
			throw new MissingCertificateException('Certificate directory "' . $dir . ' is not readable.');
		}

		unset($this->options['caInfo']);
		$this->setCertificationVerify();
		$this->options['ssl_verifyHost'] = $verifyHost; // 2=secure
		$this->options['caPath'] = $dir;

		return $this;
	}

}
