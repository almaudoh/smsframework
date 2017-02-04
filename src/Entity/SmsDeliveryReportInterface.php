<?php

namespace Drupal\sms\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\sms\Message\SmsDeliveryReportInterface as PlainDeliveryReportInterface;

interface SmsDeliveryReportInterface extends PlainDeliveryReportInterface, ContentEntityInterface {

}
