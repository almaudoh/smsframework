<?php

namespace Drupal\sms\Tests;

use Drupal\Core\Url;
use Drupal\sms\Direction;
use Drupal\sms\Entity\SmsDeliveryReport;
use Drupal\sms\Message\SmsDeliveryReportInterface;
use Drupal\sms\Message\SmsMessageReportStatus;
use Drupal\sms\Message\SmsMessageResultInterface;

/**
 * Integration tests for delivery reports.
 *
 * @group SMS Framework
 */
class SmsFrameworkDeliveryReportTest extends SmsFrameworkWebTestBase {

  /**
   * Tests delivery reports integration.
   */
  public function testDeliveryReports() {
    $user = $this->drupalCreateUser();
    $this->drupalLogin($user);

    $test_gateway = $this->createMemoryGateway(['skip_queue' => TRUE]);
    $this->container->get('router.builder')->rebuild();
    $sms_message = $this->randomSmsMessage($user->id())
      ->setGateway($test_gateway);

    $sms_messages = $this->defaultSmsProvider->send($sms_message);

    $result = $sms_messages[0]->getResult();
    $this->assertTrue($result instanceof SmsMessageResultInterface);
    $this->assertEqual(count($sms_message->getRecipients()), count($result->getReports()));
    $reports = $result->getReports();

    /** @var \Drupal\sms\Message\SmsDeliveryReportInterface $first_report */
    $first_report = reset($reports);
    $message_id = $first_report->getMessageId();
    $this->assertTrue($first_report instanceof SmsDeliveryReportInterface);
    $this->assertEqual($first_report->getStatus(), SmsMessageReportStatus::QUEUED);

    // Get the delivery reports url and simulate push delivery report.
    $url = $test_gateway->getPushReportUrl()->setAbsolute()->toString();
    $delivered_time = REQUEST_TIME;
    $delivery_report = <<<EOF
{
   "reports":[
      {
         "message_id":"{$message_id}",
         "recipient":"{$first_report->getRecipient()}",
         "time_sent":{$first_report->getTimeQueued()},
         "time_delivered": $delivered_time,
         "status_message": "status message"
      }
   ]
}
EOF;
    $this->drupalPost($url, 'application/json', ['delivery_report' => $delivery_report]);
    $this->assertText('custom response content');
    \Drupal::state()->resetCache();

    // Get the stored report and verify that it was properly parsed.
    $second_report = $this->getTestMessageReport($message_id, $test_gateway);
    $this->assertTrue($second_report instanceof SmsDeliveryReportInterface);
    $this->assertEqual("status message", $second_report->getStatusMessage());
    $this->assertEqual($delivered_time, $second_report->getTimeDelivered());
    $this->assertEqual($message_id, $second_report->getMessageId());
  }

  /**
   * Tests that delivery reports are updated after initial sending.
   */
  public function testDeliveryReportUpdate() {
    $user = $this->drupalCreateUser();
    $this->drupalLogin($user);

    $test_gateway = $this->createMemoryGateway();
    $test_gateway
      ->setRetentionDuration(Direction::OUTGOING, 1000)
      ->save();
    $this->container->get('router.builder')->rebuild();
    $sms_message = $this->randomSmsMessage($user->id())
      ->setGateway($test_gateway)
      ->setDirection(Direction::OUTGOING);

    $this->defaultSmsProvider->queue($sms_message);
    $this->container->get('cron')->run();
    $saved_reports = SmsDeliveryReport::loadMultiple();
    $this->assertEqual(count($sms_message->getRecipients()), count($saved_reports));
    foreach ($saved_reports as $report) {
      $this->assertEqual(SmsMessageReportStatus::QUEUED, $report->getStatus());
    }

    /** @var \Drupal\sms\Message\SmsDeliveryReportInterface $first_report */
    $first_report = reset($saved_reports);
    $message_id = $first_report->getMessageId();

    // Get the delivery reports url and simulate push delivery report.
    $url = $test_gateway->getPushReportUrl()->setAbsolute()->toString();
    $delivered_time = REQUEST_TIME + rand(10, 300);
    $delivery_report = <<<EOF
{
   "reports":[
      {
         "message_id":"{$message_id}",
         "recipient":"{$first_report->getRecipient()}",
         "time_sent":{$first_report->getTimeQueued()},
         "time_delivered": $delivered_time,
         "status_message": "status message"
      }
   ]
}
EOF;
    $this->drupalPost($url, 'application/json', ['delivery_report' => $delivery_report]);
    $this->container->get('entity_type.manager')->getStorage('sms_report')->resetCache();
    $updated_report = SmsDeliveryReport::load($first_report->id());
    $this->assertEqual(SmsMessageReportStatus::DELIVERED, $updated_report->getStatus());
    $this->assertEqual($delivered_time, $updated_report->getTimeDelivered());
  }

}
