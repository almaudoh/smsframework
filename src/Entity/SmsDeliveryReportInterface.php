<?php

namespace Drupal\sms\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\sms\Message\SmsDeliveryReportInterface as PlainDeliveryReportInterface;

/**
 * Interface for SMS delivery report entity.
 */
interface SmsDeliveryReportInterface extends PlainDeliveryReportInterface, ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets the parent SMS message entity.
   *
   * @return \Drupal\sms\Entity\SmsMessageInterface
   */
  public function getSmsMessage();

  /**
   * Sets the parent SMS message entity.
   *
   * @param \Drupal\sms\Entity\SmsMessageInterface $sms_message
   *   The parent SMS message object.
   *
   * @return $this
   */
  public function setSmsMessage(SmsMessageInterface $sms_message);

}
