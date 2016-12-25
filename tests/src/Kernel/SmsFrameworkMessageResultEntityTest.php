<?php

namespace Drupal\Tests\sms\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\sms\Entity\SmsDeliveryReport;
use Drupal\sms\Entity\SmsMessageResult;
use Drupal\sms\Message\SmsMessageResultStatus;
use Drupal\sms\Tests\SmsFrameworkMessageResultTestTrait;

/**
 * Tests the SMS message result entity.
 *
 * @group SMS Framework
 * @coversDefaultClass \Drupal\sms\Entity\SmsMessageResult
 */
class SmsFrameworkMessageResultEntityTest extends KernelTestBase {

  use SmsFrameworkMessageResultTestTrait;

  public static $modules = ['user', 'sms', 'sms_test_gateway', 'telephone', 'dynamic_entity_reference', 'entity_test'];

  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('user');
    $this->installEntitySchema('sms');
    $this->installEntitySchema('sms_result');
    $this->installEntitySchema('sms_report');
  }

  /**
   * {@inheritdoc}
   */
  protected function createMessageResult() {
    return SmsMessageResult::create();
  }

  public function testSaveAndRetrieveResult() {
    /** @var \Drupal\sms\Entity\SmsMessageResult $result */
    $result = $this->createMessageResult()
      ->setCreditsUsed(rand(5,10))
      ->setCreditsBalance(rand(10,20))
      ->setError(SmsMessageResultStatus::INVALID_SENDER)
      ->setErrorMessage('Invalid sender ID')
      ->setReports([(SmsDeliveryReport::create())->setRecipient('1234567890')]);
    $result->save();

    $storage = $this->container->get('entity_type.manager')->getStorage('sms_result');
    $saved = $storage->load($result->id());
    /** @var \Drupal\sms\Entity\SmsMessageResult $saved */
    $this->assertEquals($result->getCreditsBalance(), $saved->getCreditsBalance());
    $this->assertEquals($result->getCreditsUsed(), $saved->getCreditsUsed());
    $this->assertEquals($result->getError(), $saved->getError());
    $this->assertEquals($result->getErrorMessage(), $saved->getErrorMessage());
    $this->assertEquals($result->getReports(), $saved->getReports());
    $this->assertEquals($result->uuid(), $saved->uuid());
  }

}
