<?php

const JSON_CONTENT_TYPE = 'application/json;charset=utf-8';
const JSONP_CONTENT_TYPE = 'application/javascript;charset=utf-8;';
const ZIP_CONTENT_TYPE = 'application/zip';
const PDF_CONTENT_TYPE = 'application/pdf';
const PLAIN_CONTENT_TYPE = 'text/plain;charset=utf-8;';
const HTML_CONTENT_TYPE = 'text/htm;charset=utf-8;';
const CSS_CONTENT_TYPE = 'text/css;charset=utf-8;';
const JAVASCRIPT_CONTENT_TYPE = 'text/javascript;charset=utf-8;';
const XML_CONTENT_TYPE = 'text/xml;charset=utf-8;';
const PNG_CONTENT_TYPE = 'image/png';
const JPEG_CONTENT_TYPE = 'image/jpeg';
const MPEG_CONTENT_TYPE = 'audio/mpeg';

/**
 * Lifecycle：启动
 */
const LIFECYCLE_BOOT = 'LIFECYCLE_BOOT';

/**
 * Lifecycle：启动完成
 */
const LIFECYCLE_BOOTED = 'LIFECYCLE_BOOTED';

/**
 * Lifecycle：服务启动完成
 */
const LIFECYCLE_SERVER_BOOTED = 'LIFECYCLE_SERVER_BOOTED';

/**
 * Lifecycle：沙盒启动完成
 */
const LIFECYCLE_SANDBOX_BOOTED = 'LIFECYCLE_SANDBOX_BOOTED';

/**
 * Lifecycle：沙盒执行结束
 */
const LIFECYCLE_SANDBOX_RAN = 'LIFECYCLE_SANDBOX_RAN';

/**
 * Lifecycle：沙盒销毁
 */
const LIFECYCLE_SANDBOX_DESTRUCT = 'LIFECYCLE_SANDBOX_DESTRUCT';
