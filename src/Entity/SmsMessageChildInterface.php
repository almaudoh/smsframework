<?php

namespace Drupal\sms\Entity;

/**
 * Interface for objects that have parent relationship with SmsMessage entities.
 */
interface SmsMessageChildInterface {

  /**
   * Gets the parent of this child entity.
   *
   * @return \Drupal\sms\Entity\SmsMessageInterface
   */
  public function getParent();

  /**
   * Sets the parent of this child entity.
   *
   * @param \Drupal\sms\Entity\SmsMessageInterface $sms_message
   *   The parent SMS message object.
   *
   * @return $this
   */
  public function setParent(SmsMessageInterface $sms_message);

}
