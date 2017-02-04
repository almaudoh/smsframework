<?php

namespace Drupal\sms\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\sms\Message\SmsMessageResultInterface as PlainMessageResultInterface;

interface SmsMessageResultInterface extends PlainMessageResultInterface, ContentEntityInterface {

}
