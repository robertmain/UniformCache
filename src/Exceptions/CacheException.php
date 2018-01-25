<?php

namespace UniformCache\Exceptions;

use Exception;
use Psr\Cache\CacheException as PSRCacheException;

class CacheException extends Exception implements PSRCacheException
{
}
