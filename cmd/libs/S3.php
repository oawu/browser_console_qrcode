<?php

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2016 OA Wu Design
 */

class S3 {
  const ACL_PRIVATE = 'private';
  const ACL_PUBLIC_READ = 'public-read';
  const ACL_PUBLIC_READ_WRITE = 'public-read-write';
  const ACL_AUTHENTICATED_READ = 'authenticated-read';

  public static $use_ssl = false;
  public static $verify_peer = true;
  public static $exts = array ('jpg' => array ('image/jpeg', 'image/pjpeg'), 'gif' => 'image/gif', 'png' => array ('image/png', 'image/x-png'), 'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'ico' => 'image/x-icon', 'swf' => 'application/x-shockwave-flash', 'pdf' => array ('application/pdf', 'application/x-download'), 'zip' => array ('application/x-zip', 'application/zip', 'application/x-zip-compressed'), 'gz' => 'application/x-gzip', 'tar' => 'application/x-tar', 'bz' => 'application/x-bzip', 'bz2' => 'application/x-bzip2', 'txt' => 'text/plain', 'asc' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html', 'css' => 'text/css', 'js' => 'application/x-javascript', 'xml' => 'text/xml', 'xsl' => 'text/xml', 'ogg' => 'application/ogg', 'mp3' => array ('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'), 'wav' => array ('audio/x-wav', 'audio/wave', 'audio/wav'), 'avi' => 'video/x-msvideo', 'mpg' => 'video/mpeg', 'mpeg' => 'video/mpeg', 'mov' => 'video/quicktime', 'flv' => 'video/x-flv', 'php' => 'application/x-httpd-php', 'hqx' => 'application/mac-binhex40', 'cpt' => 'application/mac-compactpro', 'csv' => array ('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'), 'bin' => 'application/macbinary', 'dms' => 'application/octet-stream', 'lha' => 'application/octet-stream', 'lzh' => 'application/octet-stream', 'exe' => array ('application/octet-stream', 'application/x-msdownload'), 'class' => 'application/octet-stream', 'psd' => 'application/x-photoshop', 'so' => 'application/octet-stream', 'sea' => 'application/octet-stream', 'dll' => 'application/octet-stream', 'oda' => 'application/oda', 'ai' => 'application/postscript', 'eps' => 'application/postscript', 'ps' => 'application/postscript', 'smi' => 'application/smil', 'smil' => 'application/smil', 'mif' => 'application/vnd.mif', 'xls' => array ('application/excel', 'application/vnd.ms-excel', 'application/msexcel'), 'ppt' => array ('application/powerpoint', 'application/vnd.ms-powerpoint'), 'wbxml' => 'application/wbxml', 'wmlc' => 'application/wmlc', 'dcr' => 'application/x-director', 'dir' => 'application/x-director', 'dxr' => 'application/x-director', 'dvi' => 'application/x-dvi', 'gtar' => 'application/x-gtar', 'php4' => 'application/x-httpd-php', 'php3' => 'application/x-httpd-php', 'phtml' => 'application/x-httpd-php', 'phps' => 'application/x-httpd-php-source', 'sit' => 'application/x-stuffit', 'tgz' => array ('application/x-tar', 'application/x-gzip-compressed'), 'xhtml' => 'application/xhtml+xml', 'xht' => 'application/xhtml+xml', 'mid' => 'audio/midi', 'midi' => 'audio/midi', 'mpga' => 'audio/mpeg', 'mp2' => 'audio/mpeg', 'aif' => 'audio/x-aiff', 'aiff' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff', 'ram' => 'audio/x-pn-realaudio', 'rm' => 'audio/x-pn-realaudio', 'rpm' => 'audio/x-pn-realaudio-plugin', 'ra' => 'audio/x-realaudio', 'rv' => 'video/vnd.rn-realvideo', 'bmp' => array ('image/bmp', 'image/x-windows-bmp'), 'jpeg' => array ('image/jpeg', 'image/pjpeg'), 'jpe' => array ('image/jpeg', 'image/pjpeg'), 'shtml' => 'text/html', 'text' => 'text/plain', 'log' => array ('text/plain', 'text/x-log'), 'rtx' => 'text/richtext', 'rtf' => 'text/rtf', 'mpe' => 'video/mpeg', 'qt' => 'video/quicktime', 'movie' => 'video/x-sgi-movie', 'doc' => 'application/msword', 'docx' => array ('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'), 'xlsx' => array ('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'), 'word' => array ('application/msword', 'application/octet-stream'), 'xl' => 'application/excel', 'eml' => 'message/rfc822', 'json' => array ('application/json', 'text/json'), 'svg' => array ('image/svg+xml'));

  private static $__access_key = NULL;
  private static $__secret_key = NULL;

  public static function init ($access_key, $secret_key) {
    self::$__access_key = $access_key;
    self::$__secret_key = $secret_key;
  }

  public static function listBuckets ($detailed = false) {
    $rest = new S3Request ('GET', '', '');
    $rest = $rest->getResponse ();

    if (($rest->error !== false) || ($rest->code !== 200))
      throw new Exception (sprintf ("S3::listBuckets(): [%s] %s", $rest->code, 'Unexpected HTTP status'));

    $results = array ();
    if (!isset ($rest->body->Buckets))
      return $results;

    if ($detailed) {
      if (isset ($rest->body->Owner, $rest->body->Owner->ID, $rest->body->Owner->DisplayName))
        $results['owner'] = array (
            'id' => (String)$rest->body->Owner->ID,
            'name' => (String)$rest->body->Owner->ID
          );

      $results['buckets'] = array ();
      foreach ($rest->body->Buckets->Bucket as $bucket) array_push ($results['buckets'], array (
          'name' => (String) $bucket->Name,
          'time' => date ('Y-m-d H:i:s', strtotime ((String) $bucket->CreationDate))
        ));
    } else {
      $results = array ();
      foreach ($rest->body->Buckets->Bucket as $bucket) array_push ($results, $bucket);
    }
      
    return $results;
  }

  public static function getBucket ($bucket, $prefix = null, $marker = null, $maxKeys = null, $delimiter = null, $returnCommonPrefixes = false) {
    $rest = new S3Request ('GET', $bucket, '');
    if ($prefix !== null && $prefix !== '') $rest->setParameter ('prefix', $prefix);
    if ($marker !== null && $marker !== '') $rest->setParameter ('marker', $marker);
    if ($maxKeys !== null && $maxKeys !== '') $rest->setParameter ('max-keys', $maxKeys);
    if ($delimiter !== null && $delimiter !== '') $rest->setParameter ('delimiter', $delimiter);

    $response = $rest->getResponse ();
    if (($response->error !== false) || ($response->code !== 200))
      throw new Exception (sprintf ("S3::getBucket(): [%s] %s", $response->code, 'Unexpected HTTP status'));

    $results = array ();

    $nextMarker = null;
    if (isset ($response->body, $response->body->Contents))
      foreach ($response->body->Contents as $content) {
        $results[(String)$content->Key] = array ('name' => (String)$content->Key, 'time' => date ('Y-m-d H:i:s', strtotime ((String)$content->LastModified)), 'size' => (int)$content->Size, 'hash' => substr ((String)$content->ETag, 1, -1));
        $nextMarker = (String)$content->Key;
      }

    if ($returnCommonPrefixes && isset ($response->body, $response->body->CommonPrefixes))
      foreach ($response->body->CommonPrefixes as $content)
        $results[(String) $content->Prefix] = array ('prefix' => (String)$content->Prefix);

    if (isset ($response->body, $response->body->IsTruncated) && (((String)$response->body->IsTruncated) == 'false')) return $results;
    if (isset ($response->body, $response->body->NextMarker)) $nextMarker = (String)$response->body->NextMarker;

    if (($maxKeys == null) && ($nextMarker !== null) && (((String)$response->body->IsTruncated) == 'true'))
      do {
        $rest = new S3Request ('GET', $bucket, '');
        
        if (($prefix !== null) && ($prefix !== '')) $rest->setParameter ('prefix', $prefix);
        $rest->setParameter ('marker', $nextMarker);

        if (($delimiter !== null) && ($delimiter !== '')) $rest->setParameter ('delimiter', $delimiter);
        if ((($response = $rest->getResponse(true)) == false) || ($response->code !== 200)) break;

        if (isset ($response->body, $response->body->Contents))
          foreach ($response->body->Contents as $content) {
            $results[(String)$content->Key] = array ('name' => (String)$content->Key, 'time' => date ('Y-m-d H:i:s', strtotime ((String)$content->LastModified)), 'size' => (int) $content->Size, 'hash' => substr ((String)$content->ETag, 1, -1));
            $nextMarker = (String)$content->Key;
          }

        if ($returnCommonPrefixes && isset ($response->body, $response->body->CommonPrefixes)) foreach ($response->body->CommonPrefixes as $content) $results[(String)$content->Prefix] = array ('prefix' => (String)$content->Prefix);

        if (isset ($response->body, $response->body->NextMarker)) $nextMarker = (String)$response->body->NextMarker;
      } while (($response !== false) && (((String)$response->body->IsTruncated) == 'true'));
    return $results;
  }

  public static function putBucket ($bucket, $acl = self::ACL_PRIVATE, $location = false) {
    $rest = new S3Request ('PUT', $bucket, '');
    $rest->setAmzHeader ('x-amz-acl', $acl);

    if ($location !== false) {
      $dom = new DOMDocument ();
      $createBucketConfiguration = $dom->createElement ('CreateBucketConfiguration');
      $locationConstraint = $dom->createElement ('LocationConstraint', strtoupper ($location));
      $createBucketConfiguration->appendChild ($locationConstraint);
      $dom->appendChild ($createBucketConfiguration);
      $rest->data = $dom->saveXML ();
      $rest->size = strlen ($rest->data);
      $rest->setHeader ('Content-Type', 'application/xml');
    }
    $rest = $rest->getResponse ();

    if (($rest->error !== false) || ($rest->code !== 200))
      throw new Exception (sprintf ("S3::putBucket(%s, $s, $s): [%s] %s", $bucket, $acl, $location, $rest->code, 'Unexpected HTTP status'));

    return true;
  }

  public static function deleteBucket ($bucket) {
    $rest = new S3Request ('DELETE', $bucket);
    $rest = $rest->getResponse ();
    if (($rest->error !== false) || ($rest->code !== 200))
      throw new Exception (sprintf ("S3::deleteBucket(%s): [%s] %s", $bucket, $rest->code, 'Unexpected HTTP status'));

    return true;
  }

  public static function fileMD5 ($filePath) {
    return base64_encode (md5_file ($filePath, true));
  }
  public static function putFile ($filePath, $bucket, $s3Path, $acl = self::ACL_PUBLIC_READ, $metaHeaders = array (), $requestHeaders = array ()) {
    if (!(file_exists ($filePath) && is_file ($filePath) && is_readable ($filePath)))
      throw new Exception ('S3::putFile(): Unable to open input file: ' . $filePath);

    $rest = new S3Request ('PUT', $bucket, $s3Path);


    $rest->fp = @fopen ($filePath, 'rb');
    $rest->size = filesize ($filePath);

    $rest->setHeader ('Content-Type', self::__getMimeType ($filePath))
         ->setHeader ('Content-MD5', self::fileMD5 ($filePath));
    foreach ($requestHeaders as $h => $v) $rest->setHeader ($h, $v);
    
    $rest->setAmzHeader ('x-amz-acl', $acl);
    foreach ($metaHeaders as $h => $v) $rest->setAmzHeader ('x-amz-meta-' . $h, $v);
    
    if (!(($rest->size >= 0) && (($rest->fp !== false) || ($rest->data !== false))))
      throw new Exception (sprintf ("S3::putObject(): [%s] %s", 0, 'Missing input parameters'));

    $rest->getResponse ();

    if (($rest->response->error !== false) || ($rest->response->code !== 200))
      throw new Exception (sprintf ("S3::putObject(): [%s] %s", $rest->response->code, 'Unexpected HTTP status'));

    return true;
  }

  public static function __getMimeType (&$file) {
    return ($extension = self::getMimeByExtension ($file)) ? $extension : 'text/plain';//'application/octet-stream';
  }

  public static function getMimeByExtension ($file) {
    $extension = strtolower (substr (strrchr ($file, '.'), 1));
    return isset (self::$exts[$extension]) ? is_array (self::$exts[$extension]) ? current (self::$exts[$extension]) : self::$exts[$extension] : false;
  }

  public static function getObject($bucket, $uri, $saveTo = false) {
    $rest = new S3Request ('GET', $bucket, $uri);
    
    if ($saveTo !== false)
      if (($rest->fp = @fopen ($saveTo, 'wb')) !== false) $rest->file = realpath ($saveTo);
      throw new Exception (sprintf ("S3::getObject(%s, %s): [%s] %s",$bucket, $uri, 0, 'Unable to open save file for writing: ' . $saveTo));

    $rest->getResponse();
    
    if (($rest->response->error !== false) || ($rest->response->code !== 200))
      throw new Exception (sprintf ("S3::getObject(%s, %s): [%s] %s",$bucket, $uri, $rest->response->code, 'Unexpected HTTP status'));

    return $rest->response;
  }

  public static function getObjectInfo ($bucket, $uri, $returnInfo = true) {
    $rest = new S3Request ('HEAD', $bucket, $uri);
    $rest = $rest->getResponse ();

    if (($rest->error !== false) || (($rest->code !== 200) && ($rest->code !== 404)))
      throw new Exception (sprintf ("S3::getObjectInfo(%s, %s): [%s] %s", $bucket, $uri, $rest->code, 'Unexpected HTTP status'));

    return $rest->code == 200 ? $returnInfo ? $rest->headers : true : false;
  }

  public static function copyObject ($srcBucket, $srcUri, $bucket, $uri, $acl = self::ACL_PUBLIC_READ, $metaHeaders = array (), $requestHeaders = array ()) {
    $rest = new S3Request ('PUT', $bucket, $uri);
    $rest->setHeader ('Content-Length', 0);

    foreach ($requestHeaders as $h => $v)
      $rest->setHeader ($h, $v);

    foreach ($metaHeaders as $h => $v)
      $rest->setAmzHeader ('x-amz-meta-' . $h, $v);

    $rest->setAmzHeader ('x-amz-acl', $acl)
         ->setAmzHeader ('x-amz-copy-source', sprintf ('/%s/%s', $srcBucket, $srcUri));

    if ((sizeof ($requestHeaders) > 0) || (sizeof ($metaHeaders) > 0))
      $rest->setAmzHeader ('x-amz-metadata-directive', 'REPLACE');

    $rest = $rest->getResponse ();

    if (($rest->error !== false) || ($rest->code !== 200))
      throw new Exception (sprintf ("S3::copyObject(%s, %s, %s, %s): [%s] %s", $srcBucket, $srcUri, $bucket, $uri, $rest->code, 'Unexpected HTTP status'));

    return isset ($rest->body->LastModified, $rest->body->ETag) ? array ('time' => date ('Y-m-d H:i:s', strtotime ((String) $rest->body->LastModified)), 'hash' => substr ((String) $rest->body->ETag, 1, -1)) : false;
  }
  
  public static function getBucketLocation ($bucket) {
    $rest = new S3Request ('GET', $bucket, '');
    $rest->setParameter ('location', null);
    $rest = $rest->getResponse ();
    if (($rest->error !== false) || ($rest->code !== 200))
      throw new Exception (sprintf ("S3::getBucketLocation(%s): [%s] %s", $bucket, $rest->code, 'Unexpected HTTP status'));
    return (isset ($rest->body[0]) && (((String)$rest->body[0]) !== '')) ? (String)$rest->body[0] : 'US';
  }

  public static function deleteObject ($bucket, $uri) {
    $rest = new S3Request ('DELETE', $bucket, $uri);
    $rest = $rest->getResponse ();

    if (($rest->error !== false) || ($rest->code !== 204))
      throw new Exception (sprintf ("S3::deleteObject(): [%s] %s", $rest->code, 'Unexpected HTTP status'));

    return true;
  }

  public static function __getSignature ($string) {
    return 'AWS ' . self::$__access_key . ':' . self::__getHash ($string);
  }

  private static function __getHash($string) {
    return base64_encode (extension_loaded ('hash') ? hash_hmac ('sha1', $string, self::$__secret_key, true) : pack ('H*', sha1 ((str_pad (self::$__secret_key, 64, chr (0x00)) ^ (str_repeat (chr (0x5c), 64))) . pack ('H*', sha1 ((str_pad (self::$__secret_key, 64, chr (0x00)) ^ (str_repeat (chr (0x36), 64))) . $string)))));
  }
}

final class S3Request {

