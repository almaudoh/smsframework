<?php

namespace Drupal\sms\Tests;

use Drupal\sms\Direction;
use Drupal\sms\Entity\SmsDeliveryReport;
use Drupal\sms\Message\SmsDeliveryReportInterface;
use Drupal\sms\Message\SmsMessage;
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
    $delivered_time = $this->container->get('datetime.time')->getRequestTime();
    $delivery_report = $this->buildJsonDeliveryReport(
      $message_id,
      $first_report->getRecipient(),
      SmsMessageReportStatus::DELIVERED,
      $delivered_time
    );
    $this->drupalPost($url, 'application/json', ['delivery_report' => $delivery_report]);
    $this->assertText('custom response content');
    \Drupal::state()->resetCache();

    // Get the stored report and verify that it was properly parsed.
    $second_report = $this->getTestMessageReport($message_id, $test_gateway);
    $this->assertTrue($second_report instanceof SmsDeliveryReportInterface);
    $this->assertEqual("Message delivered", $second_report->getStatusMessage());
    $this->assertEqual($delivered_time, $second_report->getTimeDelivered());
    $this->assertEqual($message_id, $second_report->getMessageId());
  }

  /**
   * Tests that delivery reports are updated after initial sending.
   */
  public function testDeliveryReportUpdate() {
    $user = $this->drupalCreateUser();
    $this->drupalLogin($user);
    $request_time = $this->container->get('datetime.time')->getRequestTime();

    $test_gateway = $this->createMemoryGateway();
    $test_gateway
      ->setRetentionDuration(Direction::OUTGOING, 1000)
      ->save();
    $this->container->get('router.builder')->rebuild();
    // Get the delivery reports url for simulating push delivery report.
    $push_url = $test_gateway->getPushReportUrl()->setAbsolute()->toString();

    $sms_message = (new SmsMessage())
      ->setSender($this->randomMachineName())
      ->addRecipients(['1234567890', '987654321'])
      ->setMessage($this->randomString())
      ->setUid($user->id())
      ->setGateway($test_gateway)
      ->setDirection(Direction::OUTGOING);

    $this->defaultSmsProvider->queue($sms_message);
    $this->container->get('cron')->run();
    $saved_reports = SmsDeliveryReport::loadMultiple();
    $this->assertEqual(2, count($saved_reports));
    $this->assertEqual(SmsMessageReportStatus::QUEUED, $saved_reports[1]->getStatus());
    $this->assertEqual(SmsMessageReportStatus::QUEUED, $saved_reports[2]->getStatus());

    /** @var \Drupal\sms\Message\SmsDeliveryReportInterface $first_report */
    $first_report = reset($saved_reports);
    $this->assertEqual($request_time, $first_report->getStatusTime());

    $message_id = $first_report->getMessageId();
    $status_time = $request_time + 100;
    $json_report = $this->buildJsonDeliveryReport($message_id, $first_report->getRecipient(), 'pending', $status_time);

    // Simulate push delivery report.
    $this->drupalPost($push_url, 'application/json', ['delivery_report' => $json_report]);
    $this->container->get('entity_type.manager')->getStorage('sms_report')->resetCache();
    $updated_report = SmsDeliveryReport::load($first_report->id());
    $this->assertEqual('pending', $updated_report->getStatus());
    $this->assertEqual($status_time, $updated_report->getStatusTime());
    $this->assertNull($updated_report->getTimeDelivered());

    // Simulate push delivery report.
    $status_time = $request_time + 500;
    $json_report = $this->buildJsonDeliveryReport($message_id, $first_report->getRecipient(), SmsMessageReportStatus::DELIVERED, $status_time);

    $this->drupalPost($push_url, 'application/json', ['delivery_report' => $json_report]);
    $this->container->get('entity_type.manager')->getStorage('sms_report')->resetCache();
    $updated_report = SmsDeliveryReport::load($first_report->id());
    $this->assertEqual(SmsMessageReportStatus::DELIVERED, $updated_report->getStatus());
    $this->assertEqual($status_time, $updated_report->getStatusTime());
    $this->assertEqual($status_time, $updated_report->getTimeDelivered());
  }

  /**
   * Builds a JSON delivery report for the Memory gateway.
   *
   * @param string $message_id
   *   The delivery report message ID.
   * @param string $recipient
   *   The delivery report recipient number.
   * @param string $status
   *   The message delivery status.
   * @param int $time
   *   The time for the current status update.
   *
   * @return string
   *   A JSON-formatted delivery report string.
   */
  protected function buildJsonDeliveryReport($message_id, $recipient, $status, $time) {
    return <<<EOF
{
   "reports":[
      {
         "message_id":"$message_id",
         "recipient":"$recipient",
         "status":"$status",
         "status_time": $time,
         "status_message": "Message $status"
      }
   ]
}
EOF;
  }

}
