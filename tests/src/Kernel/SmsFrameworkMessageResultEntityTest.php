<?php

namespace Drupal\Tests\sms\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\sms\Entity\SmsMessageResult;
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

}