  private $verb, $bucket, $uri, $resource = '', $parameters = array (), $amzHeaders = array (), $headers = array ('Host' => '', 'Date' => '', 'Content-MD5' => '', 'Content-Type' => '');
  public $fp = false, $size = 0, $data = false, $response;

  public function __construct ($verb, $bucket = '', $uri = '', $defaultHost = 's3.amazonaws.com') {
    $this->verb = $verb;
    $this->bucket = strtolower ($bucket);
    $this->uri = $uri !== '' ? '/' . str_replace ('%2F', '/', rawurlencode($uri)) : '/';

    if ($this->bucket !== '') {
      $this->headers['Host'] = $this->bucket . '.' . $defaultHost;
      $this->resource = '/' . $this->bucket . $this->uri;
    } else {
      $this->headers['Host'] = $defaultHost;
      $this->resource = $this->uri;
    }

    $this->headers['Date'] = gmdate('D, d M Y H:i:s T');
    $this->response = new STDClass;
    $this->response->error = false;
    $this->response->body = '';
  }

  public function setParameter ($key, $value) {
    $this->parameters[$key] = $value;
    return $this;
  }

  public function setHeader ($key, $value) {
    $this->headers[$key] = $value;
    return $this;
  }

  public function setAmzHeader ($key, $value) {
    $this->amzHeaders[$key] = $value;
    return $this;
  }

