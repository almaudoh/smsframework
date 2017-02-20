<?php

namespace Drupal\Tests\sms\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\sms\Entity\SmsDeliveryReport;
use Drupal\sms\Entity\SmsDeliveryReportInterface;
use Drupal\sms\Message\SmsMessageReportStatus;
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
  use SmsFrameworkDeliveryReportTestTrait {
    // Remove 'test' prefix so it will not be run by test runner and override.
    testTimeQueued as timeQueued;
    testTimeDelivered as timeDelivered;
  }

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

  public function testTimeQueued() {
    $report = $this->createDeliveryReport();
    $this->assertNull($report->getTimeQueued(), 'Default value is NULL');

    // Save a version that has QUEUED as the status.
    $time = 123123123;
    $report
      ->setStatus(SmsMessageReportStatus::QUEUED)
      ->setStatusTime($time)
      ->save();

    $return = $report
      ->setTimeQueued($time);

    $this->assertTrue($return instanceof SmsDeliveryReportInterface);
    $this->assertEquals($time, $report->getTimeQueued());
  }

  public function testTimeDelivered() {
    $report = $this->createDeliveryReport();
    $this->assertNull($report->getTimeQueued(), 'Default value is NULL');

    // Save a version that has QUEUED as the status.
    $time = 123123123;
    $report
      ->setStatus(SmsMessageReportStatus::DELIVERED)
      ->setStatusTime($time)
      ->save();

    $return = $report
      ->setTimeDelivered($time);

    $this->assertTrue($return instanceof SmsDeliveryReportInterface);
    $this->assertEquals($time, $report->getTimeDelivered());
  }

  /**
   * Tests saving and retrieval of a complete entity.
   */
  public function testSaveAndRetrieveReport() {
    /** @var \Drupal\sms\Entity\SmsDeliveryReport $report */
    $report = $this->createDeliveryReport()
      ->setMessageId($this->randomMachineName())
      ->setStatus(SmsMessageReportStatus::DELIVERED)
      ->setRecipient('1234567890')
      ->setStatusMessage('Message delivered')
      ->setTimeQueued(REQUEST_TIME)
      ->setTimeDelivered(REQUEST_TIME + 3600);
    $report->save();

    $storage = $this->container->get('entity_type.manager')->getStorage('sms_report');
    $saved = $storage->loadByProperties([
      'recipient' => '1234567890',
    ]);
    $this->assertEquals(1, count($saved));
    $saved = reset($saved);
    $this->assertEquals($report->getRecipient(), $saved->getRecipient());
    $this->assertEquals($report->getMessageId(), $saved->getMessageId());
    $this->assertEquals($report->getStatus(), $saved->getStatus());
    $this->assertEquals($report->getStatusMessage(), $saved->getStatusMessage());
    $this->assertEquals($report->getTimeQueued(), $saved->getTimeQueued());
    $this->assertEquals($report->getTimeDelivered(), $saved->getTimeDelivered());
    $this->assertEquals($report->getTimeDelivered(), $saved->getTimeDelivered());
    $this->assertEquals($report->uuid(), $saved->uuid());
  }

  public function testSaveReportWithoutParent() {

  }

  public function testReportRevisions() {

  }

}
