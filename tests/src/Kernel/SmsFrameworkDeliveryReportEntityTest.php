<?php

namespace Drupal\Tests\sms\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\sms\Entity\SmsDeliveryReport;
use Drupal\sms\Tests\SmsFrameworkDeliveryReportTestTrait;
use Drupal\sms\Tests\SmsFrameworkTestTrait;

/**
 * Tests the SMS Delivery report entity.
 *
 * @group SMS Framework
 * @coversDefaultClass \Drupal\sms\Entity\SmsDeliveryReport
 */
class SmsFrameworkDeliveryReportEntityTest extends KernelTestBase  {

  use SmsFrameworkTestTrait;
  use SmsFrameworkDeliveryReportTestTrait;

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
  protected function createDeliveryReport() {
    return SmsDeliveryReport::create();
  }

}