  public function getResponse () {
    $query = '';
    if (sizeof ($this->parameters) > 0) {
      $query = substr ($this->uri, -1) !== '?' ? '?' : '&';

      foreach ($this->parameters as $var => $value) $query .= (($value == null) || ($value == '') ? $var . '&' : ($var . '=' . rawurlencode($value) . '&'));

      $query = substr ($query, 0, -1);
      $this->uri .= $query;

      if (array_key_exists ('acl', $this->parameters) || array_key_exists ('location', $this->parameters) || array_key_exists ('torrent', $this->parameters) || array_key_exists ('logging', $this->parameters)) $this->resource .= $query;
    }

    $url = ((S3::$use_ssl && extension_loaded ('openssl')) ? 'https://' : 'http://') . $this->headers['Host'] . $this->uri;

    $curl = curl_init ();
    curl_setopt ($curl, CURLOPT_USERAGENT, 'S3/php');

    if (S3::$use_ssl) {
      curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 1);

      if (S3::$verify_peer) curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 1);
      else curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    }

    curl_setopt ($curl, CURLOPT_URL, $url);

    $headers = $amz = array ();

    foreach ($this->amzHeaders as $header => $value)
      if (strlen ($value) > 0) $headers[] = $header . ': ' . $value;
    
    foreach ($this->headers as $header => $value)
      if (strlen ($value) > 0) $headers[] = $header . ': ' . $value;

