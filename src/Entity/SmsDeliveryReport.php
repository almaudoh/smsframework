<?php

namespace Drupal\sms\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\sms\Message\SmsDeliveryReportInterface as PlainDeliveryReportInterface;

/**
 * Defines the SMS message delivery report entity.
 *
 * The SMS delivery report entity is used to keep track of delivery reports for
 * each message until they have been cleared by the administrator or an
 * automated process.
 *
 * @ContentEntityType(
 *   id = "sms_report",
 *   label = @Translation("SMS Delivery Report"),
 *   base_table = "sms_report",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class SmsDeliveryReport extends ContentEntityBase implements SmsDeliveryReportInterface {

  /**
   * {@inheritdoc}
   */
  public function getMessageId() {
    return $this->get('message_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMessageId($message_id) {
    $this->set('message_id', $message_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipient() {
    return $this->get('recipient')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRecipient($recipient) {
    $this->set('recipient', $recipient);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    $this->set('status', $status);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatusMessage() {
    return $this->get('status_message')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatusMessage($message) {
    $this->set('status_message', $message);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimeQueued() {
    return $this->get('time_queued')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTimeQueued($time) {
    $this->set('time_queued', $time);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimeDelivered() {
    return $this->get('time_delivered')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTimeDelivered($time) {
    $this->set('time_delivered', $time);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['message_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Message ID'))
      ->setDescription(t('The message ID assigned to the message.'))
      ->setReadOnly(TRUE)
      ->setDefaultValue('');

    $fields['recipient'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Recipient number'))
      ->setDescription(t('The phone number of the recipient of the message.'))
      ->setReadOnly(TRUE)
      ->setDefaultValue('')
      ->setRequired(TRUE);

    $fields['status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Delivery status'))
      ->setDescription(t('The status of the message. One of queued, delivered, expired, rejected, invalid_recipient or content_invalid'))
      ->setReadOnly(TRUE)
      ->setRequired(TRUE);

    $fields['status_message'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Status message'))
      ->setDescription(t('The status message describing the message status.'))
      ->setReadOnly(TRUE)
      ->setDefaultValue('')
      ->setRequired(FALSE);

    $fields['time_queued'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Time queued'))
      ->setDescription(t('The time the message was queued for sending.'))
      ->setReadOnly(TRUE)
      ->setRequired(TRUE);

    $fields['time_delivered'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Time delivered'))
      ->setDescription(t('The time the message was delivered.'))
      ->setReadOnly(TRUE)
      ->setRequired(TRUE);

    return $fields;
  }

  /**
   * Converts a plain SMS delivery report into an entity.
   *
   * @param \Drupal\sms\Message\SmsDeliveryReportInterface $sms_report
   *   A plain SMS delivery report.
   *
   * @return \Drupal\sms\Entity\SmsDeliveryReportInterface
   *   An SMS delivery report entity that can be saved.
   */
  public static function convertFromDeliveryReport(PlainDeliveryReportInterface $sms_report) {
    if ($sms_report instanceof static) {
      return $sms_report;
    }

    $new = static::create();
    $new
      ->setMessageId($sms_report->getMessageId())
      ->setRecipient($sms_report->getRecipient())
      ->setStatus($sms_report->getStatus())
      ->setStatusMessage($sms_report->getStatusMessage())
      ->setTimeQueued($sms_report->getTimeQueued())
      ->setTimeDelivered($sms_report->getTimeDelivered());

    return $new;
  }

}
