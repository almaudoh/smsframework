<?php

namespace Drupal\sms\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\sms\Exception\SmsException;
use Drupal\sms\Message\SmsDeliveryReportInterface as PlainDeliveryReportInterface;

/**
 * Defines the SMS message result entity.
 *
 * The SMS message result entity is used to store SMS message results.
 *
 * @ContentEntityType(
 *   id = "sms_result",
 *   label = @Translation("SMS Message Result"),
 *   base_table = "sms_result",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class SmsMessageResult extends ContentEntityBase implements SmsMessageResultInterface {

  /**
   * The static cache for delivery reports associated with this SMS result.
   *
   * @var \Drupal\sms\Entity\SmsDeliveryReportInterface[]
   */
  protected $reports = [];

  /**
   * {@inheritdoc}
   */
  public function getError() {
    return $this->get('error')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setError($error) {
    $this->set('error', $error);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getErrorMessage() {
    return $this->get('error_message')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setErrorMessage($message) {
    $this->set('error_message', $message);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getReport($recipient) {
    foreach ($this->reports as $report) {
      if ($report->getRecipient() === $recipient) {
        return $report;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getReports() {
    return $this->reports;
  }

  /**
   * {@inheritdoc}
   */
  public function setReports(array $reports) {
    $this->reports = $reports;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addReport(PlainDeliveryReportInterface $report) {
    $this->reports[] = $report;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreditsBalance() {
    return $this->get('credits_balance')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreditsBalance($balance) {
    if (is_numeric($balance) || is_null($balance)) {
      $this->set('credits_balance', $balance);
    }
    else {
      throw new SmsException(sprintf('Credit balance set is a %s', gettype($balance)));
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreditsUsed() {
    return $this->get('credits_used')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreditsUsed($credits_used) {
    if (is_numeric($credits_used) || is_null($credits_used)) {
      $this->set('credits_used', $credits_used);
    }
    else {
      throw new SmsException(sprintf('Credit used is a %s', gettype($credits_used)));
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSmsMessage() {
    return $this->get('sms_message')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setSmsMessage(SmsMessageInterface $sms_message) {
    $this->set('sms_message', $sms_message);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['error'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Error'))
      ->setDescription(t('The normalized error code.'))
      ->setReadOnly(TRUE)
      ->setRequired(FALSE);

    $fields['error_message'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Error message'))
      ->setDescription(t('The description of the error from the gateway.'))
      ->setReadOnly(TRUE)
      ->setDefaultValue('')
      ->setRequired(FALSE);

    $fields['credits_balance'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Credits balance'))
      ->setDescription(t('The balance of credits after the message was sent.'))
      ->setReadOnly(TRUE)
      ->setRequired(TRUE);

    $fields['credits_used'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Credits used'))
      ->setDescription(t('The credits used for the message transaction.'))
      ->setReadOnly(TRUE)
      ->setRequired(TRUE);

    $fields['sms_message'] = BaseFieldDefinition::create('entity_reference')
      ->setSetting('target_type', 'sms')
      ->setLabel(t('SMS Message'))
      ->setDescription(t('The parent SMS message.'))
      ->setReadOnly(TRUE)
      ->setRequired(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    // SMS message result cannot be saved without a parent SMS message.
    if (!$this->getSmsMessage()) {
      throw new \LogicException('No parent SMS message specified for SMS message result');
    }
    parent::preSave($storage);
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities) {
    // This post-load hook is to ensure consistency of behavior of the
    // ::getReports() method by assigning the actual values from the parent SMS
    // message entity (if it exists) to the temporary in-memory $reports
    // variable.
    /** @var \Drupal\sms\Entity\SmsMessageResultInterface $result */
    foreach ($entities as $result) {
      if ($result->getSmsMessage()) {
        $result->setReports($result->getSmsMessage()->getReports());
      }
    }
  }

}