    foreach ($this->amzHeaders as $header => $value)
      if (strlen($value) > 0) $amz[] = strtolower ($header) . ':' . $value;

    if (sizeof ($amz) > 0) {
      sort ($amz);
      $amz = "\n" . implode("\n", $amz);
    } else
      $amz = '';

    $headers[] = 'Authorization: ' . S3::__getSignature ($this->headers['Host'] == 'cloudfront.amazonaws.com' ? $this->headers['Date'] : $this->verb . "\n" . $this->headers['Content-MD5'] . "\n" . $this->headers['Content-Type'] . "\n" . $this->headers['Date'] . $amz . "\n" . $this->resource);

    curl_setopt ($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt ($curl, CURLOPT_HEADER, false);
    curl_setopt ($curl, CURLOPT_RETURNTRANSFER, false);
    curl_setopt ($curl, CURLOPT_WRITEFUNCTION, array (&$this, '__responseWriteCallback'));
    curl_setopt ($curl, CURLOPT_HEADERFUNCTION, array (&$this, '__responseHeaderCallback'));
    curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, true);

    switch ($this->verb) {
      case 'PUT': case 'POST':
        if ($this->fp !== false) {
          curl_setopt ($curl, CURLOPT_PUT, true);
          curl_setopt ($curl, CURLOPT_INFILE, $this->fp);
          if ($this->size >= 0) curl_setopt ($curl, CURLOPT_INFILESIZE, $this->size);
        } elseif ($this->data !== false) {
          curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
          curl_setopt ($curl, CURLOPT_POSTFIELDS, $this->data);
          if ($this->size >= 0) curl_setopt ($curl, CURLOPT_BUFFERSIZE, $this->size);
        } else {
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
        }
        break;

      case 'HEAD':
        curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
        curl_setopt ($curl, CURLOPT_NOBODY, true);
        break;

      case 'DELETE':
        curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        break;

      case 'GET': default: break;
    }

    if (curl_exec ($curl)) $this->response->code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);
    else $this->response->error = array ('code' => curl_errno ($curl), 'message' => curl_error ($curl), 'resource' => $this->resource);
    
    @curl_close ($curl);

    if ($this->response->error === false && isset ($this->response->headers['type']) && $this->response->headers['type'] == 'application/xml' && isset ($this->response->body)) {
      $this->response->body = simplexml_load_string($this->response->body);

      if (!in_array ($this->response->code, array (200, 204)) && isset ($this->response->body->Code, $this->response->body->Message)) {
        $this->response->error = array ('code' => (String) $this->response->body->Code, 'message' => (String) $this->response->body->Message);

        if (isset($this->response->body->Resource)) $this->response->error['resource'] = (String) $this->response->body->Resource;
        unset ($this->response->body);
      }
    }

    if ($this->fp !== false && is_resource ($this->fp)) fclose($this->fp);

    return $this->response;
  }

  private function __responseWriteCallback (&$curl, &$data) {
    if ($this->response->code == 200 && $this->fp !== false) return fwrite($this->fp, $data);
    else $this->response->body .= $data;

    return strlen ($data);
  }

  private function __responseHeaderCallback (&$curl, &$data) {
    if (($strlen = strlen ($data)) <= 2) return $strlen;
    
    if (substr ($data, 0, 4) == 'HTTP') { $this->response->code = (int)substr ($data, 9, 3); }
    else {
      list ($header, $value) = explode (': ', trim ($data), 2);

      if ($header == 'Last-Modified') $this->response->headers['time'] = strtotime ($value);
      elseif ($header == 'Content-Length') $this->response->headers['size'] = (int)$value;
      elseif ($header == 'Content-Type') $this->response->headers['type'] = $value;
      elseif ($header == 'ETag') $this->response->headers['hash'] = $value{0} == '"' ? substr ($value, 1, -1) : $value;
      elseif (preg_match ('/^x-amz-meta-.*$/', $header)) $this->response->headers[$header] = is_numeric ($value) ? (int)$value : $value;
    }

    return $strlen;
  }
}